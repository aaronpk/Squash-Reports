<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use DB;
use Auth;

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

    public function dashboard(Request $request) {
      list($user, $org) = self::logged_in();

      $groups = DB::table('groups')->where('org_id', $user->org_id)->get();
      $following = DB::table('following')->where('user_id', $user->id)->lists('group_id');

      $my_groups = array_filter($groups, function($g) use($following) {
        return in_array($g->id, $following);
      });
      $other_groups = array_filter($groups, function($g) use($following) {
        return !in_array($g->id, $following);
      });

      return view('dashboard', [
        'org' => $org,
        'user' => $user,
        'my_groups' => $my_groups,
        'other_groups' => $other_groups
      ]);
    }

    public function user_profile(Request $request) {
      list($user, $org) = self::logged_in();

      $groups = DB::table('groups')->where('org_id', $user->org_id)->get();
      $following = DB::table('following')->where('user_id', $user->id)->lists('group_id');

      $my_groups = array_filter($groups, function($g) use($following) {
        return in_array($g->id, $following);
      });

      $entries = DB::table('entries')
        ->select('entries.*', 'groups.shortname AS groupname', 'users.username', 'users.display_name', 'users.photo_url', 'users.timezone')
        ->join('groups', 'entries.group_id','=','groups.id')
        ->join('users', 'entries.user_id','=','users.id')
        ->where('entries.user_id', $user->id)->limit(30)->get();

      return view('profile', [
        'org' => $org,
        'user' => $user,
        'my_groups' => $my_groups,
        'entries' => $entries
      ]);
    }

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }
}
