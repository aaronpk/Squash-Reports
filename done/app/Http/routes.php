<?php

Route::get('/', 'Controller@index');
Route::post('/slack/incoming', 'Slack@incoming');

Route::get('/auth/logout', 'Controller@logout');

Route::group(['middleware' => ['web']], function () {
  Route::get('/slack/redirect', 'Slack@redirect');
});

Route::group(['middleware' => ['web','auth']], function () {
  Route::get('/dashboard', 'Controller@dashboard');
  Route::get('/{org}/entry/{entry_id}', 'Controller@entry');
  Route::get('/{org}/group/{group}', 'Controller@group_profile');
  Route::get('/{org}/group/{group}/{date}', 'Controller@group_profile');
  Route::get('/{org}/{username}', 'Controller@user_profile');
  Route::get('/{org}/{username}/{group}', 'Controller@user_profile_group');
  Route::post('/action/like-entry', 'Controller@like_entry_json');
  Route::post('/action/subscribe', 'Controller@subscribe_json');
  Route::post('/action/edit-timezone', 'Controller@edit_timezone_json');
});
