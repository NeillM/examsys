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
* Displays a list of papers.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/icon_display.inc';
require_once '../include/errors.inc';
require_once '../include/demo_replace.inc';

require_once '../classes/moduleutils.class.php';
require_once '../classes/stateutils.class.php';
require_once '../classes/paperutils.class.php';

$module = check_var('module', 'GET', true, false, true);
$type = check_var('type', 'GET', true, false, true);

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

if ($_GET['module'] != '0') {
  $module_details = module_utils::get_full_details_by_ID($module, $mysqli);

  if (!$module_details) {
   $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
   $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  } elseif ($module_details['active'] == 0) {
   $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
   $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);	
  }
} else {
  $module_details['moduleid'] = 'Unassigned';
  $module_details['fullname'] = 'Questions/papers not on any module'; 
  $module_details['checklist'] = '';
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
  <?php
    if (isset($state['showretired']) and $state['showretired'] == 'true') {
      echo ".retired {display:block}\n";
    } else {
      echo ".retired {display:none}\n";
    }
    ?>
    .sum_cal {margin-left: 36px; margin-top: 10px; margin-bottom: 12px}
    .subsect_table {margin-left: 12px; margin-bottom: 8px}
	</style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function addQuestion(qType) {
      top.location.href='../question/edit/?type=' + qType + '&module=<?php echo $module; ?>';
    }
    $(document).ready(function() {
      $('#showretired').click(function() {
        $('.retired').toggle();
      });
      
    });
  </script>
</head>

<body>
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
  
  $types_used = module_utils::paper_types($module, $mysqli);
?>
<div id="content">
        
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></div>
<?php
  echo "<div style=\"position:absolute; right: 6px; top: 24px\"><label><input class=\"chk\" type=\"checkbox\" name=\"showretired\" id=\"showretired\" value=\"on\"\"";
  if (isset($state['showretired']) and $state['showretired'] == 'true') echo ' checked="checked"';
  echo " />" . $string['showretired'] . "</label></div>\n";
?>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="index.php?module=<?php echo $module ?>"><?php echo $module_details['moduleid'] ?></a></div>
  <div class="page_title"><?php echo $string['papers'] ?>: <span style="font-weight:normal"><?php echo $string[strtolower($types_array[$type])] ?> (<?php echo $types_used[$type] ?>)</span></div>
</div>

  
<?php
if ($_GET['type'] == 2) {
  echo '<table class="sum_cal"><tr><td><a href="../admin/calendar.php"><img src="../artwork/calendar_icon.png" width="48" height="48" /></a></td><td><strong><a href="../admin/calendar.php">Summative Exam Calendar<br />' . date('Y') . '</a></strong></td></tr></table>';
}

// UPDATED SQL query simplified removed the modules table as no data was coming from it.  also removed distinct as group by was doing it.  the user data is returned but for some reason the icons alt tags (that contain the user data don't display
if ($_GET['module'] != '0') {
  $sql = "SELECT calendar_year, paper_ownerID, properties.property_id, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM (properties, properties_modules, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = ? AND paper_type = ? AND properties.paper_ownerID = users.id  AND deleted IS NULL GROUP BY paper_title ORDER BY calendar_year DESC, paper_title";
  $results = $mysqli->prepare($sql);
  $results->bind_param('is', $module, $type);
} else {
  $sql = "SELECT calendar_year, paper_ownerID, properties.property_id, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM (properties, users) LEFT JOIN properties_modules ON properties.property_id = properties_modules.property_id LEFT JOIN papers ON properties.property_id = papers.paper WHERE properties_modules.idMod IS NULL AND paper_type = ? AND properties.paper_ownerID = users.id AND paper_ownerID = ? AND deleted IS NULL GROUP BY paper_title ORDER BY calendar_year DESC, paper_title";
  $results = $mysqli->prepare($sql);
  $results->bind_param('si', $type, $userObject->get_user_ID());
}
$results->execute();
$results->bind_result($calendar_year, $paper_ownerID, $property_id, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password);
$results->store_result();
$old_calendar_year = 'zzzz';
$sent_clear_all = false;
if ($results->num_rows > 0) {
  while ($results->fetch()) {
    if ($old_calendar_year != $calendar_year) {
      if ($sent_clear_all) {
        echo "<br clear=\"left\" />";
      }
      $sent_clear_all = true;
      
      if ($calendar_year == '') {
        $display_calendar_year = $string['unspecifiedsession'];
      } else {
        $display_calendar_year = $calendar_year;      
      }

      //echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $display_calendar_year . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
      echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $display_calendar_year . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
    }
    display_paper_icon($paper_ownerID, $property_id, $type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObject);
    $old_calendar_year = $calendar_year;
  }
  $results->close();
}
$mysqli->close();
?>
</div>

</body>
</html>