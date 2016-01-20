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
                <li><a href="/{{ $org->shortname }}/{{ $u->username }}"><img src="{{ $u->photo_url }}" width="48"></a></li>
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
