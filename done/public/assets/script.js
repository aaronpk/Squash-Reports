function open_nav() {
  $("body").toggleClass("nav_open");
}

$(function(){
  $(".like-entry").click(function(){
    $.post('/action/like-entry', {
      entry_id: $(this).data('entry-id'),
      _token: $("#csrf-token").val()
    }, function(response) {
      if(response.state == 'active') {
        $("*[data-entry-id="+response.entry_id+"]").addClass('active');
      } else {
        $("*[data-entry-id="+response.entry_id+"]").removeClass('active');
      }
      $("*[data-entry-id="+response.entry_id+"] .num").text(response.likes > 0 ? response.likes : '');
    });
    return false;
  });

  $(".subscribe-button").click(function(){
    $.post('/action/subscribe', {
      group_id: $(this).data('group-id'),
      _token: $("#csrf-token").val()
    }, function(response) {
      if(response.state == 'subscribed') {
        $("*[data-group-id="+response.group_id+"]").addClass('subscribed').removeClass('not-subscribed');
      } else {
        $("*[data-group-id="+response.group_id+"]").removeClass('subscribed').addClass('not-subscribed');
      }
      $("#num-subscribers").text(response.num_subscribers);
    });
  });
});
