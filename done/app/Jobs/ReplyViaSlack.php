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
  private $_opts;

  public function __construct($url, $text, $opts=[]) {
    $this->_url = $url;
    $this->_text = $text;
    $this->_opts = $opts;
  }

  public function handle() {
    Log::info("Posting to ".$this->_url."\n".$this->_text);

    $params = [
      'text' => $this->_text
    ];

    if(array_key_exists('response_type', $this->_opts))
      $params['response_type'] = $this->_opts['response_type'];

    $client = new GuzzleHttp\Client();
    $res = $client->request('POST', $this->_url, [
      'json' => $params
    ]);
  }
}
