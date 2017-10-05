$(function () {
    $('#cancel').click(function() {
        history.back();
    });
});
$(function () {
  $('#cfg_ims_enabled').change(function() {
    $('#display_ims').toggle();
  });
});