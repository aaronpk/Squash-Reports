<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use DB, Auth, Storage;
use DateTime, DateTimeZone;
use \Firebase\JWT\JWT;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request) {
      return view('index');
    }

    public static function logged_in() {
      $user = Auth::user();
      $org = DB::table('orgs')->where('id', $user->org_id)->first();
      return [$user, $org];
    }

    public static function load_user_groups($user) {
      $groups = DB::table('groups')->where('org_id', $user->org_id)->orderBy('shortname','asc')->get();
      $subscriptions = DB::table('subscriptions')->where('user_id', $user->id)->lists('group_id');

      $my_groups = array_filter($groups, function($g) use($subscriptions) {
        return in_array($g->id, $subscriptions);
      });
      $other_groups = array_filter($groups, function($g) use($subscriptions) {
        return !in_array($g->id, $subscriptions);
      });

      return [$my_groups, $other_groups];
    }

    public function dashboard(Request $request) {
      list($who, $org) = self::logged_in();

      list($my_groups, $other_groups) = self::load_user_groups($who);

      $entries = DB::table('entries')
        ->select('entries.*', 'groups.shortname AS groupname', 'users.username', 'users.display_name', 'users.photo_url', 'users.timezone')
        ->join('groups', 'entries.group_id','=','groups.id')
        ->join('users', 'entries.user_id','=','users.id')
        ->join('subscriptions', 'entries.group_id', '=', 'subscriptions.group_id')
        ->where('users.id', $who->id)
        ->orderBy('entries.created_at', 'desc')
        ->limit(20);

      if($request->search) {
        $query = preg_replace('/[^a-z0-9_\-\.]/i', ' ', $request->search);
        $entries = $entries->whereRaw('MATCH (text) AGAINST ("'.$query.'" IN BOOLEAN MODE)');
      } else {
        $query = '';
      }

      $entries = $entries->get();

      $likes = $this->collectUserLikesOfEntries($who, $entries);

      return view('dashboard', [
        'org' => $org,
        'user' => $who,
        'my_groups' => $my_groups,
        'other_groups' => $other_groups,
        'entries' => $entries,
        'likes' => $likes,
        'search' => $query
      ]);
    }

    public function user_profile(Request $request) {
      list($who, $org) = self::logged_in();

      $user = DB::table('users')->where('org_id', $who->org_id)->where('username', $request->username)->first();
      if(!$user) {
        return 'not found';
      }

      list($my_groups, $other_groups) = self::load_user_groups($user);

      $entries = DB::table('entries')
        ->select('entries.*', 'groups.shortname AS groupname', 'users.username', 'users.display_name', 'users.photo_url', 'users.timezone')
        ->join('groups', 'entries.group_id','=','groups.id')
        ->join('users', 'entries.user_id','=','users.id')
        ->where('entries.user_id', $user->id)
        ->orderBy('entries.created_at', 'desc')
        ->limit(20)->get();

      $likes = $this->collectUserLikesOfEntries($who, $entries);

      $timezones = ["America/Los_Angeles", "America/Chicago", "America/New_York", "America/Phoenix", "Asia/Hong_Kong", "Europe/Berlin", "Europe/Dublin", "Europe/Amsterdam", "Europe/London", "Europe/Stockholm", "Europe/Zurich"];

      return view('profile', [
        'org' => $org,
        'user' => $user,
        'who' => $who,
        'my_groups' => $my_groups,
        'entries' => $entries,
        'likes' => $likes,
        'timezones' => $timezones
      ]);
    }

    public function group_profile(Request $request) {
      list($who, $org) = self::logged_in();

      $group = DB::table('groups')->where('org_id', $who->org_id)->where('shortname', $request->group)->first();
      if(!$group) {
        return 'not found';
      }

      $subscribers = DB::table('users')
        ->join('subscriptions', 'users.id','=','subscriptions.user_id')
        ->where('subscriptions.group_id', $group->id)
        ->orderBy('users.username', 'asc')
        ->get();

      if($request->date) {
        try {
          $date = new DateTime($request->date, new DateTimeZone($group->timezone));
        } catch(Exception $e) {
          return 'invalid date';
        }
      } else {
        $latestEntry = DB::table('entries')
          ->where('group_id', $group->id)
          ->orderBy('created_at', 'desc')
          ->limit(1)
          ->first();
        if($latestEntry) {
          $date = new DateTime($latestEntry->created_at);
          $date->setTimeZone(new DateTimeZone($group->timezone));
        } else {
          $date = new DateTime('now', new DateTimeZone($group->timezone));
        }
      }

      $from = new DateTime($date->format('Y-m-d 00:00:00'), new DateTimeZone($group->timezone));
      $from->setTimeZone(new DateTimeZone('UTC'));
      $to = new DateTime($date->format('Y-m-d 23:59:59'), new DateTimeZone($group->timezone));
      $to->setTimeZone(new DateTimeZone('UTC'));

      $entries = DB::table('entries')
        ->select('entries.*', 'groups.timezone')
        ->join('groups', 'entries.group_id','=','groups.id')
        ->where('group_id', $group->id)
        ->where('entries.created_at', '>=', $from->format('Y-m-d H:i:s'))
        ->where('entries.created_at', '<=', $to->format('Y-m-d H:i:s'))
        ->orderBy('entries.created_at', 'asc')
        ->get();

      $previousEntry = DB::table('entries')
        ->where('group_id', $group->id)
        ->where('created_at', '<', $from->format('Y-m-d H:i:s'))
        ->orderBy('created_at', 'desc')
        ->limit(1)
        ->first();
      if($previousEntry) {
        $previous = new DateTime($previousEntry->created_at);
        $previous->setTimeZone(new DateTimeZone($group->timezone));
      } else {
        $previous = false;
      }

      $nextEntry = DB::table('entries')
        ->where('group_id', $group->id)
        ->where('created_at', '>', $to->format('Y-m-d H:i:s'))
        ->orderBy('created_at', 'asc')
        ->limit(1)
        ->first();
      if($nextEntry) {
        $next = new DateTime($nextEntry->created_at);
        $next->setTimeZone(new DateTimeZone($group->timezone));
      } else {
        $next = false;
      }

      $users = [];
      foreach($entries as $e) {
        if(!array_key_exists($e->user_id, $users)) {
          $users[$e->user_id] = [
            'user' => DB::table('users')->where('id', $e->user_id)->first(),
            'entries' => []
          ];
        }

        $users[$e->user_id]['entries'][] = $e;
      }

      $subscribed = in_array($who->id, array_map(function($s){
        return $s->user_id;
      }, $subscribers));

      $likes = $this->collectUserLikesOfEntries($who, $entries);

      return view('group', [
        'who' => $who,
        'org' => $org,
        'group' => $group,
        'date' => $date,
        'subscribers' => $subscribers,
        'users' => $users,
        'previous' => $previous,
        'next' => $next,
        'likes' => $likes,
        'user_subscribed' => $subscribed
      ]);
    }

    public function group_subscribers(Request $request) {
      list($who, $org) = self::logged_in();

      $group = DB::table('groups')->where('org_id', $who->org_id)->where('shortname', $request->group)->first();
      if(!$group) {
        return 'not found';
      }

      $subscribers = DB::table('users')
        ->join('subscriptions', 'users.id','=','subscriptions.user_id')
        ->where('subscriptions.group_id', $group->id)
        ->orderBy('users.username', 'asc')
        ->get();

      return view('group-subscribers', [
        'who' => $who,
        'org' => $org,
        'group' => $group,
        'subscribers' => $subscribers,
      ]);
    }

    public function user_subscriptions(Request $request) {
      list($who, $org) = self::logged_in();

      $user = DB::table('users')->where('org_id', $who->org_id)->where('username', $request->username)->first();
      if(!$user) {
        return 'not found';
      }

      $subscriptions = DB::table('groups')
        ->join('subscriptions', 'groups.id','=','subscriptions.group_id')
        ->where('subscriptions.user_id', $user->id)
        ->orderBy('groups.shortname', 'asc')
        ->get();

      return view('user-subscriptions', [
        'who' => $who,
        'org' => $org,
        'user' => $user,
        'subscriptions' => $subscriptions,
      ]);
    }

    public function entry(Request $request) {
      list($who, $org) = self::logged_in();

      $entry = DB::table('entries')
        ->select('entries.*', 'groups.shortname AS groupname', 'users.username', 'users.display_name', 'users.photo_url', 'users.timezone')
        ->join('groups', 'entries.group_id','=','groups.id')
        ->join('users', 'entries.user_id','=','users.id')
        ->where('entries.org_id', $who->org_id)
        ->where('entries.id', $request->entry_id)
        ->first();
      if(!$entry) {
        return 'not found';
      }

      $likes = $this->collectUserLikesOfEntries($who, [$entry]);

      return view('entry-permalink', [
        'org' => $org,
        'user' => $who,
        'entry' => $entry,
        'likes' => $likes
      ]);
    }

    public function like_entry_json(Request $request) {
      list($who, $org) = self::logged_in();

      $entry = DB::table('entries')
        ->where('org_id', $org->id)
        ->where('id', $request->entry_id)
        ->first();

      if(!$entry) {
        return response()->json([
          'error' => 'not found'
        ]);
      } else {

        $like = DB::table('responses')
          ->where('user_id', $who->id)
          ->where('entry_id', $entry->id)
          ->where('like', 1)
          ->first();

        if($like) {
          DB::table('responses')
            ->where('user_id', $who->id)
            ->where('entry_id', $entry->id)
            ->where('like', 1)
            ->delete();
          $state = '';
        } else {
          DB::table('responses')->insert([
            'entry_id' => $entry->id,
            'user_id' => $who->id,
            'created_at' => date('Y-m-d H:i:s'),
            'like' => 1
          ]);
          $state = 'active';
        }

        $likes = DB::table('responses')
          ->where('entry_id', $entry->id)
          ->where('like', 1)
          ->count();

        DB::table('entries')
          ->where('id', $entry->id)
          ->update([
            'num_likes' => $likes
          ]);

        return response()->json([
          'state' => $state,
          'entry_id' => $entry->id,
          'likes' => $likes
        ]);
      }
    }

    public function subscribe_json(Request $request) {
      list($who, $org) = self::logged_in();

      $group = DB::table('groups')
        ->where('org_id', $org->id)
        ->where('id', $request->group_id)
        ->first();

      if(!$group) {
        return response()->json([
          'error' => 'not found'
        ]);
      }

      $subscription = DB::table('subscriptions')
        ->where('group_id', $group->id)
        ->where('user_id', $who->id)
        ->first();
      if(!$subscription) {
        DB::table('subscriptions')->insert([
          'group_id' => $group->id,
          'user_id' => $who->id,
          'frequency' => 'daily',
          'daily_localtime' => 21,
          'created_at' => date('Y-m-d H:i:s')
        ]);
        $state = 'subscribed';
      } else {
        DB::table('subscriptions')
          ->where('group_id', $group->id)
          ->where('user_id', $who->id)
          ->delete();
        $state = 'unsubscribed';
      }

      $num_subscribers = DB::table('subscriptions')
        ->where('group_id', $group->id)
        ->count();

      return response()->json([
        'state' => $state,
        'group_id' => $group->id,
        'num_subscribers' => $num_subscribers
      ]);
    }

    public function admin_unsubscribe_json(Request $request) {
      // Allow group admins to unsubscribe other users from the group
      list($who, $org) = self::logged_in();

      // TODO: check that the authenticated user is the group admin once permissions are added

      $group = DB::table('groups')
        ->select('groups.*')
        ->where('org_id', $org->id)
        ->where('id', $request->group_id)
        ->first();

      if(!$group) {
        return response()->json([
          'error' => 'group not found'
        ]);
      }

      DB::table('subscriptions')
        ->where('group_id', $group->id)
        ->where('user_id', $request->user_id)
        ->delete();
      $state = 'unsubscribed';

      return response()->json([
        'state' => $state,
        'user_id' => $request->user_id,
        'group_id' => $request->group_id
      ]);
    }

    public function edit_timezone_json(Request $request) {
      list($who, $org) = self::logged_in();

      try {
        $tz = new DateTimeZone($request->timezone);
        $timezone = $request->timezone;
      } catch(Exception $e) {
        $timezone = $who->timezone;
      }

      DB::table('users')->where('id', $who->id)->update([
        'timezone' => $timezone,
      ]);

      return response()->json([
        'timezone' => $timezone
      ]);
    }

    public function select_cover_photo(Request $request) {
      list($who, $org) = self::logged_in();
      list($my_groups, $other_groups) = self::load_user_groups($who);

      $photos = Storage::files('photos');
      $photos = array_map(function($p){
        return basename($p);
      }, $photos);

      if($request->group_id) {
        $group = DB::table('groups')->where('org_id', $org->id)->where('id', $request->group_id)->first();
        if(!$group) {
          return redirect('/dashboard');
        }
        $for = '#'.$group->shortname;
      } else {
        $for = 'your profile';
      }

      return view('cover-photo', [
        'org' => $org,
        'user' => $who,
        'photos' => $photos,
        'choose_for' => $for,
        'group_id' => $request->group_id # will be null for editing user profiles
      ]);
    }

    public function select_cover_photo_json(Request $request) {
      list($who, $org) = self::logged_in();

      // TODO: support uploading files and storing on S3
      $photo_url = '/photos/'.$request->photo;

      if($request->group_id) {
        $group = DB::table('groups')->where('org_id', $org->id)->where('id', $request->group_id)->first();
        if(!$group) {
          return response()->json([
            'error' => 'not found'
          ]);
        }

        DB::table('groups')->where('id', $group->id)->update([
          'cover_photo' => $photo_url
        ]);

        $redirect = '/'.$org->shortname.'/group/'.$group->shortname;
      } else {
        DB::table('users')->where('id', $who->id)->update([
          'cover_photo' => $photo_url
        ]);
        $redirect = '/'.$org->shortname.'/'.$who->username;
      }

      return response()->json([
        'url' => $photo_url,
        'redirect' => $redirect
      ]);
    }

    private function collectUserLikesOfEntries($who, $entries) {
      $entryIDs = array_map(function($entry) {
        return $entry->id;
      }, $entries);

      $likes = array_map(function($response){
        return $response->entry_id;
      }, DB::table('responses')
        ->select('entry_id')
        ->whereIn('entry_id', $entryIDs)
        ->where('user_id', $who->id)
        ->where('like', 1)
        ->get());

      return $likes;
    }

    public function login(Request $request) {
      try {
        $tokenData = JWT::decode($request->token, env('APP_KEY'), ['HS256']);
      } catch(\Exception $e) {
        return 'Login link was invalid or expired';
      }

      Auth::loginUsingId($tokenData->user_id);

      // Redirect to the group page that generated the token
      if($tokenData->group_id) {
        $group = DB::table('groups')
          ->select('orgs.shortname AS org', 'groups.shortname AS group')
          ->join('orgs', 'groups.org_id', '=', 'orgs.id')
          ->where('groups.id', $tokenData->group_id)->first();
        return redirect('/'.$group->org.'/group/'.$group->group);
      } else {
        return redirect('/dashboard');
      }
    }

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }
}
