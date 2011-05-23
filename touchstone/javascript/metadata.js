function toggle(objectID) {
  if (document.getElementById(objectID).style.backgroundColor == 'white') {
    document.getElementById(objectID).style.backgroundColor = '#B3C8E8';
  } else {
    document.getElementById(objectID).style.backgroundColor = 'white';
  }
}

function updateList() {
  var keywordno = document.getElementById('keywordno').value;
  var newList = '';
  for (i=0; i<keywordno; i++) {
    if (document.getElementById('keyword' + i).checked == true) {
      newList = newList + ';1' + document.getElementById('keyword' + i).value;
    } else {
      newList = newList + ';0' + document.getElementById('keyword' + i).value;
    }
  }
  document.getElementById('thelist').value = newList;
}

function newKeyword() {
  keywordwin=window.open("/touchstone/question/new_keyword.php","keywords","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  if (window.focus) {
    keywordwin.focus();
  }
}

function deleteKeyword() {
  keywordwin=window.open("/touchstone/question/delete_rename_keyword.php","keywords","width=500,height=400,left="+(screen.width/2-250)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  if (window.focus) {
    keywordwin.focus();
  }
}

function deleteMedia(media_id) {
  document.getElementById(media_id).style.display = 'none';
  document.getElementById('delete_'+media_id).value = '1';
}
