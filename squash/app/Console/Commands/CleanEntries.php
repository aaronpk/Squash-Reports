<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use PDO;

class CleanEntries extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'import:clean';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clean up slack markup from entries';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $entries = DB::table('entries')->where('created_at', '>', '2015-09-01')->get();
    foreach($entries as $e) {
      $text = $e->text;

      $text = str_replace([
        '&lt;', '&gt;', '&amp;'
        ], [
        '<', '>', '&'
      ], $text);

      if(preg_match_all('/<@(U[^>]+)>/', $text, $matches)) {
        foreach($matches[1] as $id) {
          $user = DB::table('users')->join('slack_users', 'slack_users.user_id','=','users.id')
            ->where('slack_userid', $id)->first();
          if($user) {
            $text = str_replace('<@'.$id.'>', '@'.$user->username, $text);
          }
        }
      }

      $text = preg_replace('/<(http[^>\|]+)>/', '$1', $text);
      $text = preg_replace('/<(http[^>\|]+)\|([^>\|]+)>/', '$2', $text);

      if($text != $e->text) {
        DB::table('entries')
          ->where('id', $e->id)
          ->update(['text' => $text]);
      }
    }

  }

}
