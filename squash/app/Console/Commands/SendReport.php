<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DB, Log;
use DateTime, DateTimeZone;

class SendReport extends Command
{
  use DispatchesJobs;

  protected $signature = 'report:send {org} {group} {user}';

  protected $description = 'Send the current group report to the given user immediately';

  public function handle()
  {

    $group = DB::table('groups')
      ->select('groups.*')
      ->join('orgs', 'orgs.id','=','groups.org_id')
      ->where('orgs.shortname', $this->argument('org'))
      ->where('groups.shortname', $this->argument('group'))
      ->first();

    if(!$group) {
      $this->error("Group not found");
      return;
    }

    $user = DB::table('users')
      ->select('users.*')
      ->join('orgs', 'orgs.id','=','users.org_id')
      ->where('orgs.shortname', $this->argument('org'))
      ->where('users.username', $this->argument('user'))
      ->first();

    if(!$group) {
      $this->error("User not found");
      return;
    }

    $subscription = DB::table('subscriptions')
      ->where('user_id', $user->id)
      ->where('group_id', $group->id)
      ->first();

    if(!$subscription) {
      $this->error("This user is not subscribed to the group");
      return;
    }

    // Set the date to now
    $date = new DateTime();
    // Convert to a local time
    try {
      $date->setTimeZone(new DateTimeZone($group->timezone));
    } catch(\Exception $e) {
    }
    // Set the hour/minute/second
    $date->setTime($subscription->daily_localtime, 0, 0);

    $job = new \App\Jobs\SendReport($date->format('Y-m-d\TH:i:sP'), $group->id, [$user->id]);
    $job->handle();
  }

}
