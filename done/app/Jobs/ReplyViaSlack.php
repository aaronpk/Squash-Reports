<?php
namespace App\Jobs;

use GuzzleHttp;
use Log;
use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReplyViaSlack extends Job implements SelfHandling, ShouldQueue {

  private $_url;
  private $_text;

  public function __construct($url, $text) {
    $this->_url = $url;
    $this->_text = $text;
  }

  public function handle() {
    Log::info("Posting to ".$this->_url."\n".$this->_text);

    $client = new GuzzleHttp\Client();
    $res = $client->request('POST', $this->_url, [
      'json' => [
        'text' => $this->_text
      ]
    ]);
  }
}
