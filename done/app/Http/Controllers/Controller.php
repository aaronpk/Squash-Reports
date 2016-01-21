<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use DB;
use Auth;
use DateTime, DateTimeZone;

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
        ->where('subscriptions.user_id', $who->id)
        ->orderBy('entries.created_at', 'desc')
        ->limit(20)->get();

      $likes = $this->collectUserLikesOfEntries($who, $entries);

      return view('dashboard', [
        'org' => $org,
        'user' => $who,
        'my_groups' => $my_groups,
        'other_groups' => $other_groups,
        'entries' => $entries,
        'likes' => $likes
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

      return view('profile', [
        'org' => $org,
        'user' => $user,
        'my_groups' => $my_groups,
        'entries' => $entries,
        'likes' => $likes
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

    public function like_entry(Request $request) {
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

    public function subscribe(Request $request) {
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

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }
}
