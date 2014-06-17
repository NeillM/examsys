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
* Rogō hompage. Uses ../include/options_menu.inc for the sidebar menu.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once './include/staff_student_auth.inc';
require_once './include/errors.inc';
require_once './include/sidebar_menu.inc';
require_once './classes/recyclebin.class.php';
require_once './config/index.inc';
require_once './classes/paperutils.class.php';
require_once './classes/folderutils.class.php';

$userObject = UserObject::get_instance();

// Redirect Students (if not also staff), External Examiners and Invigilators to their own areas.
if ($userObject->has_role('Student') and !($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')))) {
  header("location: ./paper/");
  exit();
} elseif ($userObject->has_role('External Examiner')) {
  header("location: ./reviews/");
  exit();
} elseif ($userObject->has_role('Invigilator')) {
  header("location: ./invigilator/");
  exit();
}

// If we're still here we should be staff
require_once './include/staff_auth.inc';
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <link rel="stylesheet" type="text/css" href="./css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="./css/header.css" />
  <link rel="stylesheet" type="text/css" href="./css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="./css/warnings.css" />
  <link rel="stylesheet" type="text/css" href="./css/announcements.css" />
	
	
	<style>
	.recent_icon {width:16px; height:16px; padding-right:8px}
	</style>

  <script src="./js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript" src="./js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="./js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="./js/toprightmenu.js"></script>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script src="./js/sidebar.js" type="text/javascript"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
<?php
	if ($configObject->get('cfg_interactive_qs') == 'html5') {
?>
			if (!isCanvasSupported()){
			  $('#html5warn').show();
			}
<?php
	}
?>
		});

		function isCanvasSupported(){
			var elem = document.createElement('canvas');
			return !!(elem.getContext && elem.getContext('2d'));
		}
		
    function startPaper(paperID, fullsc) {
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      if (fullsc == 0) {
        window.open("./reviews/start.php?id="+paperID+"&review=1","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      } else {
        window.open("./reviews/start.php?id="+paperID+"&review=1","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      }
    }
    
    function illegalChar(codeID) {
      if (codeID == 59) {
        alert("Character ';' illegal - please use alternative characters in folder name.");
      }
      event.returnValue = false;
    }

    function newPaper(paperID) {
      notice = window.open("./paper/new_paper1.php?folder=","properties","width=750,height=500,left="+(screen.width/2-375)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
    
    function hideAnnouncement(announcementID) {
      $('#announcement' + announcementID).hide();
      
      var request = $.ajax({
        url: "./ajax/staff/hide_announcement.php",
        type: "get",
        data: {announcementID: announcementID},
        timeout: 30000, // timeout after 30 seconds
        dataType: "html",
      });
    
    }

  </script>
</head>

<body>

<?php
  require './include/options_menu.inc';
  require './include/toprightmenu.inc';
  require './include/icon_display.inc';
	
	echo draw_toprightmenu();
?>

<div id="content" class="content">
<form id="theform" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?php
  // -- Create new folder ---------------------------------------------------
  $duplicate_folder = false;
  if (isset($_POST['submit'])) {
    $new_folder_name = $_POST['folder_name'];

    $duplicate_folder = folder_utils::folder_exists($new_folder_name, $userObject, $mysqli);
    if ($duplicate_folder == false) {
      folder_utils::create_folder($new_folder_name, $userObject, $mysqli);
    }
  }
?>

<table cellpadding="0" cellspacing="0" border="0" class="header">
  <tr>
    <th style="padding-left:16px; padding-top:5px">

    <img src="./artwork/r_logo.gif" alt="logo" class="logo_img" />
    <div class="logo_lrg_txt">Rog&#333;</div>
    <div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem']; ?></div>

    </th>
    <th style="text-align:right; vertical-align:top"><img src="./artwork/toprightmenu.gif" id="toprightmenu_icon"></th>
  </tr>
</table>
<?php
  $as_pos = strpos($configObject->get('cfg_install_type'),' as ');
  if ($as_pos !== false) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"width:40px\"><div class=\"greywarn\"><img src=\"./artwork/agent.png\" width=\"32\" height=\"32\" alt=\"Impersonate\" /></div></td><td><div class=\"greywarn\">" . $string['loggedinas'] . " " . substr($configObject->get('cfg_install_type'), ($as_pos+4)) . "</div></td></tr></table>\n";
  }
	if ($configObject->get('cfg_interactive_qs') == 'html5') {
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; display:none" id="html5warn"><tr><td style="width:32px"><div class="yellowwarn"><img src="./artwork/html5_32.png" width="32" height="32" alt="HTML5" style="position:relative; left:6px; top:1px" /></div></td><td><div class="yellowwarn">&nbsp;&nbsp;<?php echo $string['html5warn']; ?></div></td></tr></table>
<?php
  }
?>
<div style="padding-left:6px; padding-right:14px">
<?php

  // Check for any news/announcements
  $news_icons = array('', 'news_64.png', 'new_64.png', 'tip_64.png', 'software_64.png', 'exclamation_64.png', 'sync_64.png', 'megaphone_64.png');
  $result = $mysqli->prepare("SELECT id, title, staff_msg, icon FROM announcements WHERE NOW() > startdate AND NOW() < enddate AND deleted IS NULL");
  $result->execute();
  $result->bind_result($announcementID, $news_title, $staff_msg, $icon);
  while ($result->fetch()) {
    if (!isset($_SESSION['announcement' . $announcementID])) {
      echo "<br /><div class=\"announcement\" id=\"announcement$announcementID\"><img src=\"./artwork/close_note.png\" style=\"display:block; float:right\" onclick=\"hideAnnouncement($announcementID)\" /><div style=\"min-height:64px; padding-left:80px; padding-top:5px; background: transparent url('./artwork/" . $news_icons[$icon] . "') no-repeat 5px 5px;\"><strong>$news_title</strong><br />\n<br />\n$staff_msg</div></div>\n";
    }
  }
  $result->close();

  // -- Display any papers for review ---------------------------------
  $result = $mysqli->prepare("SELECT paper_title, property_id, fullscreen, DATE_FORMAT(internal_review_deadline,'%d/%m/%Y') AS internal_review_deadline, crypt_name FROM (properties, properties_reviewers) WHERE properties.property_id = properties_reviewers.paperID AND deleted IS NULL AND internal_review_deadline >= CURDATE() AND reviewerID = ? AND type = 'internal' ORDER BY paper_title");
  $tmp = $userObject->get_user_ID();
  $result->bind_param('i', $tmp);
  $result->execute();
  $result->bind_result($paper_title, $property_id, $fullscreen, $internal_review_deadline, $crypt_name);
  $result->store_result();
  if ($result->num_rows() > 0) {
    echo "<br />\n";
    echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['papersforreview'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  }
  while ($result->fetch()) {
    $reviewed = '';
    $result2 = $mysqli->prepare("SELECT DATE_FORMAT(MAX(started),'%d/%m/%Y %T') AS started FROM review_metadata WHERE reviewerID = ? AND paperID = ?");
    $result2->bind_param('ii', $userObject->get_user_ID(), $property_id);
    $result2->execute();
    $result2->bind_result($reviewed);
    $result2->fetch();
    $result2->close();
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"#\" onclick=\"startPaper('" . $crypt_name . "'," . $fullscreen . "); return false;\"><img src=\"./artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Paper Icon\" /></a></td>\n";
    echo "  <td><a href=\"#\" onclick=\"startPaper('" . $crypt_name . "'," . $fullscreen . "); return false;\">" . $paper_title . "</a><br /><div style=\"color:#C00000\">" . $string['deadline'] . " " . $internal_review_deadline . "</div>";
    if ($reviewed == '') {
      echo "<span style=\"color:white; background-color:#FF4040\">&nbsp;" . $string['notreviewed'] . "&nbsp;</span>";
    } else {
      echo "<span style=\"color:#808080\">" . $string['reviewed'] . ": $reviewed</span>";
    }
    echo "</td></tr></table></div>\n";
  }
  if ($result->num_rows() > 0) echo '<br clear="left" />';
  $result->close();

  // -- Display personal folders --------------------------------------
  $module_sql = '';
  if (count($userObject->get_staff_modules()) > 0) {
    $module_sql = " OR idMod IN (" . implode(',', array_keys($userObject->get_staff_modules())) . ")";
  }

  $result = $mysqli->prepare("SELECT DISTINCT id, name, color FROM folders LEFT JOIN folders_modules_staff ON folders.id = folders_modules_staff.folders_id WHERE  (ownerID=? $module_sql) AND name NOT LIKE '%;%' AND deleted IS NULL ORDER BY name, id");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($id, $name, $color);
  $result->store_result();

  echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['myfolders'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  while ($result->fetch()) {
    echo "<div class=\"f\" ><a href=\"./folder/details.php?folder=$id\" class=\"blacklink\"><img class=\"f_icon\" src=\"./artwork/" . $color . "_folder.png\"  alt=\"Folder\" />$name</a></div>\n";
  }
  $result->close();

  if (isset($_GET['newfolder']) and $_GET['newfolder'] == 'y' or $duplicate_folder == true) {
    if (isset($_POST['submit']) and $_POST['submit'] and $duplicate_folder == true) {
      echo "<script language=\"JavaScript\">alert(\"" . $string['duplicatefoldername'] . "\")</script>";
      echo "<div class=\"f\"><img class=\"f_icon\" src=\"./artwork/yellow_folder.png\" alt=\"Folder\" /><input class=\"errfield\" type=\"text\" size=\"30\" name=\"folder_name\" value=\"$new_folder_name\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['create'] . "\" /></div>\n";
    } elseif (!isset($_POST['submit'])) {
      echo "<div class=\"f\"><img class=\"f_icon\" src=\"./artwork/yellow_folder.png\" alt=\"Folder\" /><input type=\"text\" size=\"30\" name=\"folder_name\" value=\"" . $string['newfolder'] . "\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['create'] . "\" /></div>\n";
    }
  }

  echo "<div class=\"f\"><a href=\"./delete/recycle_list.php\" class=\"blacklink\"><img class=\"f_icon\" src=\"./artwork/recycle_bin.png\" alt=\"" . $string['recyclebin'] . "\" />" . $string['recyclebin'] . "</a></div>\n";
?>
<br clear="left" />
<?php
  if (!isset($_GET['folder']) OR $_GET['folder'] == '') {
    echo "<br />\n";
    // -- Display module folders ------------------------------------
    $staff_team_array = $userObject->get_staff_team_modules();

    echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['mymodules'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    if ($userObject->has_role('SysAdmin')) {
      echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"./module/all.php\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" /></a></td><td><a href=\"./module/all.php\" class=\"blacklink\"><strong>" . $string['allmodules']  . "</strong></a><br /><span style=\"color:#C00000\">(" . $string['sysadminonly'] . ")</span></td></tr></table></div>\n";
    } elseif ($userObject->has_role('Admin')) {
      echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"./module/all.php\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" /></a></td><td><a href=\"./module/all.php\" class=\"blacklink\"><strong>" . $string['allmodulesinschool'] . "</strong></a><br /><span style=\"color:#C00000\">(" . $string['adminonly'] . ")</span></td></tr></table></div>\n";
    }
    foreach ($staff_team_array as $idMod => $folder_title) {
      $url = './module/index.php?module=' . $idMod;
	    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"./artwork/yellow_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">" . $folder_title['code'] . "</a><br /><span class=\"grey\">" . $folder_title['fullName'] . "</span></td></tr></table></div>\n";
    }
    $url = './module/index.php?module=0';
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"./artwork/red_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">Unassigned</a><br /><span class=\"grey\">Questions/papers not on any module</span></td></tr></table></div>\n";

    $module_no = count($staff_team_array);
    if ($module_no == 0) {
      echo '<div style="color:#C00000; padding-left:15px"><img src="./artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="!" /> <strong>' . $string['warning'] . '</strong> ' . $string['nomodules'] . ' <a href="mailto:' . $configObject->get('support_email') . '">' . $configObject->get('support_email') . '</div>';
    }
  }

  $mysqli->close();
?>

</div>
</div>
</body>
</html>
