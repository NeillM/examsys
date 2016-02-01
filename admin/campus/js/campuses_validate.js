$(function () {
    $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
            return true;
        }
    });
    $('form').removeAttr('novalidate');
    $('#cancel').click(function() {
        history.back();
    });
});