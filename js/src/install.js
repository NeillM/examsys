$(function() {
  $(document).tooltip();
});

function go_config() {
  window.location='../admin/config.php';
}

$(function () {
  $("#installForm").validate();
  $('#useLdap').change(function() {
    $('#ldapOptions').toggle();
  });
  $('#uselookupLdap').change(function() {
    $('#ldaplookupOptions').toggle();
  });
});