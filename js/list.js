$(function () {
  $('body').click(deselLine);
});
    
function selLine(lineID, evt) {
  $('.highlight').removeClass('highlight');

  $('#menu1a').hide();
  $('#menu1b').show();
  $('#lineID').val(lineID);
     
  $('#' + lineID).addClass('highlight');
  evt.cancelBubble = true;
}

function deselLine() {
  $('.highlight').removeClass('highlight');
  
  $('#lineID').val();
  $('#menu1b').hide();
  $('#menu1a').show();
}
