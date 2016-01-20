<li class="h-entry entry">
  <div class="header">
    <div class="left">
      <img src="{{ $entry->photo_url }}" class="u-photo profile-photo" width="48">
    </div>
    <div class="right">
      <div class="author p-author h-card">
        <a href="/{{ $org->shortname }}/{{ $entry->username }}" class="u-url">
          <span class="p-name name">{{ $entry->display_name }}</span>
          <span class="p-nickname nickname">{{ '@'.$entry->username }}</span>
        </a>
      </div>
      <div class="group-date">
        <a href="/{{ $org->shortname }}/group/{{ $entry->groupname }}">#{{ $entry->groupname }}</a>
        &middot;
        <a href="/{{ $org->shortname }}/entry/{{ $entry->id }}" class="u-url">
          @entrydate($entry)
        </a>
      </div>
    </div>
  </div>
  <div class="content">
    <span class="command">/{{ $entry->command }}</span> {{ $entry->text }}
  </div>
  <!--
  <div class="footer">
    <div class="footer-actions">
      <a class="action" href=""><i class="star icon"></i> Like</a>
      <a class="action" href=""><i class="reply icon"></i> Comment</a>
    </div>
  </div>
  -->
</li>
