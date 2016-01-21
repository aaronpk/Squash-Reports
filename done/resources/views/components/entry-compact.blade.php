<li class="h-entry entry-compact">
  <div class="content">
    <a href="/{{ $org->shortname }}/entry/{{ $entry->id }}" class="u-url">
      @entrytime($entry)
    </a>
    <span class="text"><span class="command">/{{ $entry->command }}</span> {!! App\TextFormatter::format($entry->text, $org) !!}</span>
  </div>
  <!--
  <div class="footer">
    <div class="footer-actions">
      <span class="action"><a href=""><i class="star icon"></i></a> {{ $entry->num_likes ?: '' }}</span>
      <span class="action"><a href=""><i class="reply icon"></i></a> {{ $entry->num_comments ?: '' }}</span>
      <a href="/{{ $org->shortname }}/entry/{{ $entry->id }}" class="u-url">
        @entrytime($entry)
      </a>
    </div>
  </div>
  -->
</li>
