<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ImportEmoji extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'emoji:import';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Import emoji from iamcal/emoji-data';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    // First clone the emoji repo into public/emoji
    // git clone --depth 1 https://github.com/iamcal/emoji-data.git
    $emoji = json_decode(file_get_contents(dirname(__FILE__).'/../../../public/emoji/emoji.json'));
    /*     {
        "name": "COPYRIGHT SIGN",
        "unified": "00A9",
        "variations": [
            "00A9-FE0F"
        ],
        "docomo": "E731",
        "au": "E558",
        "softbank": "E24E",
        "google": "FEB29",
        "image": "00a9.png",
        "sheet_x": 0,
        "sheet_y": 0,
        "short_name": "copyright",
        "short_names": [
            "copyright"
        ],
        "text": null,
        "texts": null,
        "category": "Symbols",
        "sort_order": 197,
        "has_img_apple": true,
        "has_img_google": true,
        "has_img_twitter": false,
        "has_img_emojione": true
    } */
    echo "Importing ".count($emoji)." emoji\n";
    foreach($emoji as $e) {
      $existing = DB::table('emoji')->where('org_id', 0)->where('shortname', $e->short_name)->first();
      if($existing) {
        DB::table('emoji')
          ->where('org_id', 0)->where('shortname', $e->short_name)
          ->update(['filename' => $e->image]);
      } else {
        DB::table('emoji')->insert([
          'org_id' => 0,
          'shortname' => $e->short_name,
          'filename' => $e->image
        ]);
      }
    }
  }
}
