<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use GuzzleHttp;
use DB;
use Log;
use Auth;

class Slack extends BaseController {

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

      // Look up the team ID in the database
      $slackteam = DB::table('slack_teams')->where('slack_teamid', $login->team_id)->first();
      if(!$slackteam) {

        // Create the org and slack team
        $orgID = DB::table('orgs')->insertGetId([
          'name' => $login->team_name,
          'created_at' => date('Y-m-d H:i:s'),
          'slack_token' => $login->access_token
        ]);

        DB::table('slack_teams')->insertGetId([
          'slack_teamid' => $login->team_id,
          'slack_teamname' => $login->team_name,
          'org_id' => $orgID,
          'created_at' => date('Y-m-d H:i:s')
        ]);

      } else {
        $orgID = $slackteam->org_id;
      }

      // Look up the user info for whoever just logged in
      $res = $client->request('GET', 'https://slack.com/api/auth.test', [
        'query' => [
          'token' => $login->access_token
        ]
      ]);
      Log::info("auth.test: ".$res->getBody());
      $auth = json_decode($res->getBody());
      if($auth && $auth->ok) {

        // Check if the Slack user already exists
        $slackuser = DB::table('slack_users')->where('slack_userid', $auth->user_id)->first();
        if(!$slackuser) {
          $res = $client->request('GET', 'https://slack.com/api/users.info', [
            'query' => [
              'token' => $login->access_token,
              'user' => $auth->user_id
            ]
          ]);
          Log::info("users.info: ".$res->getBody());
          $userInfo = json_decode($res->getBody());
          if($userInfo && $userInfo->ok) {

            // Check if there is already a user account for the email on this slack user
            $user = DB::table('users')
              ->where('org_id', $orgID)
              ->where('email', $userInfo->user->profile->email)
              ->first();
            if($user) {
              $userID = $user->id;
            } else {
              $userID = DB::table('users')->insertGetId([
                'org_id' => $orgID,
                'username' => $userInfo->user->name,
                'email' => $userInfo->user->profile->email,
                'display_name' => $userInfo->user->profile->real_name,
                'photo_url' => $userInfo->user->profile->image_512,
                'timezone' => $userInfo->user->tz,
                'tz_offset' => $userInfo->user->tz_offset,
                'created_at' => date('Y-m-d H:i:s')
              ]);
            }

            $slackuser = DB::table('slack_users')->insertGetId([
              'slack_userid' => $auth->user_id,
              'user_id' => $userID,
              'created_at' => date('Y-m-d H:i:s')
            ]);

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

    //
    Log::info(json_encode($request->all()));

    return 'whee';
  }



}
