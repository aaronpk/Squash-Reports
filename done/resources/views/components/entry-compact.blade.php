<li class="h-entry entry-compact">
  <div class="content">
    <div class="entry-actions timestamp">
      <span class="action"><a href="" data-entry-id="{{ $entry->id }}" class="like-entry {{ in_array($entry->id, $likes) ? 'active' : '' }}"><i class="star icon"></i></a> {{ $entry->num_likes ?: '' }}</span>
      {{-- <span class="action"><a href=""><i class="reply icon"></i></a> {{ $entry->num_comments ?: '' }}</span> --}}
      <a href="/{{ $org->shortname }}/entry/{{ $entry->id }}" class="u-url">
        @entrytime($entry)
      </a>
    </div>
    <span class="text"><span class="command">/{{ $entry->command }}</span> {!! App\TextFormatter::format($entry->text, $org) !!}</span>
  </div>
</li>
