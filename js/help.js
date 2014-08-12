function goHome() {
  window.location="index.php?id=1";
}

function roll(img_name,img_src)  {
  document[img_name].src = img_src;
}

function newPage() {
  window.location = 'new_page.php';
}

function infoPage()  {
  window.location = 'stats.php';
}

function search() {
  window.location = 'search.php?searchstring=' + document.getElementById('searchbox').value;
}

function recycleBin() {
  window.location = 'recycle_bin.php';
}

function updateMenu(sectionID, imageID) {
  $('#' + sectionID).toggle();

  icon = ($('#' + imageID).attr('src') == '../open_book.png') ? '../closed_book.png' : '../open_book.png';
  $('#' + imageID).attr('src', icon);
}

$(function() {
  $('.gototop').click(function() {
    $("#contents").animate({ scrollTop: 0 }, "slow");


  });
});