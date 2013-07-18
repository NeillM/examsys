var selLine = function (e) {
  e.stopPropagation();
  
  $('.highlight').removeClass('highlight');

  var id = $(this).data('id');

  $('.reactive').addClass('menuitem')
  .removeClass('greymenuitem');

  $('.reactive').children('a')
  .unbind("click")
  .click(function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    url += '?id=' + id;
    if ($(this).hasClass('launchwin')) {
      launchWindow(url);
    } else {
      window.location.href = url;
    }
  });

  $(this).addClass('highlight');
}

var deselLine = function () {
  $('.highlight').removeClass('highlight');
  
  $('.reactive').removeClass('menuitem')
  .addClass('greymenuitem');

  $('.reactive').children('a')
  .unbind("click")
  .click(function (e) { 
    e.preventDefault(); 
  });
}

var launchWindow = function (url) {
  notice=window.open(url, "deleteitem", "width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  notice.moveTo(screen.width/2-210,screen.height/2-85);
  if (window.focus) {
    notice.focus();
  }
}

$(function () {
  $('body, #content').click(deselLine);
  $('.selectable').click(selLine);
  $('.selectable').dblclick(function () {
    $(this).trigger('click');
    $('.menu_list .default a').trigger('click');
  });
  
  deselLine();
});