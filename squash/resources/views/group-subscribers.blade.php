@extends('layouts.master')

@section('content')
<div id="profile">
  <div id="cover_photo" {!! $group->cover_photo ? 'style="background-image:url('.$group->cover_photo.')"' : 'class="none"' !!}>
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

        </div>
      </div>
      <div id="group_entries">

        <div class="subscribers">
          <span><i class="users icon"></i> <span id="num-subscribers">{{ count($subscribers) }}</span> Subscriber{{ count($subscribers) == 1 ? '' : 's' }}</span>
          <table class="ui compact single line table">
            <thead>
              <tr>
                <td></td>
                <td>Name</td>
                <td>Nickname</td>
                <td>Email</td>
                <td></td>
              </tr>
            </thead>
            <tbody>
              @foreach($subscribers as $u)
                <tr class="user-{{ $u->user_id }}">
                  <td><a href="/{{ $org->shortname }}/{{ $u->username }}"><img src="{{ $u->photo_url }}" class="profile-photo" style="width: 32px; height: 32px;"></a></td>
                  <td>{{ $u->display_name }}</td>
                  <td><a href="/{{ $org->shortname }}/{{ $u->username }}">{{ $u->username }}</a></td>
                  <td>{{ $u->email }}</td>
                  <td><button class="ui compact icon button unsubscribe-user" data-user-id="{{ $u->user_id }}" data-group-id="{{ $group->id }}"><i class="x icon"></i></button></td>
                </tr>
              @endforeach
            </tbody>
          </table>
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
      $(".user-"+response.user_id).remove();
    });
  });
});
</script>
@endsection
