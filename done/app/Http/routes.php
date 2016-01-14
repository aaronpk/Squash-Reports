<?php

Route::get('/', 'Controller@index');
Route::post('/slack/incoming', 'Slack@incoming');

Route::group(['middleware' => ['web']], function () {
  Route::get('/slack/redirect', 'Slack@redirect');
});

Route::group(['middleware' => ['web','auth']], function () {
  Route::get('/dashboard', 'Controller@dashboard');
  Route::get('/{username:[A-Za-z0-9]+}', 'Controller@user_profile');
  Route::get('/{username:[A-Za-z0-9]+}/{group:[A-Za-z0-9_]+}', 'Controller@user_profile_group');
});
