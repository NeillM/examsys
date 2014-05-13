$(function () {
  $.ajaxSetup({ timeout: 3000 });
  $('#content').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
   doError();
  });

  $('#save_message').hide();

  $('.tbmark').click(updateMark);
});

var id, action;

function updateMark(e) {
  e.preventDefault();

  id = $(this).data('id');
  action = $(this).attr('id');

  var group = $(this).closest('.student-answer-block');
  var reminders = new Array();

  group.find('.reminder:checked').each(function() {
    reminders.push($(this).val());
  });
  reminders = reminders.join('|')

  var mark = $('#mark' + id).val();
  var comment = $('#comment' + id).val();

  $.post('../ajax/reports/save_textbox_marks.php',
    {
      paper_id: $('#paper_id').val(),
      q_id: $('#q_id').val(),
      log_id: $('#logrec' + id).val(),
      marker_id: $('#marker_id').val(),
      mark: mark,
      phase: $('#phase').val(),
      log: $('#log' + id).val(),
      user_id: $('#username' + id).val(),
      comments: comment,
      reminders: reminders
    },
    doSuccess
  ).fail(doError);
}

function doSuccess(data) {
  if (data != 'OK') {
    alert(langStrings['saveerror']);
    return false;
  } else {
    $('#save_message').show().delay( 800 ).slideUp('slow'); 
  }

  if ($('#mark' + id).val() == 'NULL') {
    $('#ans_' + id).closest('.student-answer-block').removeClass('marked');
  } else {
    $('#ans_' + id).closest('.student-answer-block').addClass('marked');
  }

  if (action.indexOf('next') > -1) {
    $('#ans_' + id).closest('.student-answer-block').hide();
    $('#ans_' + (++id)).closest('.student-answer-block').show();
  } else if (action.indexOf('prev') > -1) {
    $('#ans_' + id).closest('.student-answer-block').hide();
    $('#ans_' + (--id)).closest('.student-answer-block').show();
  }
}

function doError() {
  alert(langStrings['saveerror']);
}
