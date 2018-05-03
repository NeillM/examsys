$(function() {
  $('#edit_form').submit(function () { 
    tinyMCE.triggerSave();
  })
  $('#edit_form').validate({
    ignore: '',
    rules: {
      leadin: 'required'
    },
    messages: {
      leadin: lang_string['enterleadin']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'leadin') {
        error.insertAfter('#leadin_parent');
        tinyMCE.getInstanceById('leadin').getWin().document.body.style.backgroundColor='#ffd6d6';
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang_string['validationerror']);
    }
  });
});