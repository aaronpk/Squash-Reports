@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo">
  </div>
  <div id="profile_contents">

    <div id="profile_text">
      <div id="profile_info">
        @if($group->photo_url)
          <div id="profile_photo"><img src="{{ $group->photo_url }}"></div>
        @endif
        <div id="profile_bio">
          <h2>#{{ $group->shortname }}</h2>

          <br>

          <button class="ui primary button" style="width: 208px;">Subscribe</button>

          <div class="subscribers">
            <span><i class="users icon"></i> {{ count($subscribers) }} Subscriber{{ count($subscribers) == 1 ? '' : 's' }}</span>
            <ul class="group_subscribers">
              @foreach($subscribers as $u)
                <li><a href="/{{ $org->shortname }}/{{ $u->username }}"><img src="{{ $u->photo_url }}" class="profile-photo" width="48"></a></li>
              @endforeach
            </ul>
          </div>

        </div>
      </div>
      <div id="group_entries">

        <div class="user-list">
          <div class="nav">
            <div class="link left">
              @if($previous)
                <a href="/{{ $org->shortname }}/group/{{ $group->shortname }}/{{ $previous->format('Y-m-d') }}"><i class="caret left icon"></i></a>
              @else
                <span style="opacity:0"><i class="caret left icon"></i></span>
              @endif
            </div>
            <div class="date">{{ $date->format('F j, Y') }}</div>
            <div class="link right">
              @if($next)
                <a href="/{{ $org->shortname }}/group/{{ $group->shortname }}/{{ $next->format('Y-m-d') }}"><i class="caret right icon"></i></a>
              @else
                <span style="opacity:0"><i class="caret right icon"></i></span>
              @endif
            </div>
          </div>

          @foreach($users as $user)
            <div class="user">
              <div class="header">
                <div class="left">
                  <img src="{{ $user['user']->photo_url }}" class="u-photo profile-photo" width="48">
                </div>
                <div class="right">
                  <div class="author p-author h-card">
                    <div class="nickname"><a href="/{{ $org->shortname }}/{{ $user['user']->username }}" class="u-url p-nickname">{{ '@'.$user['user']->username }}</a></div>
                    @if($user['user']->display_name)
                      <div class="name"><a href="/{{ $org->shortname }}/{{ $user['user']->username }}" class="p-name">{{ $user['user']->display_name }}</a></div>
                    @endif
                  </div>
                </div>
              </div>

              <ul class="entry-list-compact">
                @foreach($user['entries'] as $entry)
                  @include('components/entry-compact')
                @endforeach
              </ul>
            </div>
          @endforeach
        </div>

      </div>
    </div>

  </div>
</div>
@endsection
