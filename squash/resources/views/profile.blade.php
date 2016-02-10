@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo" {!! $user->cover_photo ? 'style="background-image:url('.$user->cover_photo.')"' : 'class="none"' !!}>
    @if($user->id == $who->id)
      <div class="edit-cover"><a href="/settings/cover-photo" class="file-input ui small button">{{ $user->cover_photo ? 'Replace' : 'Choose' }} Cover Photo</a></div>
    @endif
    <!--
    <div class="edit-cover"><label for="new_cover_photo" class="file-input ui small button">{{ $user->cover_photo ? 'Replace' : 'Choose' }} Cover Photo</div></div>
    <form action="/action/replace-cover-photo" method="post">
      <input type="file" style="visibility:hidden;" name="new_cover_photo" id="new_cover_photo">
    </form>
    -->
  </div>
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
      <div id="group_entries">

        <div class="group-list">
          <div class="date-nav">
            <div class="link left">
              @if($previous)
                <a href="/{{ $org->shortname }}/{{ $user->username }}/{{ $previous->format('Y-m-d') }}"><i class="caret left icon"></i></a>
              @else
                <span style="opacity:0"><i class="caret left icon"></i></span>
              @endif
            </div>
            <div class="date">{{ $date ? $date->format('l F j, Y') : $year->format('Y') }}</div>
            <div class="link right">
              @if($next)
                <a href="/{{ $org->shortname }}/{{ $user->username }}/{{ $next->format('Y-m-d') }}"><i class="caret right icon"></i></a>
              @else
                <span style="opacity:0"><i class="caret right icon"></i></span>
              @endif
            </div>
          </div>

          @foreach($groups as $group)
            <div class="group">
              <!-- TODO: add the group picture here once that's supported -->
              <div class="groupname"><a href="/{{ $org->shortname }}/group/{{ $group['group']->shortname }}">{{ '#'.$group['group']->shortname }}</a></div>

              <ul class="entry-list-compact">
                {{ $last = false }}
                @foreach($group['entries'] as $entry)
                  @if($year && ($last == false || $last->format('M') != (new DateTime($entry->created_at))->format('M')))
                    <h2>{{ (new DateTime($entry->created_at))->format('M') }}</h2>
                  @endif
                  @include('components/entry-compact', ['last'=>$last=(new DateTime($entry->created_at))])
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
