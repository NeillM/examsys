$(function () {
 $.ajaxSetup({ timeout: 3000 });
 $('#content').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
   doError();
 });

  $('.tbmark').click(updateMark);
});

var id;

function updateMark(e) {
  e.preventDefault();

  id = $(this).data('id');

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
  }

  if ($('#mark' + id).val() == 'NULL') {
    $('#ans_' + id).removeClass('marked').effect("highlight", {}, 1500);
  } else {
    $('#ans_' + id).addClass('marked').effect("highlight", {}, 1500);
  }

}

function doError() {
  alert(langStrings['saveerror']);
}
