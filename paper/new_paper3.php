<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';

$assessment = new assessment($mysqli, $configObject);

$paper_name = check_var('paper_name', 'POST', true, false, true);
$paper_type = check_var('paper_type', 'POST', true, false, true);
$startdate = check_var('startdate', 'POST', true, false, true);
$enddate = check_var('enddate', 'POST', true, false, true);
$paper_owner = check_var('paper_owner', 'POST', true, false, true);
$session = $_POST['session'];
if (empty($session)) {
  $yearutils = new yearutils($mysqli);
  $session = $yearutils->get_current_session();  
}
$papertype = $assessment->get_type_value($paper_type);

// Process the posted modules
$modules = array();
$first = true;
for ($i=0; $i<$_POST['module_no']; $i++) {
  if (isset($_POST['mod' . $i])) {
    $module_code = module_utils::get_moduleid_from_id($_POST['mod' . $i], $mysqli);
    if ($first == true) {
      $first_module = $_POST['mod' . $i];
      $first = false;
    }
    $modules[$_POST['mod' . $i]] = $module_code;
  }
}

try {
    $property_id = $assessment->create($paper_name, $papertype, $paper_owner , $startdate, $enddate, '', NULL, $session, $modules, $_POST['timezone']);

    if ($configObject->get('cfg_summative_mgmt') and $papertype == $assessment::TYPE_SUMMATIVE) {
        $assessment->schedule($property_id, $_POST['period'], $_POST['barriers_needed'], $_POST['cohort_size'], $_POST['notes'], $_POST['sittings'], $_POST['campus']);
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>New Paper</title>
  <script>
    function jumpToPaper() {
      <?php
        if ($_POST['folder'] != '') {
          echo 'window.opener.location = "details.php?paperID=' . $property_id . '&folder=' . $_POST['folder'] . '";';
        } else {
          echo 'window.opener.location = "details.php?paperID=' . $property_id . '&module=' . $first_module . '";';
        }
      ?>
      window.close();
    }
  </script>
</head>
<body onload="jumpToPaper()">
</body>
</html>
