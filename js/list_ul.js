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
    window.location.href = url;
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
    alert('hi');
  });
}

$(function () {
  $('body, #content').click(deselLine);
  $('.selectable').click(selLine);
  $('.selectable').dblclick(function () {
    $(this).trigger('click');
    $('.menu_list .edit a').trigger('click');
  });
  
  deselLine();
});