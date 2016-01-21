function open_nav() {
  $("body").toggleClass("nav_open");
}

$(function(){
  $(".like-entry").click(function(){
    console.log($(this).data('entry-id'));
    $.post('/action/like-entry', {
      entry_id: $(this).data('entry-id'),
      _token: $("#csrf-token").val()
    }, function(response) {
      console.log(response);
      if(response.state == 'active') {
        $("*[data-entry-id="+response.entry_id+"]").addClass('active');
      } else {
        $("*[data-entry-id="+response.entry_id+"]").removeClass('active');
      }
      $("*[data-entry-id="+response.entry_id+"] .num").text(response.likes > 0 ? response.likes : '');
    });
    return false;
  });
});
