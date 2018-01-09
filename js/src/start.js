// Dialog box
function info_dialog(msg) {
    $("#info_overlay").show();
    $("#info_submit_dialog_msg").html(msg);
    $("#info_submit_dialog").css('left', (($(window).width() / 2) - 250) + 'px');
    $("#info_submit_dialog").css('top', (($(window).height() / 2) - 100) + 'px');
}

function UpdateClock( hours, minutes, seconds) {
  KillClock();
  
  if ( hours == 0 ){
    hours   = '';
    minutes = ( ( minutes  < 10 ) ? "0" : "" ) + minutes;
  } else {
    hours   = ( ( hours < 10 ) ? "0" : "" ) + hours;
    minutes = ( ( minutes  < 10 ) ? ":0" : ":" ) + minutes;
  }
  seconds = ( ( seconds < 10 ) ? ":0" : ":" ) + seconds;

  $('#theTime').html("" + hours + minutes + seconds);
}


//BP Performs countdown. Saves if counter has reached 0
function UpdateTimerWithRemainingTime(remaining_time, close) {
  
  minutes = Math.floor( remaining_time / 60 );
  minutes = Math.round( minutes );
  seconds = remaining_time % 60;
  
  UpdateClock( 0, minutes, seconds);
  
  if (remaining_time == 0 && close == true) {
    KillClock();
    forceSave();
    return;
  }
  if( remaining_time > 0 ){
    remaining_time = remaining_time -1;
  }
  clockID = setTimeout( "UpdateTimerWithRemainingTime( " + remaining_time + ", " + close + " )", 1000 );
}

function UpdateClockWithCurrentTime() {

  var tDate   = new Date();
  
  var hours   = tDate.getHours();
  var minutes = tDate.getMinutes();
  var seconds = tDate.getSeconds();
  
  UpdateClock(hours, minutes, seconds);
  
  clockID = setTimeout("UpdateClockWithCurrentTime()", 1000);
}

function StartTimer(remaining_time, close) {
  clockID = setTimeout("UpdateTimerWithRemainingTime(" + remaining_time + ", " + close + " )", 500);
}

function StartClock() {
  clockID = setTimeout("UpdateClockWithCurrentTime()", 500);
}

function KillClock() {
  if (clockID) {
    clearTimeout(clockID);
    clockID  = 0;
  }
}

function MRQ(questionid, part_id, options_total, selectable) {
	var abstainExist = document.getElementById("q" + questionid + "_abstain");
	if (abstainExist != null) {
		$("#q" + questionid + "_abstain").prop("checked", false);
	}
	
  checked_total = 0;
  for (i=1; i<=options_total; i++) {
    currentid = "q" + questionid + "_" + i;
    if ($('#' + currentid).prop("checked")) {
      checked_total++;
    }
  }
  if (checked_total > selectable) {
		alert(lang['msgselectable1'] + ' ' + selectable + ' ' + lang['msgselectable2']);
		$("#q" + questionid + "_" + part_id).prop("checked", false);
  }
}

function MRQabstain(questionid, options_total) {
  for (i=1; i<=options_total; i++) {
		$("#q" + questionid + "_" + i).prop("checked", false);
  }
}

function rankCheck() {
  var sel = $(this).val();    
  var classlist =  '.' + $(this).attr('class').replace(' ', '.');
  var count = 0;
  var loopSel = '';
  
  $(classlist).each(function () {
    loopSel = $(this).val();
    if(loopSel != '0' && loopSel != 'u' && loopSel == sel) count++;
  });
  if (count > 1) {
    info_dialog(lang['msgselectable3'] + ' ' + sel  + lang['msgselectable4']);
    $(this).val('u');
  }
}

function multimatchingCheck(questionid, options_total, selectable) {
  checked_total = 0;
  for (i=0; i<options_total; i++) {
    if (document.getElementById(questionid).options[i].selected == 1) {
      checked_total++;
    }
  }
  tmp_count = 0;
  if (checked_total > selectable) {
    alert(lang['msgselectable1'] + ' ' + selectable + ' ' + lang['msgselectable2']);
	
    for (i=0; i<options_total; i++) {
      if (document.getElementById(questionid).options[i].selected == 1) {
        tmp_count++;
      }
      if (tmp_count > selectable) {
        document.getElementById(questionid).options[i].selected = 0;
      }
    }
  }
}

$(document).ready(function(){
  var el = document.getElementById('paper');

  $('#previous').click(function() {
    $('#button_pressed').val('previous');
  });
  
  $('#finish').click(function() {
    $('#button_pressed').val('finish');
  });
  
  $('.act').click(function() {
    onoff($(this).attr('id'));
  });

  $('.inact').click(function() {
    onoff($(this).attr('id'));
  });

  $('#jumpscreen').change(function (event) {
    $('#button_pressed').val('jumpscreen');
    $('#qForm').attr('action',"start.php?id=" + el.dataset.pid + "&dont_record=true");
    submitType = 'userSubmit';
    return checkSubmit(event);
  });

  //Stop forms being submitted with ENTER
  $('input[type=text]').keydown(function (event) {
    event = event || window.event;
    if (event.keyCode === 13) {
      event.preventDefault();
      return false;
    } else {
      return true;
    }
  });

  $("#info_dialog_ok").click(function(event) {
    $("#info_overlay").hide();
  });

  last_saved_user_answers = $('#qForm').serialize();
  $('#next').click(checkSubmit);

  $('#previous').click(checkSubmit);
  $('#finish').click(checkSubmit);

  // Attach UI events
  $('.rankselect').change(rankCheck);
  $(".calc-answer").keydown(filterKeypress);

  autoSaveRef = '';
  last_save_point = (new Date).getTime();
  last_saved_user_answers = null; // Holds the data of the last successful auto save
  submitted = false;

  // Setup autosave
  startAutoSave();

  $('#fire_exit').click(function() {
    $('#button_pressed').val('fire_exit');
    $('#qForm').attr('action',"fire_evacuation.php?id=" + el.dataset.pid + "&dont_record=true");
    ajaxSave(1, 'userSubmit');
  });
});

function onoff(objID) {
  var parts = objID.split("_");
  var questionID = parts[0];
  var itemID = parts[1];

  if ($('#' + objID).hasClass("act")) {
    $('#' + objID).addClass("inact")
    $('#' + objID).removeClass("act")
    setting = '1';
  } else {
    $('#' + objID).addClass("act")
    $('#' + objID).removeClass("inact")
    setting = '0';
  }
  objID = 'dismiss' + questionID;
  current_value = $('#' + objID).val();
  new_value = current_value.slice(0,itemID-1) + setting + current_value.slice(itemID,current_value.length);
  $('#' + objID).val(new_value);      
}

function write_string(p_string) {
  document.write(p_string);
}

function filterKeypress(event) {
  // There is no situation where a shifted key is valid
  if (event.shiftKey === true || event.altKey === true) {
    event.preventDefault();
    return false;
  }

  // Allow only one .
  if ((event.keyCode == 190    // .
      || event.keyCode == 110)) // . (keypad)
  {
    if ($(event.target).val().indexOf('.') !== -1) {
      event.preventDefault();
    }
    return;
  }
  // Allow - only at start of answer
  if (event.keyCode == 173    // -
      || event.keyCode == 189 // - (IE)
      || event.keyCode == 109) // - (keypad)
  {
    if ($(event.target).val().indexOf('-') !== -1) {
      event.preventDefault();
    }
    return;
  }
  // Allow: backspace, delete, tab and escape
  if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 ||
  // Allow: Ctrl+A
  (event.keyCode == 65 && event.ctrlKey === true) ||
  // Allow: home, end, left, right
  (event.keyCode >= 35 && event.keyCode <= 39)) {
    // let it happen, don't do anything
    return;
  } else {
    // Ensure that it is a number and stop the keypress
    if (((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 ))) {
      event.preventDefault();
      return false;
    }
  }
}
// Wrapper for window.close, if not a popup we move back a page.
function close_window () {
  if (window.opener == null) {
    parent.history.back();
  } else {
    window.close();
  }
}

function changeRef (refID) {
  $('#refpane').val(refID);
  var el = document.getElementById('paper');
  refcount = el.dataset.refcount;
  winH = $(window).height();
  resizeReference();
  var flag = 0;
  for (i=0; i< refcount; i++) {
    if (i === refID) {
      $('#framecontent' + i).show();
      $('#refhead' + i).css('top', (31 * i) + 'px');
      flag = 1;
    } else {
      $('#framecontent' + i).hide();
      if (flag === 0) {
        $('#refhead' + i).css('top', (31 * i) + 'px');
      } else {
        $('#refhead' + i).css('top', '');
        $('#refhead' + i).css('bottom', ((refcount - (i + 1)) * 31) + 'px');
      }
    }
  }
}

function resizeReference () {
  winH = $(window).height();
  var el = document.getElementById('paper');
  refcount = el.dataset.refcount;
  if (refcount > 0) {
    $subtract = (31 * refcount) + 11;
    for (i=0; i< refcount; i++) {
      $('#framecontent' + i).css('height', (winH - $subtract) + 'px');
    }
    var mainWidth = $('body').outerWidth() - $('#framecontent0').outerWidth(true);
    $('#maincontent').width(mainWidth);
  }
}

function confirmSubmit (event) {
    var el = document.getElementById('paper');
    if (el.dataset.submittype === 'preview') {
      confirmSubmitPreview(event);
    } else if (el.dataset.submittype === 'linear') {
      confirmSubmitLinear(event);
    } else {
      confirmSubmitBiDirectional(event);
    }
}

function confirmSubmitPreview (event) {
  conductSave(event);
}

function confirmSubmitLinear (event) {
  if ($('#button_pressed').val() === 'finish') {
    showDialog(lang['javacheck2']);
  } else {
    var msg = lang['javacheck1'];
    if ($('.ecalc-answer').length > 0) {
      var ecalcQuestions = [];
      $('.ecalc-answer').each(function(){
        ecalcQuestions[ecalcQuestions.length] = this.id.substring(1);
      });
      msg = lang['javacheck3'].replace('[X]', ecalcQuestions.join());
    }
    showDialog(msg);
  }

  $("#dialog_ok").click(function(event) {
    $('body').css('cursor','wait');
    submitted = true;
    $("#overlay").hide();
    conductSave(event);
  });
}

function confirmSubmitBiDirectional (event) {
  if (submitted === true) {
    return false;
  }
  if ($('#button_pressed').val() === 'finish') {
    showDialog(lang['javacheck2']);
    $("#dialog_ok").click(function(event) {
      $('body').css('cursor','wait');
      submitted = true;
      $("#overlay").hide();
      conductSave(event);
    });
  } else if ($('#isEnhancedCalc').val() === '1' && $('#missingCalcAnswer').val() != '1') {
    var ecalcQuestions = [];
    $('.ecalc-answer').each(function(){
      if ($(this).val() === '') {
        ecalcQuestions[ecalcQuestions.length] = $(this).attr('data-screen');
      }
    });

    if (ecalcQuestions.length > 0) {
      var msg = lang['answerrequired'] + '<br/><br/><strong>' + lang['answerrequired_confirm'] + '</strong>'.replace('[X]', ecalcQuestions.join());
      showEnhancedcalcWarning(msg);
      $("#enhancedcalc_warning_ok").click(function(event) {
        submitted = true;
        $('body').css('cursor','wait');
        $("#overlay").hide();
        conductSave(event);
      });
    } else {
      conductSave(event);
    }
  } else {
    conductSave(event);
  }
}

// Normal user submit by clicking on next, previous, finish or jump screen
function checkSubmit (event) {
  stopAutoSave();
  if (typeof(tinyMCE) !== "undefined") {
    tinyMCE.triggerSave();
  }

  submitType = 'userSubmit';
  if (event === null) {
    $('#button_pressed').attr('value',event.target.id);
  }

  $("#dialog_cancel, #enhancedcalc_warning_cancel").click(function(event) {
    if ($('#button_pressed').val() === 'jumpscreen') {
      $('#jumpscreen option').each(function () {
        if (this.defaultSelected) {
          this.selected = true;
          return false;
        }
      });
    }
    $('#savemsg').html("");
    $('body').css('cursor','default');
    $("#overlay").hide();
  });
  confirmSubmit(event);
}

function conductSave(event) {
  var el = document.getElementById('paper');
  if (typeof(tinyMCE) !== "undefined") {
    tinyMCE.triggerSave();
  }
  stopAutoSave();
  $('#saveError').fadeOut('slow');
  $('#savemsg').html("<img src=\"../artwork/busy.gif\" class=\"busyicon\" />");
  // Log which method the users submitted the page via
  if ($('#button_pressed').val() === 'finish') {
    $('#qForm').attr('action',"finish.php?id=" + el.dataset.pid + el.dataset.urlmod + "&dont_record=true");
  } else {
    $('#qForm').attr('action',"start.php?id=" + el.dataset.pid + el.dataset.urlmod + "&dont_record=true");
  }
  ajaxSave(1, 'userSubmit');
}

function showDialog(msg) {
  $("#dialog_cancel").focus();
  $("#submit_dialog_msg").html(msg);
  $("#submit_dialog").css('left', (($(window).width() / 2) - 250) + 'px');
  $("#submit_dialog").css('top', (($(window).height() / 2) - 100) + 'px');
  $(".dialogs").hide();
  $("#submit_dialog").show();
  $("#overlay").show();
}

function showEnhancedcalcWarning(msg) {
  $("#enhancedcalc_warning_cancel").focus();
  $("#enhancedcalc_warning_msg").html(msg);
  $("#enhancedcalc_warning").css('left', (($(window).width() / 2) - 250) + 'px');
  $("#enhancedcalc_warning").css('top', (($(window).height() / 2) - 200) + 'px');
  $(".dialogs").hide();
  $("#enhancedcalc_warning").show();
  $("#overlay").show();
}

// Called when a user has run out of time by UpdateTimerWithRemainingTime in start.js
function forceSave () {
  var el = document.getElementById('paper');
  stopAutoSave();
  ajaxSave(1, 'forcedSubmit');
  info_dialog(lang['forcesave']);
  $('#qForm').attr('action',"finish.php?id=" + el.dataset.pid + el.dataset.urlmod + "&dont_record=true");
  $('#qForm').submit();
}

// Called on auto save time out
function autoSave () {

  // This could take longer than the autosave timeout stop auto save to stop duplicate events.
  stopAutoSave();

   // Save any data from wysiwyg
  if (typeof(tinyMCE) != "undefined") {
    tinyMCE.triggerSave();
  }
  var formData = $('#qForm').serialize();

  // Only auto save if the data has changed, OR 20 minutes has elapsed - stop sessions expiring. ?>
  var now_milliseconds = (new Date).getTime();
  var save_diff = now_milliseconds - last_save_point;
  if (last_saved_user_answers !== formData) {
    $('#savemsg').html("<img src=\"../artwork/busy.gif\" class=\"busyicon\" />");
    ajaxSave(1, 'autoSave');
    last_save_point = (new Date).getTime();
  } else if (save_diff > (1000 * 1200)) {
    ajaxSave(0, 'autoSave');
    last_save_point = (new Date).getTime();
  } else {
    // Re-register the autosave timer
    startAutoSave();
  }
}

function startAutoSave () {
  var el = document.getElementById('paper');
  clearTimeout(autoSaveRef);// Cancel any outstanding timeouts to make sure only one auto save is ever registered.
  autoSaveRef = setTimeout("autoSave()", el.dataset.savefreq);
}

function stopAutoSave () {
  clearTimeout(autoSaveRef);
}

function ajaxSave (ans_changed, submitType) {
  var el = document.getElementById('paper');
  // Hide any errors
  $('#saveError').fadeOut('fast');
  // Random page ID to stop IE caching results.
  date = new Date();
  randomPageID = date.getTime();
  $('#randomPageID').val(randomPageID);
  if (typeof(tinyMCE) !== "undefined"){
    tinyMCE.triggerSave();
  }
  $.ajax({
    url: 'save_screen.php?id=' + el.dataset.pid + el.dataset.urlmod + '&ans_changed=' + ans_changed + '&submitType=' + submitType + '&rnd=' + randomPageID + el.dataset.urlmod,
    type: 'post',
    data: $('#qForm').serialize(),
    dataType: 'html',
    timeout: el.dataset.savetimeout,
    cache: false,
    tryCount : 0,
    retryLimit : el.dataset.saveretry,
    beforeSend: function() {},
    fail: function() {
      if (this.retry()) {
        return;
      } else  {
        saveFail('fail', this.url, '');
        return;
      }
    },
    error: function(xhr, textStatus, errorThrown) {
      if (textStatus === 'error') {
        if (this.retry()) {
          return;
        } else {
          // Status is the response code and errorThrown is the HTTP response text.
          if (errorThrown !== '') {
              saveFail(textStatus + ': ' + xhr.status, this.url, errorThrown);
              return;
          }
        }
      }
      // Just use the xhr status.
      saveFail(textStatus + ': ' + xhr.status, this.url, '');
      return;
    },
    success: function (ret_data, textStatus, jqXHR) {
      if (ret_data == randomPageID) {
        $('#save_failed').val('');
        //Cache the form data to look for changes on next auto save
        last_saved_user_answers = this.data;
        saveSuccess();
        return;
      }
      if (this.retry()) {
        return;
      } else {
        // marking_funcions.inc record_marks failed after retry.
        // red_data can be ERROR, a random generated number or a html page.
        if (ret_data === 'ERROR' || (!isNaN(parseFloat(ret_data)) && isFinite(ret_data))) {
          // record the returned random number or ERROR.
          saveFail('record_marks', this.url, ret_data);
        } else {
            // Strip out the title of the html as thats is all we are interested in.
            htmlstart = ret_data.indexOf("<title>") + 7;
            htmlend = ret_data.indexOf("</title>");
            htmltitle = ret_data.substring(htmlstart, htmlend);
            if (htmltitle === '') {
                htmltitle = 'html response';
            }
            saveFail('record_marks', this.url, htmltitle);
        }
        return;
      }
    },
    retry: function (){
      // Retry if we can
      this.tryCount++;
      if (this.tryCount <= this.retryLimit) {
        // Indicate the retry on the url
        if (this.tryCount === 1) {
          this.url = this.url + "&retry=" + this.tryCount;
        } else {
          this.url = this.url.replace("&retry=" + (this.tryCount - 1), "&retry=" + this.tryCount);
        }
        $.ajax(this);
        return true;
      }
      return false;
    }
  });
  return;
}

function saveSuccess () {
  // Re-register the autosave timer ?>
  startAutoSave();
  if (submitType === 'userSubmit') {
    $('#qForm').submit();
    return true;
  } else if(submitType === 'forcedSubmit') {
    $('#qForm').submit();
  } else {
    // Clear auto save message ?>
    $('#savemsg').html("");
  }
}

function saveFail (textStatus, url, ret_data) {
  // Re-register the autosave timer
  startAutoSave();

  current_val =  $('#save_failed').val();
  unix_now = Math.round($.now() / 1000);
  if (current_val === '') {
    $('#save_failed').val(unix_now  + '-' + textStatus + '-' + url + '-' + ret_data);
  } else {
    $('#save_failed').val(current_val + '\n' + unix_now + '-' + textStatus + '-' + url + '-' + ret_data);
  }
  $('#savemsg').html("");
  // usersubmit always warns, auto save only on application error.
  if (submitType !== 'autoSave' || textStatus === 'record_marks') {
    $('#saveError').fadeIn('fast');
  }
  $('body').css('cursor','default');
  submitted = false;

  return false;
}