var ie  = document.all
var ns6 = document.getElementById&&!document.all
var isMenu  = false ;
var menuSelObj = null ;
var overpopupmenu = false;

function mouseSelect(e) {
  var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
  if (isMenu) {
    if (overpopupmenu == false) {
      isMenu = false ;
      overpopupmenu = false;
      $('#menudiv').hide();
      return true ;
    }
    return true ;
  }
  return false;
}

// POP UP MENU
function popMenu(option_no, e) {
  if (!e) var e = window.event;
  var currentX = e.clientX;
  var currentY = e.clientY;
  var scrOfX = $(document).scrollLeft();
  var scrOfY = $(document).scrollTop();
  
  $('#menudiv').show();
  for (i=1; i<=option_no; i++) {
    $('#item'+i+'b').css('background-color', '#FFFFFF');
  }
  
  top_pos = currentY + scrOfY;
  div_height = $('#menudiv').height() + 6;
  if (top_pos > ($(window).height() + scrOfY - div_height)) {
    top_pos = $(window).height() + scrOfY - div_height;
  }
  $('#menudiv').css('left', e.clientX + scrOfX);
  $('#menudiv').css('top', top_pos);
  
  isMenu = true;
  return false;
}

function menuRowOn(rowID) {
  // Left menu column
  $('#item'+rowID+'a').css('background-color', '#FFE7A2');
  $('#item'+rowID+'a').css('border-top', '1px solid #FFBD69');
  $('#item'+rowID+'a').css('border-bottom', '1px solid #FFBD69');
  $('#item'+rowID+'a').css('border-left', '1px solid #FFBD69');
  
  // Right menu column
  $('#item'+rowID+'b').css('background-color', '#FFE7A2');
  $('#item'+rowID+'b').css('border-top', '1px solid #FFBD69');
  $('#item'+rowID+'b').css('border-bottom', '1px solid #FFBD69');
  $('#item'+rowID+'b').css('border-left', '1px solid #FFE7A2');
  $('#item'+rowID+'b').css('border-right', '1px solid #FFBD69');
}

function menuRowOff(rowID) {
  // Left menu column
  $('#item'+rowID+'a').css('background-color', '#F1F5FB');
  $('#item'+rowID+'a').css('border-top', '1px solid #F1F5FB');
  $('#item'+rowID+'a').css('border-bottom', '1px solid #F1F5FB');
  $('#item'+rowID+'a').css('border-left', '1px solid #F1F5FB');
  
  // Right menu column
  $('#item'+rowID+'b').css('background-color', '#FFFFFF');
  $('#item'+rowID+'b').css('border-top', '1px solid #FFFFFF');
  $('#item'+rowID+'b').css('border-bottom', '1px solid #FFFFFF');
  $('#item'+rowID+'b').css('border-left', '1px solid #FFFFFF');
  $('#item'+rowID+'b').css('border-right', '1px solid #FFFFFF');
}    

