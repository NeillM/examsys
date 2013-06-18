$(function () {
 $.ajaxSetup({ timeout: 3000 });
 $('#content').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
   doError();
 });

  $('.tbmark').change(updateMark);
});

var id;

function updateMark() {
  var mark = $(this).val();
  var name = $(this).attr('name');
  id = name.replace('mark', '');

  $.post('../ajax/reports/save_textbox_marks.php',
    {
      paper_id: $('#paper_id').val(),
      q_id: $('#q_id').val(),
      log_id: $('#logrec' + id).val(),
      marker_id: $('#marker_id').val(),
      mark: mark,
      phase: $('#phase').val(),
      log: $('#log' + id).val(),
      user_id: $('#username' + id).val()
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
    $('#ans_' + id).removeClass('marked').effect("highlight", {}, 1500);;
  } else {
    $('#ans_' + id).addClass('marked').effect("highlight", {}, 1500);;
  }

}

function doError() {
  alert('Error not implemented');
}
