<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Response;
use DateTimeZone;
use DB, Log;

class Micropub extends BaseController {

  private $_user;
  private $_group;
  private $_org;
  private $_subscription;

  private function _verifyAccessToken(Request $request) {
    $authorization = $request->header('Authorization');
    if($authorization) {
      if(!preg_match('/Bearer (.+)/', $authorization, $match)) {
        // Check for a plain token and reject with a specific error
        if(preg_match('/^[^ ]+$/', $authorization)) {
          $error_description = 'The Authorization header did not contain a Bearer token';
        } else {
          $error_description = 'The Authorization header was invalid';
        }
        return Response::json([
          'error' => 'unauthorized',
          'error_description' => $error_description
        ], 401);
      }
      $token = $match[1];
    } else {
      $token = Request::input('access_token');
    }

    if(!$token) {
      return Response::json([
        'error' => 'unauthorized',
        'error_description' => 'There was no access token in the request'
      ], 401);
    }

    $this->_subscription = DB::table('subscriptions')->where('access_token', $token)->first();

    if(!$this->_subscription) {
      return Response::json([
        'error' => 'unauthorized',
        'error_description' => 'The access token provided was invalid'
      ], 401);
    }

    $this->_user = DB::table('users')->where('id', $this->_subscription->user_id)->first();
    $this->_group = DB::table('groups')->where('id', $this->_subscription->group_id)->first();

    if(!$this->_user || !$this->_group) {
      return Response::json([
        'error' => 'unauthorized',
        'error_description' => 'The access token provided was invalid'
      ], 401);
    }

    $this->_org = DB::table('orgs')->where('id', $this->_group->org_id)->first();

    return true;
  }

  public function post(Request $request) {
    if(($r = $this->_verifyAccessToken($request)) !== true) {
      return $r;
    }

    $input = file_get_contents('php://input');
    $micropub = \p3k\Micropub\Request::createFromString($input);

    if($micropub->error) {
      return Response::json([
        'error' => $micropub->error,
        'error_property' => $micropub->error_property,
        'error_description' => $micropub->error_description,
      ], 400);
    }

    if($micropub->action != 'create') {
      return Response::json([
        'error' => 'unsupported',
        'error_description' => 'Only Micropub creates are supported',
      ], 400);
    }

    $properties = $micropub->properties;

    if(!isset($properties['content'][0]) || !is_string($properties['content'][0])) {
      return Response::json([
        'error' => 'bad_request',
        'error_description' => 'Must provide content as a string',
      ], 400);
    }

    $content = $properties['content'][0];

    // All other Micropub params are currently ignored

    $entryID = DB::table('entries')->insertGetId([
      'org_id' => $this->_org->id,
      'user_id' => $this->_user->id,
      'group_id' => $this->_group->id,
      'created_at' => date('Y-m-d H:i:s'),
      'command' => 'done',
      'text' => $content,
    ]);

    $url = env('APP_URL') . '/' . $this->_org->shortname . '/entry/' . $entryID;

    return Response::json([
      'url' => $url,
    ], 201)->header('Location', $url);
  }

  public function get(Request $request) {
    if(($r = $this->_verifyAccessToken($request)) !== true) {
      return $r;
    }

    return Response::json(null);
  }

}
