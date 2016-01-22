<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use GuzzleHttp;
use DB, Log, Auth;
use App\Jobs\ReplyViaSlack;
use \Firebase\JWT\JWT;

class Slack extends BaseController {

  use DispatchesJobs;

  public function redirect(Request $request) {
    $client = new GuzzleHttp\Client();
    $res = $client->request('POST', 'https://slack.com/api/oauth.access', [
      'form_params' => [
        'client_id' => env('SLACK_CLIENT_ID'),
        'client_secret' => env('SLACK_CLIENT_SECRET'),
        'code' => $request->get('code'),
        'redirect_uri' => env('SLACK_REDIRECT_URI'),
      ]
    ]);
    Log::info("oauth.access: ".$res->getBody());
    $login = json_decode($res->getBody());
    if($login && property_exists($login, 'access_token')) {

      // Look up the user info for whoever just logged in
      $res = $client->request('GET', 'https://slack.com/api/auth.test', [
        'query' => [
          'token' => $login->access_token
        ]
      ]);
      Log::info("auth.test: ".$res->getBody());
      $auth = json_decode($res->getBody());
      if($auth && $auth->ok) {

        // Look up the team ID in the database
        $slackteam = DB::table('slack_teams')->where('slack_teamid', $login->team_id)->first();
        if(!$slackteam) {

          // Create the org and slack team
          $orgID = DB::table('orgs')->insertGetId([
            'name' => $login->team_name,
            'created_at' => date('Y-m-d H:i:s')
          ]);

          DB::table('slack_teams')->insertGetId([
            'slack_teamid' => $login->team_id,
            'slack_teamname' => $login->team_name,
            'org_id' => $orgID,
            'slack_token' => $login->access_token,
            'slack_url' => $auth->url,
            'created_at' => date('Y-m-d H:i:s')
          ]);

        } else {
          $orgID = $slackteam->org_id;
        }

        // Check if the Slack user already exists
        $slackuser = DB::table('slack_users')->where('slack_userid', $auth->user_id)->first();
        if(!$slackuser) {
          $userInfo = $this->slackUserInfo($login->access_token, $auth->user_id);
          if($userInfo) {

            // Check if there is already a user account for the email on this slack user
            list($userID, $new) = $this->getOrCreateUser($orgID, $userInfo);
            $this->createSlackUser($userID, $auth->user_id, $auth->user, $orgID);

          } else {
            // Error getting user info
            return redirect('/?error=userinfo');
          }
        } else {
          $userID = $slackuser->user_id;
        }

        // Sign the user in
        Auth::loginUsingId($userID);
        return redirect('/dashboard');

      } else {
        // Error with auth.test
        return redirect('/?error=authtest');
      }
    } else {
      // Error getting an access token
      return redirect()->guest('/?error=login');
    }
  }

  public function incoming(Request $request) {
    Log::info(json_encode($request->all()));

    if($request->input('token') != env('SLACK_VERIFICATION_TOKEN'))
      return response("invalid token\n", 403);

    // Check if the team exists
    $team = DB::table('slack_teams')->where('slack_teamid', $request->input('team_id'))->first();
    if(!$team) {
      return response()->json(['text' => 'Your team isn\'t signed up yet. Please visit <'.env('APP_URL').'> to register.']);
    }

    $org = DB::table('orgs')->where('id', $team->org_id)->first();

    // If the Slack user ID doesn't exist, create them and add defaults
    $slackuser = DB::table('slack_users')->where('slack_userid', $request->input('user_id'))->first();

    $newUser = false;

    if(!$slackuser) {
      // Look up the user info for this slack user since they might already have an account in the org with the same email
      $userInfo = $this->slackUserInfo($team->slack_token, $request->input('user_id'));
      if($userInfo) {

        // Create the new user account or look up existing
        list($userID, $newUser) = $this->getOrCreateUser($org->id, $userInfo);

        // Add the slack user record linked to the user account
        $slackuserID = $this->createSlackUser($userID, $request->input('user_id'), $userInfo->user->name, $org->id);
      } else {
        return response()->json(['text' => 'There was a problem looking up your account info.']);
      }
    } else {
      $userID = $slackuser->user_id;
    }

    $user = DB::table('users')->where('id', $userID)->first();

    $groupWasCreated = false;

    // Check if there is a group associated with this slack channel
    $channel = DB::table('slack_channels')->where('org_id', $org->id)->where('slack_channelid', $request->input('channel_id'))->first();
    if($channel) {
      $groupID = $channel->group_id;
      // add a "subscription" record for this user if it's not there yet
      $subscription = DB::table('subscriptions')->where('group_id', $channel->group_id)->where('user_id', $userID)->first();
      if(!$subscription) {
        DB::table('subscriptions')->insert([
          'user_id' => $userID,
          'group_id' => $channel->group_id,
          'frequency' => 'daily',
          'daily_localtime' => 21,
          'created_at' => date('Y-m-d H:i:s')
        ]);
      }
    } else {
      // Existing users posting in a new channel create a new group
      if($newUser == false) {
        $groupID = DB::table('groups')->insertGetId([
          'org_id' => $org->id,
          'shortname' => ($request->input('channel_name') == 'general' ? $org->shortname : $request->input('channel_name')),
          'created_at' => date('Y-m-d H:i:s'),
          'created_by' => $userID,
          'timezone' => $user->timezone,
        ]);
        DB::table('slack_channels')->insertGetId([
          'slack_team_id' => $team->id,
          'slack_channelid' => $request->input('channel_id'),
          'slack_channelname' => $request->input('channel_name'),
          'org_id' => $org->id,
          'group_id' => $groupID,
          'created_at' => date('Y-m-d H:i:s')
        ]);
        $subscription = false;
        DB::table('subscriptions')->insert([
          'user_id' => $userID,
          'group_id' => $groupID,
          'frequency' => 'daily',
          'daily_localtime' => 21,
          'created_at' => date('Y-m-d H:i:s')
        ]);
        $groupWasCreated = true;
      } else {
        $groupID = null;
      }
    }

    if($groupID) {
      $group = DB::table('groups')->where('id', $groupID)->first();
    } else {
      $group = null;
    }

    if($request->input('command') == '/squash') {

      $tokenData = [
        'user_id' => $userID,
        'exp' => time() + 300
      ];
      $link = env('APP_URL').'/auth/login?token='.JWT::encode($tokenData, env('APP_KEY'));

      return response()->json(['text' => '<'.$link.'|Click to log in>']);
    } else {
      // Add the entry
      DB::table('entries')->insert([
        'org_id' => $org->id,
        'user_id' => $userID,
        'group_id' => $groupID,
        'created_at' => date('Y-m-d H:i:s'),
        'command' => str_replace('/','',$request->input('command')),
        'text' => $request->input('text'),
        'slack_userid' => $request->input('user_id'),
        'slack_username' => $request->input('user_name'),
        'slack_channelid' => $request->input('channel_id'),
        'slack_channelname' => $request->input('channel_name'),
      ]);

      if($newUser) {
        $msg = 'Welcome! Looks like this is your first time using Done Reports.';
        $this->replyViaSlack($request->input('response_url'), $msg);
      }

      if($groupWasCreated) {
        $msg = 'This was the first message posted in #'.$request->input('channel_name').' so I created a new Done Reports group for you!';
        $this->replyViaSlack($request->input('response_url'), $msg);
      } else {
        if($group && !$subscription) {
          $msg = 'Since this is your first time posting here, you are now subscribed to the "'.$group->shortname.'" group.';
          $this->replyViaSlack($request->input('response_url'), $msg);
        }
      }

      $reply = 'Thanks, '.$request->input('user_name').'!';
      if($group) {
        $reply .= ' I added your entry to the "'.$group->shortname.'" group!';
      }

      $this->replyViaSlack($request->input('response_url'), $reply, ['response_type' => 'ephemeral']);

      return response()->json(['response_type' => 'in_channel']);
    }
  }

  private function slackUserInfo($token, $userID) {
    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', 'https://slack.com/api/users.info', [
      'query' => [
        'token' => $token,
        'user' => $userID
      ]
    ]);
    Log::info("users.info: ".$res->getBody());
    $userInfo = json_decode($res->getBody());
    if($userInfo && $userInfo->ok) {
      return $userInfo;
    } else {
      return false;
    }
  }

  private function getOrCreateUser($orgID, $userInfo) {
    // Check if there is already a user account for the email on this slack user
    $user = DB::table('users')
      ->where('org_id', $orgID)
      ->where('email', $userInfo->user->profile->email)
      ->first();
    if($user) {
      $userID = $user->id;
      $new = false;
    } else {
      $userID = DB::table('users')->insertGetId([
        'org_id' => $orgID,
        'username' => $userInfo->user->name,
        'email' => $userInfo->user->profile->email,
        'display_name' => $userInfo->user->profile->real_name,
        'photo_url' => $userInfo->user->profile->image_512,
        'timezone' => $userInfo->user->tz,
        'created_at' => date('Y-m-d H:i:s')
      ]);
      $new = true;
    }
    return [$userID, $new];
  }

  private function createSlackUser($userID, $slackUserID, $slackUsername, $orgID) {
    return DB::table('slack_users')->insertGetId([
      'org_id' => $orgID,
      'slack_userid' => $slackUserID,
      'slack_username' => $slackUsername,
      'user_id' => $userID,
      'created_at' => date('Y-m-d H:i:s')
    ]);
  }

  private function replyViaSlack($url, $text) {
    $job = (new ReplyViaSlack($url, $text))->onQueue(env('QUEUE_NAME'));
    $this->dispatch($job);
  }

}
