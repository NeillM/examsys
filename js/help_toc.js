function updateMenu(sectionID, imageID) {
  $('#' + sectionID).toggle();

  icon = ($('#' + imageID).attr('src') == '../open_book.png') ? '../closed_book.png' : '../open_book.png';
  $('#' + imageID).attr('src', icon);
}
