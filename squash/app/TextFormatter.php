<?php
namespace App;
use DB;

class TextFormatter {
  public static function format($text, $org) {
    $text = htmlentities($text);

    // multi-line blockquotes
    $text = preg_replace_callback('/(?:\n|^)(?:>>>|&gt;&gt;&gt;)(.+)/ms', function($matches) {
      return '<blockquote>' . trim($matches[1]) . '</blockquote>';
    }, $text);

    // blockquotes
    $text = preg_replace_callback('/(?:\n|^)(?:>|&gt;)(.+)/', function($matches) {
      return '<blockquote>' . $matches[1] . '</blockquote>';
    }, $text);

    // Slack formatting
    // *bold* _italic_ ~strikethrough~
    $text = preg_replace('/\*([^\*\n]+)\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\_([^\_\n]+)\_/', '<em>$1</em>', $text);
    $text = preg_replace('/~([^~\n]+)~/', '<s>$1</s>', $text);
    $text = preg_replace('/```((?!```).+)```/ms', '<pre>$1</pre>', $text);
    $text = preg_replace('/`([^`\n]+)`/', '<code>$1</code>', $text);

    // Replace * with bullet
    $text = preg_replace('/\n\*/', "\n•", $text);

    // Autolink URLs
    // https://gist.github.com/gruber/8891611
    $text = preg_replace_callback('/(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/',
      function($matches) {
        $url = $matches[1];
        if(substr($url, 0, 4) != 'http') {
          $url = 'http://'.$url;
        }
        return '<a href="'.$url.'">'.$matches[1].'</a>';
      }, $text);

    // Match emoji
    $text = preg_replace_callback('/:([a-z0-9_]+):/', function($matches) use($org) {
      $emoji = DB::table('emoji')
        ->whereIn('org_id', [0, $org->id])
        ->where('shortname', $matches[1])
        ->first();

      if($emoji) {
        return '<img src="/emoji/img-apple-64/'.$emoji->filename.'" alt="'.$matches[1].'" class="emojichar">';
      } else {
        return $matches[1];
      }
    }, $text);

    // Replace #hashtags if they match other group names
    $text = preg_replace_callback('/(?<=\s|^)#([a-z0-9_\-\.]+)/i', function($matches) use($org) {
      $find = $matches[1];
      $group = DB::table('groups')
        ->join('slack_channels', 'groups.id', '=', 'slack_channels.group_id')
        ->where('groups.org_id', $org->id)
        ->where(function($query) use($find){
          $query->where('slack_channelname', $find)
            ->orWhere('groups.shortname', $find);
        })
        ->first();
      if(!$group) {
        return '#'.$matches[1];
      }

      return '<a href="'.env('APP_URL').'/'.$org->shortname.'/group/'.$group->shortname.'">#'.$find.'</a>';
    }, $text);

    // Replace @username references by looking up users in the database
    $text = preg_replace_callback('/(?<=\s|^)@([a-z0-9_\-\.]+)/i', function($matches) use($org) {
      $user = DB::table('slack_users')
        ->select('users.*')
        ->join('users', 'slack_users.user_id', '=', 'users.id')
        ->where('slack_users.org_id', $org->id)
        ->where('slack_username', $matches[1])
        ->first();
      if(!$user) {
        $user = DB::table('users')
          ->where('org_id', $org->id)
          ->where('username', $matches[1])
          ->first();
      }
      if(!$user) {
        return '@'.$matches[1];
      }

      return '<a href="'.env('APP_URL').'/'.$org->shortname.'/'.$user->username.'">@'.$matches[1].'</a>';
    }, $text);

    return $text;
  }
}
