document.write('<script type="text/javascript" src="../../javascript/validation/jquery.mcq.js"></script>');

$(function () {
  $('#edit_form').submit(function () {
    var checked = 0;
    $('.mrq-correct').each(function () {
      if ($(this).is(':checked')) {
        checked++;
      }
    });
    if (checked == 1) {
      if (confirm(lang['mrqconvert'])) {
        $('#mcqconvert').val('1');
      } else {
        return false;
      }
    }
    tinyMCE.triggerSave();
  });
});