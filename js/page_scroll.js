
function scrollXY() {
  $('#scrOfY').val($('body,html').scrollTop());
}

$(document).ready(function(){

  $(window).scroll(function() {
    scrollXY();
  });

});    