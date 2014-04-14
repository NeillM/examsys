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

require_once '../classes/moduleutils.class.php';
require_once '../classes/folderutils.class.php';
require_once '../classes/keywordutils.class.php';
require_once '../classes/stateutils.class.php';
require_once '../classes/paperutils.class.php';
require_once '../classes/question_status.class.php';
require_once '../classes/questionbank.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$type = check_var('type', 'GET', true, false, true);
$module = check_var('module', 'GET', true, false, true);

$module_details = module_utils::get_full_details_by_ID($module, $mysqli);

if (!$module_details) {
 $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
 $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
} elseif ($module_details['active'] == 0) {
 $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
 $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);	
}

$qbank = new QuestionBank($module, $string, $mysqli);

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['questionbank'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content" class="content">
<table class="header">
<?php
echo '<tr><th><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../module/index.php?module=' . $module . '">' . $module_details['moduleid'] . '</a>';
echo "</div></th><th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

echo '<tr><th><div style="margin-left:10px; font-size:200%; font-weight:bold">';
echo 'Question Bank: <span style="font-weight:normal">';
if ($type == 'type') {
  echo $string['bytype']; 
} elseif ($type == 'bloom') {
  echo $string['byblooms']; 
} elseif ($type == 'keyword') {
  echo $string['bykeyword']; 
} elseif ($type == 'status') {
  echo $string['bystatus'];
} elseif ($type == 'difficulty') {
  echo $string['bydifficulty'];
} elseif ($type == 'discrimination') {
  echo $string['bydiscrimination'];
}
echo '</span>';
echo "</div></th><th></th></tr>\n";

echo "</table>\n";

function get_keywords($idMod, $db) {
  $keywords_array = array();
  
  $result = $db->prepare("SELECT keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID = modules.id AND modules.id = $idMod ORDER BY keyword");
  $result->execute();
  $result->bind_result($keyword, $keywordID);
  while ($result->fetch()) {
    $keywords_array[$keywordID] = $keyword;
  }
  $result->close();
  
  return $keywords_array;
}


$bank_types = $qbank->get_categories($type);
$stats      = $qbank->get_stats($type);

if ($type != 'keyword') {
  echo "<br />\n";
}

$old_section = '';
foreach ($bank_types as $id=>$type_name) {
  $grey_text = '';
  $url = 'list.php?type=' . $type . '&subtype=' . $id . '&module=' . $module;
  
  if ($type == 'keyword') {
    if ($old_section != $type_name{0}) {
      echo "<br clear=\"left\" />\n";
      echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $type_name{0} . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    }
    $old_section = $type_name{0};
  }
  if (isset($stats[$id])) {
    $grey_text = '<br /><span class="grey">' . number_format($stats[$id]) . ' questions</span>';
  } elseif(isset($stats[$type_name])) {
    $grey_text = '<br /><span class="grey">' . number_format($stats[$type_name]) . ' questions</span>';
  }
  echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">" . $type_name . "</a>$grey_text</td></tr></table></div>\n";
}
$mysqli->close();
?>
</div>

</body>
</html>