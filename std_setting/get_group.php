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
require '../include/std_set_shared_functions.inc';
require_once '../classes/paperproperties.class.php';
require_once '../include/errors.inc';

$paperID = check_var('paperID', 'GET', true, false, true);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Get any questions to exclude.
$exclude = array();
$result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper = ?");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $parts);
while ($result->fetch()) {
  $exclude[$q_id] = $parts;
}
$result->close();

// Calculate marks for the current paper.
$marks_array = array();
ss_get_marks_correct($mysqli, $paperID, $exclude, $marks_array);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['selectreviewers'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>
<body>
<?php
	require '../include/std_set_menu.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(98);
?>
<div id="content" class="content" style="font-size:80%">
	<form action="group_set_angoff.php" method="post">	
	
<?php
if (isset($_GET['module'])) {
  $modules = explode(',', $_GET['module']);
  $module = $modules[0];
} else {
  $module = '';
}

$folder = '';
if (isset($_GET['folder']) and $_GET['folder'] != '') {
  $folder = $_GET['folder'];
  $result = $mysqli->prepare("SELECT name FROM folders WHERE id = ? LIMIT 1");
  $result->bind_param('i', $folder);
  $result->execute();
  $result->bind_result($folder_name);
  $result->fetch();
  $result->close();
}

echo "\n<table class=\"header\">\n";
echo "<tr><th><div class=\"breadcrumb\"><a href=\"../index.php\">{$string['home']}</a>";
if ($folder != '') {
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
} elseif (isset($_GET['module']) and $_GET['module'] != '') {
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $module . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
echo "&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">" . $propertyObj->get_paper_title() . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">{$string['standardssetting']}</a></div>";
echo '<div style="font-size:200%; color:black; font-weight:bold; margin-left:10px">' . $string['selectreviewers'] . '</div>';
echo "</th><th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";
echo "</table>\n";

?>
<table class="header">
<tr>
	<th style="width:18px">&nbsp;</th>
	<th style="width:20%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['standardsetter'] ?>&nbsp;</th>
	<th style="width:15%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['date'] ?>&nbsp;</th>
	<th style="width:8%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['passscore'] ?></th>
	<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['method'] ?></th>
	<th style="width:25%"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp</th>
</tr>
<?php
$reviews = get_reviews($mysqli, 'group', $paperID, $propertyObj->get_total_mark());
$line_no = 0;
foreach ($reviews as $review) {
  $line_no++;
  if ($review['group_review'] == 'No') {
    echo "<tr><td align=\"center\"><input type=\"checkbox\" name=\"member{$line_no}\" value=\"{$review['std_setID']}\" checked=\"checked\" /></td><td>&nbsp;{$review['name']}</td><td>&nbsp;{$review['display_date']}</td><td style=\"text-align:right\">{$review['pass_score']}%&nbsp;</td><td>&nbsp;{$review['method']}</td><td></td></tr>\n";
  }
}
$mysqli->close();
echo "<table>\n";
?>
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<br /><p style="margin-left:6px"><input type="submit" name="submit" style="width:100px" value="<?php echo $string['review'] ?>" /></p>
</form>
</div>
</body>
</html>
