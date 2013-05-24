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

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/demo_replace.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/results_cache.class.php';

$userID = check_var('userID', 'GET', true, false, true);

if (!UserUtils::userid_exists($userID, $mysqli)) {   // Check for valid user ID.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function get_taken_papers($userID, $db) {
  $papers = array();

  $i = 0;
  $result = $db->prepare("SELECT DISTINCT paperID, paper_title, paper_type, pass_mark, calendar_year, started, crypt_name FROM log_metadata, properties WHERE log_metadata.paperID = properties.property_id AND paper_type IN ('2', '5') AND userID = ? ORDER BY calendar_year DESC");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($paperID, $paper_title, $paper_type, $pass_mark, $calendar_year, $started, $crypt_name);
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

    $i++;
  }
  $result->close();

  return $papers;
}

$papers = get_taken_papers($userID, $mysqli);

$results_cache = new ResultsCache($mysqli);
$marks = $results_cache->get_paper_marks_by_student($userID);

$icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

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
    h1 {font-size:120%; padding:0px; margin-top:15px; margin-bottom:0px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="javascript">
    var ie  = document.all
    var ns6 = document.getElementById&&!document.all
    var isMenu  = false ;
    var menuSelObj = null ;
    var overpopupmenu = false;
    function mouseSelect(e) {
      var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
      if (isMenu) {
        if (overpopupmenu == false) {
          isMenu = false ;
          overpopupmenu = false;
          document.getElementById('menudiv').style.display = 'none';
          return true ;
        }
        return true ;
      }
      return false;
    }
    // POP UP MENU
    function popMenu(paper_type, crypt_name, started, e) {
      if (!e) var e = window.event;
      var currentX = e.clientX;
      var currentY = e.clientY;
      var scrOfX = $('body,html').scrollLeft();
      var scrOfY = $('body,html').scrollTop();

      document.getElementById('paper_type').value = paper_type;
      document.getElementById('crypt_name').value = crypt_name;
      document.getElementById('started').value = started;

      top_pos = currentY+scrOfY;
      document.getElementById('menudiv').style.left = e.clientX+scrOfX + 'px';
      document.getElementById('menudiv').style.top = top_pos + 'px';

      document.getElementById('menudiv').style.display = "block";
      document.getElementById('item1b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item2b').style.backgroundColor = '#FFFFFF';
      isMenu = true;
      return false;
    }
    
    function menuRowOn(rowID) {
      // Left menu column
      document.getElementById('item'+rowID+'a').style.backgroundColor = '#FFE7A2';
      document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #FFBD69';
      
      // Right menu column
      document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFE7A2';
      document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFE7A2';
    }
    
    function menuRowOff(rowID) {
      // Left menu column
      document.getElementById('item'+rowID+'a').style.backgroundColor = '#F1F5FB';
      document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #F1F5FB';
      document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #F1F5FB';
      document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #F1F5FB';
      
      // Right menu column
      document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFFFFF';
    }    

    function viewScript() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = 750;
      var winheight = screen.height-80;
      window.open("../paper/finish.php?id=" + document.getElementById('crypt_name').value + "&userid=<?php echo $_GET['userID']; ?>&previous=" + document.getElementById('started').value + "&log_type=" + document.getElementById('paper_type').value + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewFeedback() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../mapping/user_feedback.php?id=" + document.getElementById('crypt_name').value + "&userID=<?php echo $_GET['userID']; ?>&started=" + document.getElementById('started').value + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }

    document.onmousedown = mouseSelect;
  </script>
</head>

<body>

<div id="menudiv" class="popupmenu" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table width="180" cellspacing="2" cellpadding="0" border="0" style="font-size:90%; background-color:white">
  <tr><td>
    <table width="180" cellspacing="0" cellpadding="1" border="0" style="font-size:100%; background-color:white">
      <tr>
        <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/osce_16.gif" width="16" height="16" alt="" border="0" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();">Exam Script</td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" border="0" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();">Objectives</td>
      </tr>
    </table>
  </td></tr>
</table>
</div>

<?php
$demo = is_demo($userObject);
$student_details = UserUtils::get_user_details($_GET['userID'], $mysqli);
$name = demo_replace($student_details['title'], $demo) . ' ' . demo_replace($student_details['surname'], $demo) . ', ' . demo_replace($student_details['first_names'], $demo);

echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:80%\">\n";
echo "<tr><th><div style=\"padding-left:10px; font-size:200%; font-weight:bold\">" . $string['performsummary'] . "</div><div style=\"padding-left:10px\">$name</div></th></tr>\n";
echo '<tr><th class="bevel"></th></tr>';
echo "</table>\n<div style=\"margin:10px\">";
  
$old_calendar_year = '';
$plots_output = 0;
$col = 0;

foreach ($papers as $paper) {
  if ($paper['stats']['max_mark'] != '') {
    if ($old_calendar_year != $paper['calendar_year']) {
      echo "<h1>" . $paper['calendar_year'] . "</h1>\n";
      echo '<img src="draw_boxplot.php?part=0" width="51" height="265" alt="' . $string['scale'] . '" />';
      $col = 0;
    }
  
    if ($col == 10) {
      echo '<br /><img src="draw_boxplot.php?part=0" width="51" height="265" alt="' . $string['scale'] . '" />';
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
  
    echo "<img src=\"draw_boxplot.php?exam=$exam&part=1&q1=$q1&q2=$q2&q3=$q3&min=$min&max=$max&passmark=$pass_mark&mark=$mark\" width=\"115\" height=\"265\" onclick=\"popMenu(" . $paper['paper_type'] . ", '" . $paper['crypt_name'] . "', '" . $paper['started'] . "', event)\" alt=\"" . $string['boxplot'] . "\" />";

    $plots_output++;
    $col++;
    $old_calendar_year = $paper['calendar_year'];
  }
}
if ($plots_output == 0) {
  echo "<div style=\"margin:10px\">" . $string['noresults'] . "</div>\n";
}
?>
<input type="hidden" id="crypt_name" />
<input type="hidden" id="started" />
<input type="hidden" id="paper_type" />

</div>
</body>
</html>
