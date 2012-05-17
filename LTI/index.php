<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 30/04/12
 * Time: 13:02
 * To change this template use File | Settings | File Templates.
 */


require '../include/staff_student_auth.inc';
require '../include/sidebar_menu.inc';
require '../config/index.inc';
require_once '../classes/searchutils.class.php';
require_once  $cfg_web_root . 'include/lti_func.php';


global $cfg_long_date_time;

if (isset($_REQUEST['paperlinkID'])) {
  //  print_r($_SESSION);
  $retlookup = $_SESSION['postlookup'][$_REQUEST['paperlinkID']];
  unset($_SESSION['postlookup']);

  if ($retlookup > 0) {
    $info = $lti->getResourceKey(1);
    addltiresource($mysqli, $info[0], $info[1], $retlookup, 'paper');
  }

}


// jump check


//print_r($info);

$info = $lti->getResourceKey(1);

$returned = lookupltiresource($mysqli, $info[0], $info[1]);

//print_r($returned);
if ($returned === false AND !((strpos($userroles, 'SysAdmin') !== false) OR (strpos($userroles, 'Staff') !== false))) {
  echo "<html>\n<head>\n<title>" . $string['unavailablepaper'] . "</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
  echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"margin-left:60px\">" . $string['unavailablepaper'] . "</h1>\n";
  //echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0;  background-color:#C0C0C0; height:1px; border:0px\" />\n<p style=\"margin-left:60px\">". $string['ltifirstlogindesc']. "</p>\n</body>\n</html>";
  exit();
}
elseif ($returned === false) {
  //paper choice display
  $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

  print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=$cfg_page_charset" />
  <title>Rogō $cfg_install_type</title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
  .sch {padding-left:32px; text-indent:-20px}
  .greysch {padding-left:12px; color:#808080}
  .mod {padding-left:60px; text-indent:-20px}
  </style>


   $cfg_js_root

  <script language="JavaScript">


    function showHide(sectionID) {
      sectionID = 'block' + sectionID;
      current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
      document.getElementById(sectionID).style.display = current;
    }
  </script>
</head>
<body>
<div id="content" class="content" style="font-size:80%">


END;

    // -- Display personal folders --------------------------------------
    if (!isset($teams)){
        $teams = getUserTeams($userID, $mysqli);
    }
    $module_sql = '';
    foreach ($teams as $individual_team){
        if (trim($individual_team) != '') $module_sql .= " OR team_name LIKE '%$individual_team%'";
    }

    $resulta = $mysqli->prepare("SELECT id, name, team_name, color FROM folders WHERE (ownerID=$userID $module_sql)  AND deleted IS NULL ORDER BY name, id"); //AND name NOT LIKE '%;%'
    $resulta->execute();
    $resulta->bind_result($id, $name, $team_name, $color);
    $resulta->store_result();

    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['myfolders'] . " (" . ($resulta->num_rows() + 1) . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    while ($resulta->fetch()) {
        echo "<div class=\"f\" ><a href=\"../folder/details.php?folder=$id\" class=\"blacklink\"><img style=\"vertical-align:middle; padding-right:8px\" src=\"../artwork/" . $color . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" />$name</a></div>\n";
/*

        $tmp_folder = $id;

        $folder=$tmp_folder;
        $result = $mysqli->prepare("SELECT ownerID, name, team_name FROM folders WHERE id=?");
        $result->bind_param('i', $tmp_folder);
        $result->execute();
        $result->store_result();
        $result->bind_result($folder_ownerID, $orig_folder_name, $team_name);
        $result->fetch();
        $result->close();

        if (isset($folder_teams) and $folder_teams != '' and $module == '') $module = $folder_teams;

        if (substr_count($orig_folder_name,';') > 0) {
            $last_semicolon = strrpos($orig_folder_name,';');
            $path = substr($orig_folder_name,0,$last_semicolon);
            $parent_results = $mysqli->prepare("SELECT id, name FROM folders WHERE name=? AND ownerID=? LIMIT 1");
            $parent_results->bind_param('si', $path, $userID);
            $parent_results->execute();
            $parent_results->bind_result($parent_id, $parent_name);
            $parent_results->fetch();
            $parent_results->close();
        }
        if ($folder != '') {
            $folders_array = explode(';',$orig_folder_name);
            $parts = count($folders_array) - 1;
            $selfenrol = 0;
        }
        if ($folder != '') {
            echo $folders_array[$parts];
        }
        if ($folder != '') {
            $query_string = "SELECT DISTINCT paper_ownerID, property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'$cfg_long_date_time') AS display_end_date, exam_duration, title, initials, surname, retired, moduleID FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND folder=\"$folder\" AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
        }

        $results = $mysqli->prepare($query_string);
        $results->execute();
        $results->bind_result($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $moduleID);
        $results->store_result();
        $old_p_type = '';
        $sent_clear_all = false;
             if ($results->num_rows > 0) {
                while ($results->fetch()) {
                    if ($old_p_type != $paper_type and (isset($_GET['module']) AND $_GET['module'] != '') ) {
                        if ($sent_clear_all == true) {
                            echo "<br clear=\"all\" />";
                        }
                        $sent_clear_all = true;

                        echo "<table border=\"0\" style=\"margin-left:10px; padding-right:2px; padding-bottom:5px; color:#1E3287\"><tr><td><nobr>" . $string[strtolower($types_array[$paper_type])] . " (" . $paper_types[$paper_type] . ")";
                        if ($paper_type == 2) {
                            echo "&nbsp;&nbsp;&nbsp;<span style=\"font-weight:normal\"><a href=\"../admin/calendar.php?module=" . $_GET['module'] . "#" . date("n") . "\"><img src=\"../artwork/shortcut_calendar_icon.png\" width=\"16\" height=\"14\" alt=\"Calendar\" border=\"0\" /></a>&nbsp;<a href=\"../admin/calendar.php?module=" . $_GET['module'] . "#" . date("n") . "\">" . $string['calendar'] . "</a></span>\n";
                        }
                        echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
                        echo "<br />\n";
                    }
                    displayPaperIcon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $moduleID);
                    $old_p_type = $paper_type;
                    $file_no++;
                }
                $results->close();
            }




*/


   }
    $resulta->close();



  echo "<table border=\"0\" style=\"padding-top:10px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['bymodulecode'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  $old_faculty = '';
  $old_letter = '';
  $module_block = false;
  $block_id = 0;
  $plk = 0;


  $teams = getUserTeams($userID, $mysqli);
  $modlist = SearchUtils::getTeams($teams, $userroles, $userID, $mysqli);

  foreach ($modlist as $value) {
    $moduleid = $value['id'];
    if ($moduleid !== '') {


      $query_string = "SELECT DISTINCT crypt_name, paper_type, 'f', paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'$cfg_long_date_time') AS display_end_date, title, initials, surname, retired, moduleID  FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND (moduleID = '" . $moduleid . "' OR moduleID LIKE '%," . $moduleid . ",%' OR moduleID LIKE '" . $moduleid . ",%' OR moduleID LIKE '%," . $moduleid . "') AND deleted IS NULL AND paper_type IN (0,1,3)  GROUP BY paper_title ORDER BY paper_type, paper_title";

      $results2 = $mysqli->prepare($query_string);

      $results2->execute();
      $results2->bind_result($crypt_name, $paper_type, $screens, $paper_title, $start_date, $start_date_disp, $end_date_display, $title, $initials, $surname, $retired, $moduleID);

      $results2->store_result();

      if ($results2->num_rows() > 0) {
        $rt = $results2->num_rows();
        echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\" onclick=\"showHide($block_id)\"  /><a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">&nbsp;$moduleid: $fullname ($rt)</a></div>\n";
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        while ($results2->fetch()) {
          echo "<div style=\"padding-left:52px\"><a href=\"?sss=" . $moduleID . "\"><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" /></a>&nbsp;<a class=\"recent\"";
          if (strpos($paper_title, '[deleted') !== false) {
            echo ' style="color:#808080"';
          }
          echo "href=\"?paperlinkID=" . $plk . "\">" . $paper_title . "</a></div>\n";
          // $paper_title ." [" . $start_date_disp . " - " . $end_date_display . "]</a></div>\n";
          $_SESSION['postlookup'][$plk] = $crypt_name;

          $plk++;
        }
        echo "</div>";
        $block_id++;
      }
      else {
        //        echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" />&nbsp;$moduleid: $fullname</div>\n";
      }
      $results2->close();
    }
  }
  $results->close();

  echo "</div>\n"; // -- End of 'content' div ------------------


  echo "</td></tr></table>";


  exit();
}
elseif ($returned[1] == 'paper') {
  header("location: ../user_index.php?id=" . $returned[0]);
}


exit();
