@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo" {!! $user->cover_photo ? 'style="background-image:url('.$user->cover_photo.')"' : 'class="none"' !!}>
  </div>
  <div id="profile_contents">
    <div id="profile_text">
      <div id="profile_info">
        @if($user->photo_url)
          <div id="profile_photo"><img src="{{ $user->photo_url }}"></div>
        @endif
        <div id="profile_bio">
          <h2>{{ $user->display_name ?: '@'.$user->username }}</h2>
          @if($user->display_name)
            <h3>{{ '@'.$user->username }}</h3>
          @endif
          @if($user->location)
            <div><i class="marker icon"></i> {{ $user->location }}</div>
          @endif

          <div class="timezone-info">
            <div><i class="clock icon"></i> <span class="timezone-name">{{ $user->timezone }}</span></div>
          </div>

        </div>
      </div>
      <div id="profile_feed">

        <div class="subscriptions">
          <span><i class="group icon"></i> <span id="num-subscriptions">{{ count($subscriptions) }}</span> Subscription{{ count($subscriptions) == 1 ? '' : 's' }}</span>
          @if(count($subscriptions))
          <table class="ui compact single line table">
            <thead>
              <tr>
                <td>Group</td>
                <td></td>
              </tr>
            </thead>
            <tbody>
              @foreach($subscriptions as $g)
                <tr class="group-{{ $g->group_id }}">
                  <td><a href="/{{ $org->shortname }}/group/{{ $g->shortname }}">#{{ $g->shortname }}</a></td>
                  <td><button class="ui compact icon button unsubscribe-user" data-user-id="{{ $g->user_id }}" data-group-id="{{ $g->group_id }}"><i class="x icon"></i></button></td>
                </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>

      </div>
    </div>

  </div>
</div>

<script>
$(function(){
  $(".unsubscribe-user").click(function(){
    $.post("/action/admin/unsubscribe", {
      group_id: $(this).data('group-id'),
      user_id: $(this).data('user-id'),
      _token: $("#csrf-token").val()
    }, function(response){
      $(".group-"+response.group_id).remove();
    });
  });
});
</script>
@endsection
