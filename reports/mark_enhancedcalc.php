<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Marks all enhanced calculation questions for a summative paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require_once '../classes/paperproperties.class.php';
require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
require_once '../plugins/questions/enhancedcalc/helpers/enhancedcalc_helper.php';

set_time_limit(0);

$paperID = check_var('paperID', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

/*if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
*/


// Get the enhanced calculation questions on the paper.
$q_ids = array();
$result = $mysqli->prepare("SELECT question, settings FROM papers, questions WHERE papers.question = questions.q_id AND q_type = 'enhancedcalc' AND paper = ?");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $settings);
while ($result->fetch()) {
  $q_ids[$q_id] = $settings;
}
$result->close();

$server_connection = true;

$statuses = array();
foreach ($q_ids as $q_id => $setting) {
  $data = enhancedcalc_remark('2', $paperID, $q_id, $setting, $mysqli, 'all');
	if ($data[-3] > 0) {
		$server_connection = false;
	}
  $statuses[$q_id] = $data;
}

//var_dump($statuses);

?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	
  <title>Calculation Question Marking<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>
	
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
	<style>
	  body {font-size:90%}
	  h1 {font-size:140%}
	</style>
	
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
	$(document).ready(function() {
	  $('#submit').click(function() {
		  history.back();
		})
	});
	
	</script>
</head>

<body>
<table class="header">
<?php
echo '<tr><th><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';

if (isset($_GET['folder']) and $_GET['folder'] != '') {
	echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
} elseif ( isset( $_GET['module'] ) and $_GET['module'] != '' ) {
	echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $paperID . '">' . $properties->get_paper_title() . '</a></div>';

echo "</div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" /></a></th></tr>\n";

echo '<tr><th><div style="margin-left:10px; font-size:200%; font-weight:bold">' . $string['calculationguestionmarking'] . '</div></th>';
echo "<th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"></th></tr>\n";

echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";

if (!$server_connection) {
  echo "<table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"redwarn\" style=\"width:30px\"><img style=\"margin-left:6px; margin-right:10px;\" src=\"../artwork/red_warning.png\" width=\"32\" height=\"32\" alt=\"Warning\" /></td><td class=\"redwarn\">{$string['serverconnectionerr']}</td></table>\n";
	echo '<br /><input type="button" name="submit" id="submit" value="' . $string['back'] . '" style="width:100px" />';
} else {
	echo "<h1>{$string['markingcomplete']}</h1>\n";
	echo '<input type="button" name="submit" id="submit" value="' . $string['ok'] . '" style="width:100px" />';
}
?>

</body>
</html>