<li class="h-entry entry-compact">
  <div class="content">
    <div class="entry-actions timestamp">
      <span class="action"><a href="" data-entry-id="{{ $entry->id }}" class="like-entry {{ in_array($entry->id, $likes) ? 'active' : '' }}"><i class="star icon"></i> <span class="num">{{ $entry->num_likes ?: '' }}</span></a></span>
      {{-- <span class="action"><a href=""><i class="reply icon"></i></a> {{ $entry->num_comments ?: '' }}</span> --}}
      <a href="/{{ $org->shortname }}/entry/{{ $entry->id }}" class="u-url">
        @if(isset($year))
          @entrydate($entry)
        @else
          @entrytime($entry)
        @endif
      </a>
    </div>
    <span class="text"><span class="command">/{{ $entry->command }}</span> {!! App\TextFormatter::format($entry->text, $org) !!}</span>
  </div>
</li>
