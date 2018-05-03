$(function () {
  if ($('#option_text1').size() == 0) {
    $('#addbank').attr('disabled', 'disabled');
    $('#addpaper').attr('disabled', 'disabled');
  }

  $('#edit_form').validate({
    rules: {
      leadin: 'required',
      option_text1: 'required'
    },
    messages: {
      leadin: '<br />'+lang_string['enterleadin'],
      option_text1: '<br />'+lang_string['enteroption_kw']
    }
  });
});
