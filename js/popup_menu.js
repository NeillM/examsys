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
      document.getElementById('menudiv').style.display = 'none';
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
  var scrOfX = $('body,html').scrollLeft();
  var scrOfY = $('body,html').scrollTop();
  
  document.getElementById('menudiv').style.display = 'block';
  for (i=1; i<=option_no; i++) {
    document.getElementById('item'+i+'b').style.backgroundColor = '#FFFFFF';
  }
  
  top_pos = currentY + scrOfY;
  div_height = document.getElementById('menudiv').clientHeight + 4;
  if (top_pos > ($(window).height() + scrOfY - div_height)) {
    top_pos = $(window).height() + scrOfY - div_height;
  }
  document.getElementById('menudiv').style.left = e.clientX + scrOfX + 'px';
  document.getElementById('menudiv').style.top = top_pos + 'px';
  
  isMenu = true;
  return false;
}

function menuRowOn(rowID) {
  // Left menu column
  document.getElementById('item'+rowID+'a').style.backgroundColor = '#FFE7A2';
  document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #FFBD69';
  document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #FFBD69';
  document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #FFBD69';
  
  // Right menu column
  document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFE7A2';
  document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFBD69';
  document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFBD69';
  document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFBD69';
  document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFE7A2';
}

function menuRowOff(rowID) {
  // Left menu column
  document.getElementById('item'+rowID+'a').style.backgroundColor = '#F1F5FB';
  document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #F1F5FB';
  document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #F1F5FB';
  document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #F1F5FB';
  
  // Right menu column
  document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFFFFF';
  document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFFFFF';
  document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFFFFF';
  document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFFFFF';
  document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFFFFF';
}    

