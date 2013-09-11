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
<title>Rogō: Admin<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>
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

$menudata=array();
$menudata['calendar']=array($string['calendar'],'calendar.php#week' . date("W"),'calendar_icon.png');
$menudata['clearguest']=array($string['clearguestaccounts'],'clear_guest_users.php','clear_guest_users.png');
$menudata['clearlogs']=array($string['clearoldlogs'],'clear_old_logs.php','remove_orphan_icon.png');
$menudata['clearoprphanmedia']=array($string['clearorphanmedia'],'orphan_media.php','remove_orphan_icon.png');
$menudata['clearotraining']=array($string['cleartraining'],'clear_training_module.php','training.png');
$menudata['computerlabs']=array($string['computerlabs'],'list_labs.php','computer_lab_48.png');
$menudata['courses']=array($string['courses'],'list_courses.php','courses_icon.png');
$menudata['deniedlogwarnings']=array($string['deniedlogwarnings'],'view_access_denied.php','access_denied.png');
$menudata['ebelgridtemplates']=array($string['ebelgridtemplates'],'list_ebel_grids.php','grid_48.png');
$menudata['faculties']=array($string['faculties'],'list_faculties.php','faculty.png');
$menudata['imslti']=array($string['imslti'],'../LTI/lti_keys_list.php','ims_logo_64.png');
$menudata['modules']=array($string['modules'],'list_modules.php','modules_icon.png');
$menudata['news']=array($string['announcments'],'list_announcements.php','news_48.png');
$menudata['optimizetables']=array($string['optimizetables'],'optimize_tables.php','optimize_tables_icon.png');
$menudata['questionstatuses']=array($string['questionstatuses'],'list_statuses.php','status_icon.png');
$menudata['schools']=array($string['schools'],'list_schools.php','school_icon.png');
$menudata['smsimports']=array($string['smsimports'],'sms_import_summary.php','sms_import_icon.png');
$menudata['summativescheduling']=array($string['summativescheduling'],'summative_scheduling.php','summative_scheduling.png');
$menudata['summativeexamstats']=array($string['summativeexamstats'],'summative_stats.php?year=' . $summative_year,'summative_stats.png');
$menudata['systemerrors']=array($string['systemerrors'],'sys_error_list.php','bug.png');
$menudata['systeminformation']=array($string['systeminformation'],'system_info.php','information.png');
$menudata['trac']=array($string['trac'],'https://suivarro.nottingham.ac.uk/trac/rogo/','trac_logo.png');
$menudata['usermanagement']=array($string['usermanagement'],'../users/search.php','user_accounts_icon.png');




$titles = array($string['calendar'], $string['clearguestaccounts'], $string['clearoldlogs'], $string['clearorphanmedia'], $string['cleartraining'], $string['computerlabs'], $string['courses'], $string['deniedlogwarnings'], $string['ebelgridtemplates'], $string['faculties'], $string['imslti'], $string['modules'], $string['announcments'], $string['optimizetables'], $string['questionstatuses'], $string['schools'], $string['smsimports'], $string['summativescheduling'], $string['summativeexamstats'], $string['systemerrors'], $string['systeminformation'], $string['trac'], $string['usermanagement']);
  $paths = array('calendar.php#week' . date("W"), 'clear_guest_users.php', 'clear_old_logs.php', 'orphan_media.php', 'clear_training_module.php', 'list_labs.php', 'list_courses.php', 'view_access_denied.php', 'list_ebel_grids.php', 'list_faculties.php', '../LTI/lti_keys_list.php', 'list_modules.php', 'list_announcements.php', 'optimize_tables.php', 'list_statuses.php', 'list_schools.php', 'sms_import_summary.php', 'summative_scheduling.php', 'summative_stats.php?year=' . $summative_year, 'sys_error_list.php', 'system_info.php', 'https://suivarro.nottingham.ac.uk/trac/rogo/', '../users/search.php');
  $images = array('calendar_icon.png', 'clear_guest_users.png', 'clear_logs.png', 'remove_orphan_icon.png', 'training.png', 'computer_lab_48.png', 'courses_icon.png', 'access_denied.png', 'grid_48.png', 'faculty.png', 'ims_logo_64.png', 'modules_icon.png', 'news_48.png', 'optimize_tables_icon.png', 'status_icon.png', 'school_icon.png', 'sms_import_icon.png', 'summative_scheduling.png', 'summative_stats.png', 'bug.png', 'information.png', 'trac_logo.png', 'user_accounts_icon.png');

  if (!$configObject->get('cfg_summative_mgmt')) {  // Take out the summative management scheduling if not activated.
    unset($menudata['smsimports']);

    array_splice($titles, 17, 1);
    array_splice($paths, 17, 1);
    array_splice($images, 17, 1);
  }

ksort($menudata);
foreach($menudata as $menukey=>$menuitem) {
  $parts = explode('.php', $menuitem[1]);
  echo '<a href="' . $menuitem[1] . '" id="' . $parts[0] . '">';
  echo '<div class="container"><img src="../artwork/' . $menuitem[2] . '" width="48" height="48" alt="" class="icon" /><br />' . $menuitem[0] . '</div></a>';

}
/*
  for ($icon_no=0; $icon_no<count($titles); $icon_no++) {
	  $parts = explode('.php', $paths[$icon_no]);
		echo '<a href="' . $paths[$icon_no] . '" id="' . $parts[0] . '">';
		echo '<div class="container"><img src="../artwork/' . $images[$icon_no] . '" width="48" height="48" alt="" class="icon" /><br />' . $titles[$icon_no] . '</div></a>';
  }
*/
?>
</div>

</body>
</html>
