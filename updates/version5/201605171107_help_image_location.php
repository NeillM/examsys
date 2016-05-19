<?php

if ($updater_utils->check_version("6.1.0") and !$updater_utils->has_updated('update_help_image_location')) {
  // Regular expression to match the old student help image directory location.
  // it will match directories ./media/ or /media/ and grab the filename.
  $regexp = '#src="images\/(.*?)"#';
  // The substitution will replace the old src tag with a new one that.
  $webroot = $configObject->get('cfg_root_path');
  // Ensure there is a trailing slash.
  if (substr($webroot, -1) !== '/') {
    $webroot .= '/';
  }
  $substitution = 'src="' . $webroot . 'getfile.php?type=help_student&amp;filename=$1"';
  // Get question leadins with images in them. We may need to update them.
  $result = $mysqli->prepare("SELECT id, body FROM student_help WHERE body LIKE '%<img%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $body);
  while ($result->fetch()) {
    $newbody = preg_replace($regexp, $substitution, $body);
    if ($newbody != $body) {
      // There was a change, so update the record.
      $update = $mysqli->prepare("UPDATE student_help SET body = ? WHERE id = ?");
      $update->bind_param('si', $newbody, $id);
      $update->execute();
    }
  }

  // Now update the staff images.
  $substitution = 'src="' . $webroot . 'getfile.php?type=help_staff&amp;filename=$1"';
  // Get question leadins with images in them. We may need to update them.
  $result = $mysqli->prepare("SELECT id, body FROM staff_help WHERE body LIKE '%<img%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $body);
  while ($result->fetch()) {
    $newbody = preg_replace($regexp, $substitution, $body);
    if ($newbody != $body) {
      // There was a change, so update the record.
      $update = $mysqli->prepare("UPDATE staff_help SET body = ? WHERE id = ?");
      $update->bind_param('si', $newbody, $id);
      $update->execute();
    }
  }

  // Ensure that the new directory folders exist.
  $student_dir = rogo_directory::get_directory('help_student');
  $student_dir->create();
  $student_dir->copy_from_default();
  
  $staff_dir = rogo_directory::get_directory('help_staff');
  $staff_dir->create();
  $staff_dir->copy_from_default();

  $updater_utils->record_update('update_help_image_location');
}
