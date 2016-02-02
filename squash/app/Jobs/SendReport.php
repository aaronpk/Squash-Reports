<?php
namespace App\Jobs;

use GuzzleHttp;
use Log, DB, Mail;
use App\Jobs\Job;
use DateTime, DateTimeZone, DateInterval;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReport extends Job implements SelfHandling, ShouldQueue {

  private $_date;
  private $_groupID;
  private $_users;

  public function __construct($date, $groupID, $users) {
    $this->_date = $date;
    $this->_groupID = $groupID;
    $this->_users = $users;
  }

  public function handle() {
    $group = DB::table('groups')->where('id', $this->_groupID)->first();
    $org = DB::table('orgs')->where('id', $group->org_id)->first();
    $subscribers = DB::table('users')->whereIn('id', $this->_users)->get();

    Log::info('Sending report for ' . $group->shortname . ' to ' . count($subscribers) . ' users');

    $date = new DateTime($this->_date);
    $date->setTimeZone(new DateTimeZone($group->timezone));

    $from = new DateTime($date->format('c'));
    $from->setTimeZone(new DateTimeZone('UTC'));
    $from->sub(new DateInterval('P1D'));
    $to = new DateTime($date->format('c'));
    $to->setTimeZone(new DateTimeZone('UTC'));

    $entries = DB::table('entries')
      ->select('entries.*', 'groups.timezone')
      ->join('groups', 'entries.group_id','=','groups.id')
      ->where('group_id', $group->id)
      ->where('entries.created_at', '>=', $from->format('Y-m-d H:i:s'))
      ->where('entries.created_at', '<=', $to->format('Y-m-d H:i:s'))
      ->orderBy('entries.created_at', 'asc')
      ->get();

    if(count($entries) == 0) {
      Log::info('No entries for this group');
      return;
    }

    $users = [];
    foreach($entries as $e) {
      if(!array_key_exists($e->user_id, $users)) {
        $users[$e->user_id] = [
          'user' => DB::table('users')->where('id', $e->user_id)->first(),
          'entries' => []
        ];
      }

      $users[$e->user_id]['entries'][] = $e;
    }

    $from->setTimeZone(new DateTimeZone($group->timezone));
    $to->setTimeZone(new DateTimeZone($group->timezone));

    $data = [
      'org' => $org,
      'group' => $group,
      'users' => $users,
      'subscribers' => $subscribers,
      'date' => $date,
      'from' => $from,
      'to' => $to,
    ];

    Mail::send('emails.daily', $data, function($message) use($data) {
      $message->from(env('MAIL_FROM'), env('MAIL_FROM_NAME'));
      $to = [];
      foreach($data['subscribers'] as $subscriber) {
        if($subscriber->email) {
          $message->to($subscriber->email);
          $to[] = $subscriber->email;
        }
      }
      $message->subject('Report for '.$data['group']->shortname);
      Log::info('Sent email to '.implode(', ', $to));
    });

  }
}
