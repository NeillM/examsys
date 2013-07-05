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
* Displays an overview of summative and offline reports for a student
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/errors.inc';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/results_cache.class.php';

if (isset($_GET['userID'])) {
  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
    if ($_GET['userID'] != '') {
      $userID = $_GET['userID'];
    } else {
      display_error($string['idmissing'], $string['idmissing_msg'], false, true, false);
    }
  } else {  // Student is trying to hack into another students userID on the URL.
    header("HTTP/1.0 404 Not Found");
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
} else {
  $userID = $userObject->get_user_ID();
}

if (!UserUtils::userid_exists($userID, $mysqli)) {   // Check for valid user ID.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function get_taken_papers($userID, $db) {
  $papers = array();

  $i = 0;
  
  // Query for Summative and Offline papers
  $result = $db->prepare("SELECT DISTINCT paperID, paper_title, paper_type, pass_mark, calendar_year, started, crypt_name, idfeedback_release FROM log_metadata, properties LEFT JOIN feedback_release ON properties.property_id = feedback_release.paper_id WHERE log_metadata.paperID = properties.property_id AND paper_type IN ('2', '5') AND userID = ? ORDER BY calendar_year DESC");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($paperID, $paper_title, $paper_type, $pass_mark, $calendar_year, $started, $crypt_name, $idfeedback_release);
  while ($result->fetch()) {
    $papers[$i]['paperID']        = $paperID;
    $papers[$i]['paper_title']    = $paper_title;
    $papers[$i]['paper_type']     = $paper_type;
    $papers[$i]['calendar_year']  = $calendar_year;
    $papers[$i]['started']        = $started;
    $papers[$i]['crypt_name']     = $crypt_name;
    $papers[$i]['pass_mark']      = $pass_mark;
    $results_cache = new ResultsCache($db);
    $papers[$i]['stats']          = $results_cache->get_paper_cache($paperID);
    $papers[$i]['idfeedback_release'] = $idfeedback_release;

    $i++;
  }
  $result->close();
  
  // Query for OSCE stations
  $result = $db->prepare("SELECT DISTINCT q_paper, paper_title, paper_type, pass_mark, calendar_year, started, crypt_name, idfeedback_release FROM log4_overall, properties LEFT JOIN feedback_release ON properties.property_id = feedback_release.paper_id WHERE log4_overall.q_paper = properties.property_id AND paper_type IN ('4') AND userID = ? ORDER BY calendar_year DESC");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($paperID, $paper_title, $paper_type, $pass_mark, $calendar_year, $started, $crypt_name, $idfeedback_release);
  while ($result->fetch()) {
    $papers[$i]['paperID']        = $paperID;
    $papers[$i]['paper_title']    = $paper_title;
    $papers[$i]['paper_type']     = $paper_type;
    $papers[$i]['calendar_year']  = $calendar_year;
    $papers[$i]['started']        = $started;
    $papers[$i]['crypt_name']     = $crypt_name;
    $papers[$i]['pass_mark']      = $pass_mark;
    $results_cache = new ResultsCache($db);
    $papers[$i]['stats']          = $results_cache->get_paper_cache($paperID);
    $papers[$i]['idfeedback_release'] = $idfeedback_release;

    $i++;
  }
  $result->close();
  
  $sortby = 'calendar_year';
  $ordering = 'desc';
  $papers = array_csort($papers, $sortby, $ordering);

  return $papers;
}

$papers = get_taken_papers($userID, $mysqli);

$results_cache = new ResultsCache($mysqli);
$marks = $results_cache->get_paper_marks_by_student($userID);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['performsummary']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <style>
    body {font-size:90%}
    .subsect {margin-top:20px; font-size:80%}
    .indent {margin-left:30px}
    .label {position:relative; left:171px; padding:0; margin:0; width:132px; height:11px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/popup_menu.js"></script>
<?php
if (!$userObject->has_role('Student')) {  // Do not show JavaScript if a student
?>
  <script language="javascript">
    function setVars (paper_type, crypt_name, paperID, started) {
      document.getElementById('paper_type').value = paper_type;
      document.getElementById('crypt_name').value = crypt_name;
      document.getElementById('paperID').value = paperID;
      document.getElementById('started').value = started;
    }

    function viewScript() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = 750;
      var winheight = screen.height-80;
      window.open("../paper/finish.php?id=" + document.getElementById('crypt_name').value + "&userid=<?php echo $userID; ?>&previous=" + document.getElementById('started').value + "&log_type=" + document.getElementById('paper_type').value + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewFeedback() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../mapping/user_feedback.php?id=" + document.getElementById('crypt_name').value + "&userID=<?php echo $userID; ?>&started=" + document.getElementById('started').value + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewPersonalCohort() {
      window.location.href="../reports/personal_cohort_performance.php?paperID=" + document.getElementById('paperID').value + "&userID=<?php echo $userID; ?>";
    }
    
    function jumpToPaper() {
      window.opener.location.href="../paper/details.php?paperID=" + document.getElementById('paperID').value;
      self.close();
    }

    document.onmousedown = mouseSelect;
  </script>
<?php
}
?>
</head>

<body>

<div style="position:relative; width:300px; height:173px; border: 1px solid #808080; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; box-shadow:3px 3px 3px rgba(100, 100, 100, 0.50); z-index:10; float:right; top:10px; right:10px; font-size:75%; padding:5px; line-height:100%; background-color:white; color:#404040">
<img src="../artwork/boxplot_key.png" width="170" height="173" alt="Key" />
<div style="top:-175px" class="label"><?php echo $string['maximumscore']; ?></div>
<div style="top:-163px" class="label"><?php echo $string['studentsposition']; ?></div>
<div style="top:-154px" class="label"><?php echo $string['topquartile']; ?></div>
<div style="top:-145px" class="label"><?php echo $string['median']; ?></div>
<div style="top:-138px" class="label"><?php echo $string['lowerquartile']; ?></div>
<div style="top:-128px" class="label"><?php echo $string['passmark']; ?></div>
<div style="top:-118px" class="label"><?php echo $string['minimumscore']; ?></div>
<div style="top:-111px" class="label"><?php echo $string['examname']; ?></div>
<div style="top:-105px" class="label"><?php echo $string['studentsmark']; ?></div>
</div>

<?php
if (!$userObject->has_role('Student')) {  // Do not create popup menu if student
?>
<div id="menudiv" class="popupmenu" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table width="250" cellspacing="2" cellpadding="0" border="0" style="font-size:90%; background-color:white">
  <tr><td>
    <table width="250" cellspacing="0" cellpadding="1" border="0" style="font-size:100%; background-color:white">
      <tr>
        <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/osce_16.gif" width="16" height="16" alt="" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><?php echo $string['examscript']; ?></td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><?php echo $string['objectives']; ?></td>
      </tr>
      <tr>
        <td id="item3a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/personal_cohort.gif" width="16" height="16" alt="" /></td><td id="item3b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewPersonalCohort();"><?php echo $string['personalcohortperformance']; ?></td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right"><img src="../artwork/popup_divider.png" width="100%" height="3" alt="-" /></td>
      </tr>
      <tr>
        <td id="item4a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px">&nbsp;</td><td id="item4b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="jumpToPaper();"><?php echo $string['jumptopaper']; ?></td>
      </tr>
    </table>
  </td></tr>
</table>
</div>
<?php
}


$demo = is_demo($userObject);
$student_details = UserUtils::get_user_details($userID, $mysqli);
$name = demo_replace($student_details['title'], $demo) . ' ' . demo_replace($student_details['surname'], $demo) . ', ' . demo_replace($student_details['first_names'], $demo) . ' (' . demo_replace($student_details['student_id'], $demo) . ')';
?>


<div style="position:absolute; top:0px; left:0px; width:100%">
<?php
echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:90%\">\n";
echo "<tr><th><div style=\"padding-left:10px; font-size:200%; font-weight:bold\">" . $string['performsummary'] . "</div><div style=\"padding-left:10px\">$name</div></th></tr>\n";
echo '<tr><th class="bevel"></th></tr>';
echo "</table>\n<div>";

$old_calendar_year = '';
$plots_output = 0;
$col = 0;

foreach ($papers as $paper) {
  $display_paper = true;
  
  if ($paper['stats']['max_mark'] == '') {
    $display_paper = false;
  }
  if ($userObject->has_role('Student') and $paper['idfeedback_release'] == '') {
    $display_paper = false;
  }

  if ($display_paper) {
    if ($old_calendar_year != $paper['calendar_year']) {
      echo '<a name="' . $paper['calendar_year'] . '"></a><table border="0" class="subsect"><tr><td><nobr>' . $paper['calendar_year'] . '</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table>';
      $col = 0;
    }
  
    if ($col == 8) {
      echo '<br />';
      $col = 0;
    }
  
    $q1 = $paper['stats']['q1'];
    $q2 = $paper['stats']['q2'];
    $q3 = $paper['stats']['q3'];
    $min = $paper['stats']['min_percent'];
    $max = $paper['stats']['max_percent'];
    $pass_mark = $paper['pass_mark'];
    $mark = (isset($marks[$paper['paperID']])) ? $marks[$paper['paperID']] : '';
    $exam = $paper['paper_title'];
  
    if ($userObject->has_role('Student')) {
      $onclick = '';
    } else {
      $onclick = " onclick=\"popMenu(3, event); setVars(" . $paper['paper_type'] . ", '" . $paper['crypt_name'] . "', " . $paper['paperID'] . ", '" . $paper['started'] . "')\"";
    }
    
    if ($mark != '') {  // Do not plot if there is no student mark.
      if ($col == 0) {
        echo "<img src=\"draw_boxplot.php?exam=$exam&part=1&q1=$q1&q2=$q2&q3=$q3&min=$min&max=$max&passmark=$pass_mark&mark=$mark&scale=1\" width=\"166\" height=\"265\"$onclick alt=\"" . $string['boxplot'] . "\" class=\"indent\" />";
      } else {
        echo "<img src=\"draw_boxplot.php?exam=$exam&part=1&q1=$q1&q2=$q2&q3=$q3&min=$min&max=$max&passmark=$pass_mark&mark=$mark&scale=0\" width=\"115\" height=\"265\"$onclick alt=\"" . $string['boxplot'] . "\" />";
      }
      
      $plots_output++;
      $col++;
    }
    $old_calendar_year = $paper['calendar_year'];
  }
}
if ($plots_output == 0) {
  echo "<div style=\"margin:10px\">" . $string['noresults'] . "</div>\n";
}

if (!$userObject->has_role('Student')) {  // Do not show hidden fields if a student
?>
<input type="hidden" id="crypt_name" />
<input type="hidden" id="paperID" />
<input type="hidden" id="started" />
<input type="hidden" id="paper_type" />
<?php
}
?>
</div>

</div>
</body>
</html>
