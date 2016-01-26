@extends('layouts.master')


@section('content')

  <div style="height:80px;"></div>

  <div style="max-width: 800px; margin: 0 auto;">
    <h2>Choose a cover photo for {{ $choose_for }}</h2>

    <div class="ui stackable three column grid">
      @foreach($photos as $photo)
        <div class="column">
          <div class="ui special cards">
            <div class="card">
              <div class="blurring dimmable image">
                <div class="ui inverted dimmer">
                  <div class="content">
                    <div class="center">
                      <button class="ui inverted button select-button" data-photo="{{ $photo }}">Select</button>
                    </div>
                  </div>
                </div>
                <img src="/photos/thumbnails/{{ $photo }}">
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

<script>
$(function(){
  $('.special.cards .image').dimmer({
    on: 'hover'
  });

  $(".select-button").click(function(){
    $.post("/action/select-cover-photo", {
      photo: $(this).data('photo'),
      group_id: "{{ $group_id }}",
      _token: $("#csrf-token").val()
    }, function(response){
      window.location = response.redirect;
    });
  });
});
</script>
<style type="text/css">
.blurring.dimmable>.inverted.dimmer {
  background-color: rgba(255,255,255,.1);
}
</style>

@endsection
