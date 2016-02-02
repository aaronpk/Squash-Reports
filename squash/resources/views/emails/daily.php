<html>
<body style="margin: 0; padding: 0;">

  <div style="background-color: #6d812f; padding: 30px 10px; text-align: center; font-size: 22pt; color: #d5e79f;">
    Here's what <span style="color: #fff;"><?= $group->shortname ?></span> has done recently.
  </div>

  <div style="padding: 20px 10px; background-color: #ffffff;">

    <div style="margin-bottom: 20px;">
      This report was generated on <?= $date->format('D M j, Y g:ia (P)') ?> and includes entries from <?= $from->format('D g:ia') ?> to <?= $to->format('D g:ia') ?>.
    </div>

    @foreach($users as $user)
      <h3 style="font-size: 16pt; color: #6a8711; font-weight: bold;">{{ $user['user']->display_name ?: '@'.$user['user']->username }}</h3>
      <div style="margin-left: 20px;">
        <ul class="entry-list-compact">
          @foreach($user['entries'] as $entry)
            <li>
              <a href="{{ env('APP_URL') }}/{{ $org->shortname }}/entry/{{ $entry->id }}" style="color:#8e9d60;">@entrytime($entry)</a>
              <span class="text"><span style="color: #888;">/{{ $entry->command }}</span> {!! App\TextFormatter::format($entry->text, $org) !!}</span>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach

  </div>

  <div style="font-size: 9pt; text-align: center; padding-top: 10px; border-top: 3px #6d812f solid;">
    This email was sent to <?= implode(', ', array_map(function($u){ return $u->email; }, $subscribers)) ?> for the "<?= $group->shortname ?>" team. <a href="{{ env('APP_URL') }}/{{ $org->shortname }}/group/{{ $group->shortname }}">Change your subscription preferences</a>.
  </div>
</div>
