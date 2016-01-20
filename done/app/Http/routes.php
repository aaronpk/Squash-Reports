<?php

Route::get('/', 'Controller@index');
Route::post('/slack/incoming', 'Slack@incoming');

Route::get('/auth/logout', 'Controller@logout');

Route::group(['middleware' => ['web']], function () {
  Route::get('/slack/redirect', 'Slack@redirect');
});

Route::group(['middleware' => ['web','auth']], function () {
  Route::get('/dashboard', 'Controller@dashboard');
  Route::get('/{org}/{username}', 'Controller@user_profile');
  Route::get('/{username}/{username}/{group}', 'Controller@user_profile_group');
});
