<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use PDO;

class ImportDone extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'import:donereports';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Import history from !done reports';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $from = new PDO('mysql:host=127.0.0.1;dbname=donereports', 'root', '');

    $from_org_id = 1;
    $to_org_id = 6;

    $entries = $from->prepare('SELECT entries.*,
        groups.name as group_name, groups.due_timezone as timezone,
        users.email, users.username
      FROM entries
      JOIN groups ON entries.group_id = groups.id
      JOIN users ON entries.user_id = users.id
      WHERE groups.org_id = ?
      ORDER BY entries.date
      ');
    $entries->bindValue(1, $from_org_id);
    $entries->execute();

    while($entry = $entries->fetch(PDO::FETCH_OBJ)) {
      $email = strtolower($entry->email);

      // Find or create the user account
      $user = DB::table('users')->where('email', $email)->where('org_id', $to_org_id)->first();
      if(!$user) {
        $userID = DB::table('users')->insertGetId([
          'org_id' => $to_org_id,
          'username' => $entry->username,
          'email' => $email,
          'timezone' => $entry->timezone,
          'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->info($entry->date.' Created user: '.$email);
      } else {
        $userID = $user->id;
      }

      // Find or create the group
      $shortname = preg_replace('/ /', '', strtolower($entry->group_name));
      $group = DB::table('groups')->where('org_id', $to_org_id)->where('shortname', $shortname)->first();
      if(!$group) {
        $groupID = DB::table('groups')->insertGetId([
          'org_id' => $to_org_id,
          'shortname' => $shortname,
          'timezone' => $entry->timezone,
          'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->info($entry->date.' Created group: '.$shortname);
      } else {
        $groupID = $group->id;
      }

      // Subscribe the user to the group
      $subscription = DB::table('subscriptions')->where('user_id', $userID)->where('group_id', $groupID)->first();
      if(!$subscription) {
        // Check if they are a member of the group in the old version
        $check = $from->prepare('SELECT * FROM group_users WHERE user_id = ? AND group_id = ?');
        $check->bindValue(1, $entry->user_id);
        $check->bindValue(2, $entry->group_id);
        $check->execute();
        if($check->fetch()) {
          DB::table('subscriptions')->insert([
            'user_id' => $userID,
            'group_id' => $groupID,
            'frequency' => 'daily',
            'daily_localtime' => 21,
            'created_at' => date('Y-m-d H:i:s')
          ]);
          $this->info($entry->date.' Subscribed user '.$email.' to group '.$shortname);
        }
      }

      // Add the entry
      $command = $entry->type;
      switch($entry->type) {
        case 'past':
          $command = 'done'; break;
        case 'block':
          $command = 'blocking'; break;
        case 'future':
          $command = 'todo'; break;
      }

      $text = $entry->message;
      $text = str_replace([
        '&lt;', '&gt;', '&amp;'
        ], [
        '<', '>', '&'
      ], $text);

      $text = preg_replace('/<(http[^>\|]+)>/', '$1', $text);
      $text = preg_replace('/<(http[^>\|]+)\|([^>\|]+)>/', '$2', $text);

      DB::table('entries')->insert([
        'org_id' => $to_org_id,
        'user_id' => $userID,
        'group_id' => $groupID,
        'created_at' => $entry->date,
        'command' => $command,
        'text' => $text,
      ]);

    }

  }
}
