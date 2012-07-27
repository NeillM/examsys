function sendTextToAS3(mode, image, correct, user){
  var toSend = mode + ';' + image + ';';
  if (typeof 'correct' != 'undefined' && correct != '' && correct != undefined) {
    toSend += correct + ';';
  }
  if (typeof 'user' != 'undefined' && user != '' && user != undefined) {
    toSend += user + ';';
  }

  // Add small delay to get around race condition that was evident in Firefox
  setTimeout(function() {
    doSend(toSend);
  }, 150)
}

function doSend(toSend) {
  try
  {
    var flash1 = document.getElementById("externalinterface1");
    if (flash1) {
      flash1.sendTextFromJS(toSend);
    }
  }
  catch(error)
  {
    var flash2 = document.getElementById("externalinterface2");
    if (flash2) {
      flash2.sendTextFromJS(toSend);
    }
  }
}

// Requires flashTarget to be defined with ID of DOM element to receive text
function receiveTextFromAS3(txt) {
  $('#' + flashTarget).val(txt);
}
