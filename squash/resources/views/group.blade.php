@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo" {!! $group->cover_photo ? 'style="background-image:url('.$group->cover_photo.')"' : 'class="none"' !!}>
    @if($user_subscribed)
      <div class="edit-cover"><a href="/settings/cover-photo?group_id={{ $group->id }}" class="file-input ui small button">{{ $group->cover_photo ? 'Replace' : 'Choose' }} Cover Photo</a></div>
    @endif
  </div>
  <div id="profile_contents">

    <div id="profile_text">
      <div id="profile_info">
        @if($group->photo_url)
          <div id="profile_photo"><img src="{{ $group->photo_url }}"></div>
        @endif
        <div id="profile_bio">
          <h2>#{{ $group->shortname }}</h2>

          <div class="timezone-info">
            <div><i class="clock icon"></i> <span class="timezone-name">{{ $group->timezone }}</span></div>
          </div>

          @if($user_subscribed && $subscription)
            <div class="subscription-info">
              <div><i class="envelope icon"></i> <span class="delivery-time">
                {{ ucfirst($subscription->frequency) }}
                {{ $subscription->frequency == 'weekly' ? ' on '.App\TextFormatter::weekday($subscription->weekly_dow).'s' : '' }}
                at {{ App\TextFormatter::display_hour($subscription->daily_localtime) }}
              </span></div>
            </div>
          @endif

          <br>

          <button class="ui button {{ $user_subscribed ? 'subscribed' : 'not-subscribed' }} subscribe-button" style="width: 208px;" data-group-id="{{ $group->id }}"></button>

          <div class="subscribers">
            <span><i class="users icon"></i> <span id="num-subscribers">{{ count($subscribers) }}</span> Subscriber{{ count($subscribers) == 1 ? '' : 's' }}</span>
            <ul class="group_subscribers">
              <li class="hidden me_new"><a href="/{{ $org->shortname }}/{{ $who->username }}"><img src="{{ $who->photo_url }}" class="profile-photo" width="48"></a></li>
              @foreach($subscribers as $u)
                <li class="{{ $who->id == $u->user_id ? 'me' : '' }}"><a href="/{{ $org->shortname }}/{{ $u->username }}"><img src="{{ $u->photo_url }}" class="profile-photo" width="48"></a></li>
              @endforeach
            </ul>
          </div>

        </div>
      </div>
      <div id="group_entries">

        <div class="user-list">
          <div class="date-nav">
            <div class="link left">
              @if($previous)
                <a href="/{{ $org->shortname }}/group/{{ $group->shortname }}/{{ $previous->format('Y-m-d') }}"><i class="caret left icon"></i></a>
              @else
                <span style="opacity:0"><i class="caret left icon"></i></span>
              @endif
            </div>
            <div class="date">{{ $date->format('l, F j, Y') }}</div>
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
                  <a href="/{{ $org->shortname }}/{{ $user['user']->username }}">
                    <img src="{{ $user['user']->photo_url }}" class="u-photo profile-photo" width="48">
                  </a>
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
