@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo" {!! $user->cover_photo ? 'style="background-image:url('.$user->cover_photo.')"' : 'class="none"' !!}></div>
  <div id="profile_contents">

    <div id="profile_text">
      <div id="profile_info">
        <div id="profile_photo"><img src="{{ $user->photo_url }}"></div>
        <div id="profile_bio">
          <h2>{{ $user->display_name ?: '@'.$user->username }}</h2>
          @if($user->display_name)
            <h3>{{ '@'.$user->username }}</h3>
          @endif
          @if($user->location)
            <div><i class="marker icon"></i> {{ $user->location }}</div>
          @endif
          @if($user->timezone)
            <div class="timezone-info">
              @if($user->id == $who->id)
                <a href="#" class="edit-timezone" data-position="top center" style="float:right; margin-right: 1em;">Change</a>
              @endif
              <div><i class="clock icon"></i> <span class="timezone-name">{{ $user->timezone }}</span></div>
            </div>
            @if($user->id == $who->id)
              <div class="ui special popup timezone-popup">
                <select class="ui dropdown">
                  <option value="">Timezone</option>
                  @foreach($timezones as $timezone)
                    <option value="{{ $timezone }}"{{ $user->timezone == $timezone ? ' selected="selected"' : '' }}>{{ $timezone }}</option>
                  @endforeach
                </select>
              </div>
            @endif
          @endif
          <br>

          <ul class="group_list">
          @foreach($my_groups as $g)
            <li><a href="/{{ $org->shortname }}/group/{{ $g->shortname }}">#{{ $g->shortname }}</a></li>
          @endforeach
          </ul>
        </div>
      </div>
      <div id="profile_feed">

        <ul class="entry-list">
          @foreach($entries as $entry)
            @include('components/entry')
          @endforeach
        </ul>

      </div>
    </div>

  </div>
</div>
@endsection
