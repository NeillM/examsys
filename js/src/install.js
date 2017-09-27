$(function() {
  $(document).tooltip();
});

$(function () {
  $("#installForm").validate();
  $('#useLdap').change(function() {
    $('#ldapOptions').toggle();
  });
  $('#uselookupLdap').change(function() {
    $('#ldaplookupOptions').toggle();
  });
});