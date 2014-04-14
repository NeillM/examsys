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

require_once '../classes/dateutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/folderutils.class.php';
require_once '../classes/paperutils.class.php';

$module = check_var('module', 'GET', true, false, true);

$add_member = false;

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

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];
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
  <style>
    .red {background-color:#C00000; color:white; padding:2px}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script language="JavaScript">
    function newPaper() {
      notice = window.open("../paper/new_paper1.php?module=<?php echo $module ?>","paper","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }

    function newQuestion() {
      notice = window.open("../question/new.php?module=<?php echo $module ?>","question","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }

    function addTeamMember() {
      notice = window.open("edit_team_popup.php?module=<?php echo $module ?>&calling=paper_list&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=450,height="+(screen.height-200)+",left="+(screen.width/2-325)+",top=10,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
    
    function resizeList() {
      var offset = $('#list').offset();
      winH = ($(window).height() - offset.top) - 2;

      $('#list').css('height', winH + 'px');
    }

    $(document).ready(function() {
      resizeList();
      
      $(window).resize(function(){
				resizeList();
			});
    });
  </script>
</head>

<body>
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content" class="content">
<table class="header">
<?php
echo '<tr><th><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
echo "</div></th><th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

echo '<tr><th><div style="margin-left:10px; font-size:200%; font-weight:bold">';
echo $module_details['moduleid'] . ': <span style="font-weight:normal">' . $module_details['fullname'] . '</span>';
echo "</div></th><th></th></tr>\n";

echo "</table>\n";

// Paper type folders
echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>Papers</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
echo "<br />\n";
$types_used = module_utils::paper_types($module, $mysqli);
foreach ($types_used as $type=>$no_papers) {
  $url = '../module/type.php?module=' . $module . '&type=' . $type;
  echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">" . Paper_utils::type_to_name($type, $string) . "</a><br /><span class=\"grey\">" . $no_papers . " papers</span></td></tr></table></div>\n";
}
echo "<br clear=\"left\">\n";
echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"../paper/search.php\"><img src=\"../artwork/search_48.png\" alt=\"Folder\" /></a></td><td><a href=\"../paper/search.php\" class=\"blacklink\">Search</a><br /><span class=\"grey\">for papers</span></td></tr></table></div>\n";
echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"\" onclick=\"newPaper(); return false;\"><img src=\"../artwork/new_paper_48.png\" alt=\"Folder\" /></a></td><td><a href=\"\" onclick=\"newPaper(); return false;\" class=\"blacklink\">New Paper</a></td></tr></table></div>\n";

// Question bank section
echo "<br clear=\"left\">\n";
echo "<br />\n";
echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>Question Bank</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
echo "<br />\n";
echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"../question/list.php?module=$module\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"../question/list.php?module=$module\" class=\"blacklink\">All Questions</a></td></tr></table></div>\n";
$bank_types = array('by Keyword'=>'../question/bank.php?type=keyword&module=' . $module, 'by Question Type'=>'../question/bank.php?type=type&module=' . $module, 'by Status'=>'../question/bank.php?type=status&module=' . $module, 'by Bloom\'s Taxonomy'=>'../question/bank.php?type=bloom&module=' . $module, 'by Difficulty'=>'../question/bank.php?type=difficulty&module=' . $module, 'by Discrimination'=>'../question/bank.php?type=discrimination&module=' . $module);
foreach ($bank_types as $type_name=>$url) {
  echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">" . $type_name . "</a></td></tr></table></div>\n";
}
echo "<br clear=\"left\">\n";
echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"../question/search.php\"><img src=\"../artwork/search_48.png\" alt=\"Folder\" /></a></td><td><a href=\"../question/search.php\" class=\"blacklink\">Search</a><br /><span class=\"grey\">for questions</span></td></tr></table></div>\n";
echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"\" onclick=\"newQuestion(); return false;\"><img src=\"../artwork/question_stats.png\" alt=\"Folder\" /></a></td><td><a href=\"\" onclick=\"newQuestion(); return false;\" class=\"blacklink\">New Question</a></td></tr></table></div>\n";


// User section
echo "<br clear=\"left\">\n";
echo "<br />\n";
echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>Students</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
echo "<br />\n";
echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"../users/search.php\"><img src=\"../artwork/search_48.png\" alt=\"Folder\" /></a></td><td><a href=\"../users/search.php\" class=\"blacklink\">Search</a><br /><span class=\"grey\">for users</span></td></tr></table></div>\n";

if ($_GET['module'] != '0') {
  $current_year = date_utils::get_current_academic_year();
  $student_cohort = module_utils::get_student_members($current_year, $module, $mysqli);

  $url = '../users/search.php?submit=Search&team=' . $module . '&calendar_year=' . $current_year . '&students=on&search_username=&student_id=';
  $student_no = count($student_cohort);
  if ($student_no == 0) {
    $student_class = 'red';
  } else {
    $student_class = 'grey';
  }
  echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/user_accounts_icon.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">Cohort List</a><br /><span class=\"$student_class\">" . $current_year . " - $student_no students</span></td></tr></table></div>\n";

  $url = '../users/import_users_metadata.php?module=' . $module;
  echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/user_metadata_48.png\" alt=\"Folder\" /></a></td><td><a href=\"$url\" class=\"blacklink\">Add Metadata</a><br /><span class=\"grey\">extra data about students</span></td></tr></table></div>\n";
}

$mysqli->close();
?>
</div>

</body>
</html>