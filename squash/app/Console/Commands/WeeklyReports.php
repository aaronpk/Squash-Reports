<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DB, Log;
use DateTime, DateTimeZone;
use App\Jobs\SendReport;

class WeeklyReports extends Command
{
  use DispatchesJobs;

  protected $signature = 'report:weekly';

  protected $description = 'Queue all weekly reports for sending for the current day and hour';

  public function handle()
  {
    $groups = DB::table('subscriptions')
      ->select('groups.*', 'daily_localtime', 'weekly_dow', DB::raw('GROUP_CONCAT(user_id) AS users'))
      ->join('groups', 'subscriptions.group_id','=','groups.id')
      ->where('frequency', 'weekly')
      ->groupBy('group_id', 'weekly_dow', 'daily_localtime')
      ->get();

    foreach($groups as $group) {
      // Check if the current time in the group's timezone matches the subscription time
      $date = new DateTime();
      if($group->timezone) {
        try {
          $date->setTimeZone(new DateTimeZone($group->timezone));
        } catch(\Exception $e) {
        }
      }
      if((int)$date->format('G') == $group->daily_localtime && (int)$date->format('N') == $group->weekly_dow) {
        Log::info('Queuing a weekly report for '.$group->shortname.' at '.$date->format('g:ia P'));
        $this->dispatch(new SendReport($date->format('Y-m-d\TH:00:00P'), $group->id, explode(',',$group->users), 'weekly'));
      }
    }
  }

}
