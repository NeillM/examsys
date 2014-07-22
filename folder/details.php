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
require_once '../include/sidebar_menu.inc';
require_once '../include/errors.inc';
require_once '../include/demo_replace.inc';

require_once '../classes/moduleutils.class.php';
require_once '../classes/folderutils.class.php';
require_once '../classes/stateutils.class.php';
require_once '../classes/paperutils.class.php';

$folder = check_var('folder', 'GET', true, false, true);

function getLastFolder($path) {
  $parts = explode(';' , $path);
  $part_no = count($parts);

  if ($part_no > 0) {
    return $parts[$part_no-1];
  } else {
    return $parts[0];
  }
}

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$folder_name = '';
$folder_type = '';
$file_no = 0;
$parent_list = array();
$add_member = false;

// Folder security checks
$orig_folder_name = '';

$result = $mysqli->prepare("SELECT ownerID, name FROM folders WHERE id = ?");
$result->bind_param('i', $folder);
$result->execute();
$result->store_result();
$result->bind_result($folder_ownerID, $orig_folder_name);
$result->fetch();
$result->close();

if ($orig_folder_name == '') {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$parent_list = array();
if (substr_count($orig_folder_name, ';') > 0) {
  $last_semicolon = strrpos($orig_folder_name, ';');
  $path = substr($orig_folder_name, 0, $last_semicolon);
  $parts = explode(';', $path);
  $part_sql = '';
  foreach ($parts as $part) {
    if ($part_sql == '') {
      $part_sql = $part;
    } else {
      $part_sql .= ';' . $part;
    }
    $parent_results = $mysqli->prepare("SELECT id, name FROM folders WHERE name = ? AND ownerID = ? LIMIT 1");
    $parent_results->bind_param('si', $part_sql, $userObject->get_user_ID());
    $parent_results->execute();
    $parent_results->bind_result($parent_id, $parent_name);
    $parent_results->fetch();
    $parent_results->close();
    $parent_list[$parent_id] = $parent_name;
  }
}


$module = '';

if (isset($_POST['submit'])) {
  $folder_results = $mysqli->prepare("SELECT name FROM folders WHERE id = ? LIMIT 1");
  $folder_results->bind_param('i', $folder);
  $folder_results->execute();
  $folder_results->bind_result($folder_parent);
  $folder_results->fetch();
  $folder_results->close();

  $new_folder_name = $folder_parent . ';' . $_POST['folder_name'];

  $duplicate_folder = folder_utils::folder_exists($new_folder_name, $userObject, $mysqli);
  if ($duplicate_folder == false) {
    folder_utils::create_folder($new_folder_name, $userObject, $mysqli);
  }
}

$folders_array = explode(';', $orig_folder_name);
$parts = count($folders_array) - 1;
$selfenrol = 0;

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
	</style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function addQuestion(qType) {
      top.location.href='../question/edit/?type=' + qType + 'folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>';
    }

    function deleteFolder() {
      notice=window.open("../delete/check_delete_folder.php?folderID=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      notice.moveTo(screen.width/2-210,screen.height/2-85);
      if (window.focus) {
        notice.focus();
      }
    }

    function folderProperties() {
      notice=window.open("properties.php?folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=600,height=600,left="+(screen.width/2-300)+",top="+(screen.height/2-300)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }

    function newPaper(paperID) {
      notice = window.open("../paper/new_paper1.php?module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }

    function addTeamMember() {
      notice = window.open("edit_team_popup.php?module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>&calling=paper_list&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=450,height="+(screen.height-200)+",left="+(screen.width/2-325)+",top=10,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
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
  require '../include/folder_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></div>
<?php
  echo "<div style=\"position:absolute; right: 6px; top: 24px\"><input class=\"chk\" type=\"checkbox\" name=\"showretired\" id=\"showretired\" value=\"on\"\"";
  if (isset($state['showretired']) and $state['showretired'] == 'true') echo ' checked="checked"';
  echo " /> " . $string['showretired'] . "</div>\n";
?>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a>
<?php
if (count($parent_list) > 0) {
  foreach ($parent_list as $parent_id=>$parent_name) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="details.php?folder=' . $parent_id . '">' . getLastFolder($parent_name) . '</a>';
  }
}
echo "</div>\n";

echo '<div class="page_title">';
if ($folder != '') {
  echo $folders_array[$parts];
} elseif ($_GET['module'] != '') {
  echo $module_details['moduleid'] . ': <span style="font-weight:normal">' . $module_details['fullname'] . '</span>';
}
echo '</div>';


echo "</div>\n<br />\n";

// Get any sub-folders first.
$tmp_string = '';
if (count($staff_modules) > 0) {
  $tmp_string = " OR idMod IN ('" . implode("','",array_keys($staff_modules)) . "')";
}

$tmp_folder_name = $orig_folder_name . ';%';
$folder_details = $mysqli->prepare("SELECT folders.id, name, color FROM folders WHERE (ownerID=?) AND name LIKE ? AND deleted IS NULL ORDER BY name, folders.id");
$folder_details->bind_param('is', $userObject->get_user_ID(), $tmp_folder_name);
$folder_details->execute();
$folder_details->bind_result($id, $name, $color);
while ($folder_details->fetch()) {
  $display_name = str_replace("$orig_folder_name;","",$name);
  if (substr_count($display_name,';') == 0) {
    echo "<div class=\"f\" ><a href=\"../folder/details.php?folder=$id\" class=\"blacklink\"><img class=\"f_icon\" src=\"../artwork/" . $color . "_folder.png\" alt=\"Folder\" />$display_name</a></div>\n";
  }
}
$folder_details->close();


// New folder.
if (isset($_GET['newfolder']) and $_GET['newfolder'] == 'y' and !isset($_POST['submit'])) {
  echo "<div class=\"f\"><img class=\"f_icon\" src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /><input type=\"text\" size=\"30\" name=\"folder_name\" value=\"" . $string['newfolder'] . "\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['create'] . "\" /></div>\n<br clear=\"all\" />\n<br />";
}

// Get current owner papers.
$query_string = "SELECT DISTINCT paper_ownerID, property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND folder=\"$folder\" AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
$results = $mysqli->prepare($query_string);
$results->execute();
$results->bind_result($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password);
$results->store_result();
$sent_clear_all = false;
if ($results->num_rows > 0) {
  while ($results->fetch()) {
    display_paper_icon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObject);
    $file_no++;
  }
  $results->close();
}

$mysqli->close();
?>
</form>

</div>

</body>
</html>