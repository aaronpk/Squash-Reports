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

    public function dashboard(Request $request) {
      $user = Auth::user();

      $groups = DB::table('groups')->where('org_id', $user->org_id)->get();
      $following = DB::table('following')->where('user_id', $user->id)->lists('group_id');

      $my_groups = array_filter($groups, function($g) use($following) {
        return in_array($g->id, $following);
      });
      $other_groups = array_filter($groups, function($g) use($following) {
        return !in_array($g->id, $following);
      });

      return view('dashboard', [
        'my_groups' => $my_groups,
        'other_groups' => $other_groups
      ]);
    }

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }
}
