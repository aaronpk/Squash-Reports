<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Firebase\JWT\JWT;

class Login extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'login {org} {user}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Print a login link for the given user';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $org = DB::table('orgs')->where('shortname', $this->argument('org'))->first();
    $user = DB::table('users')->where('org_id', $org->id)->where('username', $this->argument('user'))->first();

    $tokenData = [
      'user_id' => $user->id,
      'group_id' => false,
      'channel_id' => false,
      'org_id' => $org->id,
      'exp' => time() + 300
    ];
    $loginLink = env('APP_URL').'/auth/login?token='.JWT::encode($tokenData, env('APP_KEY'));

    $this->info($loginLink);
  }
}
