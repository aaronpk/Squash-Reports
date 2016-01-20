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
      $groups = DB::table('groups')->where('org_id', $user->org_id)->get();
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

      return view('dashboard', [
        'org' => $org,
        'user' => $who,
        'my_groups' => $my_groups,
        'other_groups' => $other_groups
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
        ->limit(30)->get();

      return view('profile', [
        'org' => $org,
        'user' => $user,
        'my_groups' => $my_groups,
        'entries' => $entries
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
        $date = new DateTime('now', new DateTimeZone($group->timezone));
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

      return view('group', [
        'org' => $org,
        'group' => $group,
        'date' => $date,
        'subscribers' => $subscribers,
        'users' => $users
      ]);
    }

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }
}
