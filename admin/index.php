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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  require '../include/sidebar_menu.inc';
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title>Rog&#333;: Admin<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/admin.css" />

<?php echo $configObject->get('cfg_js_root') ?>
<script language="JavaScript" src="../js/jquery-1.6.1.min.js"></script>
<script language="JavaScript" src="../js/sidebar.js"></script>
<script language="JavaScript">
  $(document).ready(function() {
    $("#clear_training_module").click(function() {
		  var msg = '<?php echo $string['msg1']; ?>';
			return confirm(msg);
		});

    $("#clear_old_logs").click(function() {
		  var msg = '<?php echo $string['msg2']; ?>';
			return confirm(msg);
		});
	});

</script>
</head>

<body>

<?php
  require '../include/admin_options.inc';

  // How many guest accounts are reserved
  $results = $mysqli->query("SELECT id FROM temp_users");
  $temp_account_no = $results->num_rows;
  $results->close();

  // How many system errors are there
  $results = $mysqli->query("SELECT id FROM sys_errors WHERE fixed IS NULL");
  $sys_error_no = $results->num_rows;
  $results->close();

  // How many announcements are there
  $results = $mysqli->query("SELECT id FROM announcements WHERE startdate <= NOW() AND enddate >= NOW() AND deleted IS NULL");
  $announcement_no = $results->num_rows;
  $results->close();

  // How many papers need scheduling
  $results = $mysqli->query("SELECT property_id FROM (properties, scheduling) WHERE (start_date IS NULL OR end_date IS NULL) AND properties.property_id = scheduling.paperID AND deleted IS NULL");
  $scheduling_no = $results->num_rows;
  $results->close();

  $mysqli->close();
?>

<div id="content" class="content" style="font-size:80%">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['administrativetools']; ?></div></th></tr>
<tr><th class="bevel"></th></tr>
</table>

<?php
  if ($temp_account_no > 0) {
    $string['clearguestaccounts'] .= ' <span class="corners"><span class="num">&nbsp;' . $temp_account_no . '&nbsp;</span></span>';
  }

  if ($sys_error_no > 0) {
    $string['systemerrors'] .= ' <span class="corners"><span class="num">&nbsp;' . $sys_error_no . '&nbsp;</span></span>';
  }

  if ($announcement_no > 0) {
    $string['announcments'] .= ' <span class="corners"><span class="num">&nbsp;' . $announcement_no . '&nbsp;</span></span>';
  }

  if ($scheduling_no > 0) {
    $string['summativescheduling'] .= ' <span class="corners"><span class="num">&nbsp;' . $scheduling_no . '&nbsp;</span></span>';
  }

  $summative_year =  date('Y');
  if (date('n') < 7) {
    $summative_year--;
  }

	$menudata = array();
	$menudata['calendar']							= array('calendar.php#week' . date("W"), 'calendar_icon.png');
	$menudata['clearguestaccounts']		= array('clear_guest_users.php', 'clear_guest_users.png');
	$menudata['clearoldlogs']					= array('clear_old_logs.php', 'clear_logs.png');
	$menudata['clearorphanmedia']			= array('orphan_media.php', 'remove_orphan_icon.png');
	$menudata['cleartraining']				= array('clear_training_module.php', 'training.png');
	$menudata['computerlabs']					= array('list_labs.php', 'computer_lab_48.png');
	$menudata['courses']							= array('list_courses.php', 'courses_icon.png');
	$menudata['deniedlogwarnings']		= array('view_access_denied.php', 'access_denied.png');
	$menudata['ebelgridtemplates']		= array('list_ebel_grids.php', 'grid_48.png');
	$menudata['faculties']						= array('list_faculties.php', 'faculty.png');
	$menudata['imslti']								= array('../LTI/lti_keys_list.php', 'ims_logo_64.png');
	$menudata['modules']							= array('list_modules.php', 'modules_icon.png');
	$menudata['announcments']					= array('list_announcements.php', 'news_48.png');
	$menudata['optimizetables']				= array('optimize_tables.php', 'optimize_tables_icon.png');
	$menudata['questionstatuses']			= array('list_statuses.php', 'status_icon.png');
	$menudata['schools']							= array('list_schools.php', 'school_icon.png');
  if ($configObject->get('cfg_summative_mgmt')) {  // Enable summative management scheduling if not activated.
		$menudata['summativescheduling']	= array('summative_scheduling.php', 'summative_scheduling.png');
	}
	$menudata['summativeexamstats']		= array('summative_stats.php?year=' . $summative_year, 'summative_stats.png');
	$menudata['systemerrors']					= array('sys_error_list.php', 'bug.png');
	$menudata['systeminformation']		= array('system_info.php', 'information.png');
	$menudata['trac']									= array('https://suivarro.nottingham.ac.uk/trac/rogo/', 'trac_logo.png');
	$menudata['usermanagement']				= array('../users/search.php', 'user_accounts_icon.png');

	foreach($menudata as $menukey => $menuitem) {
		$parts = explode('.php', $menuitem[0]);
		echo '<a class="blacklink" href="' . $menuitem[0] . '" id="' . $parts[0] . '">';
		echo '<div class="container"><img src="../artwork/' . $menuitem[1] . '" alt="" class="icon" /><br />' . $string[$menukey] . '</div></a>';
	}

?>
</div>

</body>
</html>
