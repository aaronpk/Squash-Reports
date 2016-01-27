<?php

Route::get('/', 'Controller@index');
Route::post('/slack/incoming', 'Slack@incoming');

Route::get('/auth/slack-login', 'Slack@login');

Route::group(['middleware' => ['web']], function () {
  Route::get('/auth/login', 'Controller@login');
  Route::get('/auth/logout', 'Controller@logout');
  Route::get('/slack/redirect', 'Slack@redirect');
});

Route::group(['middleware' => ['web','auth']], function () {
  Route::get('/dashboard', 'Controller@dashboard');
  Route::get('/settings/cover-photo', 'Controller@select_cover_photo');
  Route::get('/{org}/entry/{entry_id}', 'Controller@entry');
  Route::get('/{org}/group/{group}', 'Controller@group_profile');
  Route::get('/{org}/group/{group}/{date}', 'Controller@group_profile');
  Route::get('/{org}/group/{group}/subscribers', 'Controller@group_subscribers');
  Route::get('/{org}/{username}', 'Controller@user_profile');
  Route::get('/{org}/{username}/subscriptions', 'Controller@user_subscriptions');
  #Route::get('/{org}/{username}/{group}', 'Controller@user_profile_group');
  Route::post('/action/like-entry', 'Controller@like_entry_json');
  Route::post('/action/subscribe', 'Controller@subscribe_json');
  Route::post('/action/edit-timezone', 'Controller@edit_timezone_json');
  Route::post('/action/select-cover-photo', 'Controller@select_cover_photo_json');
  Route::post('/action/admin/unsubscribe', 'Controller@admin_unsubscribe_json');
});
