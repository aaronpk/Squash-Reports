@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo">
  </div>
  <div id="profile_contents">

    <div id="profile_text">
      <div id="profile_info">
        <div id="profile_photo"><img src="{{ $user->photo_url }}"></div>
        <div id="profile_bio">
          <h2>{{ $user->display_name ?: $user->username }}</h2>
          @if($user->location)
            <div><i class="marker icon"></i> {{ $user->location }}</div>
          @endif
          @if($user->timezone)
            <div><i class="clock icon"></i> {{ $user->timezone }}</div>
          @endif
          <br>

          <ul class="user_groups">
          @foreach($my_groups as $g)
            <li><a href="/group/{{ $g->id }}">#{{ $g->shortname }}</a></li>
          @endforeach
          </ul>


        </div>
      </div>
      <div id="profile_feed">

      </div>
    </div>

  </div>
</div>
@endsection
