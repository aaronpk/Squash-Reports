<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DB, Log;
use DateTime, DateTimeZone;
use App\Jobs\SendReport;

class DailyReports extends Command
{
  use DispatchesJobs;

  protected $signature = 'report:daily';

  protected $description = 'Queue all daily reports for sending for the current hour';

  public function handle()
  {
    $groups = DB::table('subscriptions')
      ->select('groups.*', 'daily_localtime', DB::raw('GROUP_CONCAT(user_id) AS users'))
      ->join('groups', 'subscriptions.group_id','=','groups.id')
      ->where('frequency', 'daily')
      ->groupBy('group_id', 'daily_localtime')
      ->get();

    foreach($groups as $group) {
      // Check if the current time in the group's timezone matches the subscription time
      $date = new DateTime();
      if($group->timezone) {
        $date->setTimeZone(new DateTimeZone($group->timezone));
      }
      if((int)$date->format('G') == $group->daily_localtime) {
        Log::info('Queuing a report for '.$group->shortname.' at '.$date->format('g:ia P'));
        $this->dispatch(new SendReport($date->format('Y-m-dTH:00:00P'), $group->id, explode(',',$group->users)));
      }
    }
  }

}
