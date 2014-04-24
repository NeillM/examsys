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
* Class Totals report.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

set_time_limit(0);

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/class_totals.class.php';
require_once '../classes/folderutils.class.php';
require_once '../classes/exam_announcements.class.php';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper            = $propertyObj->get_paper_title();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli);
if (isset($_GET['recache']) and $_GET['recache'] == '1') {
  $report->compile_report(true);  // Force a re-cache
} else {
  $report->compile_report(false);
}

$user_results = $report->get_user_results();
$paper_buffer = $report->get_paper_buffer();
$cohort_size  = $report->get_cohort_size();
$stats        = $report->get_stats();
$ss_pass      = $report->get_ss_pass();
$ss_hon       = $report->get_ss_hon();
$question_no  = $report->get_question_no();
$log_late     = $report->get_log_late();
$user_no      = $report->get_user_no();

function check_late_submission_warnings($log_late, $string) {
  if (count($log_late) > 0) {
  ?>
    <table border="0" cellpadding="0" cellspacing="0" style="font-size:80%; width:100%">
      <tr>
        <td class="redwarn" style="width:40px"><img src="../artwork/late_warning_icon.png" width="32" height="32" alt="<?php echo strip_tags($string['latesubmissionsmsg']) ?>" /></td>
        <td class="redwarn"><?php echo sprintf($string['latesubmissionsmsg'],  count($log_late)) . ' (<a style="color:black" href="#" onclick="launchHelp(221); return false;">' . $string['moredetails'] . '</a>)'; ?></td>
      </tr>
    </table>
  <?php
  }
}

function check_unmarked_textbox_warnings($report, $string) {
  if ($report->unmarked_textbox()) {
  ?>
    <table border="0" cellpadding="0" cellspacing="0" style="font-size:80%; width:100%">
      <tr>
        <td class="redwarn" style="width:40px"><img src="../artwork/unmarked_questions_warning.png" width="32" height="32" alt="Warning" /></td>
        <td class="redwarn"><?php echo $string['unmarkedtextbox'] ?></td>
      </tr>
    </table>
  <?php
  }
}

function check_unmarked_enhancedcalc_warnings($report, $string) {
  if ($report->unmarked_enhancedcalc()) {
  ?>
    <table border="0" cellpadding="0" cellspacing="0" style="font-size:90%; width:100%">
      <tr>
        <td class="redwarn" style="width:40px"><img src="../artwork/unmarked_questions_warning.png" width="32" height="32" alt="Warning" /></td>
        <td class="redwarn"><?php echo $string['unmarkedenhancedcalc'] ?></td>
      </tr>
    </table>
  <?php
  }
}

function check_temp_account_warnings($user_results, $string) {
  // Check for any temporary accounts and if so display warning banner
  $temp_user_no = 0;
  $user_no = count($user_results);
  for ($i=0; $i<$user_no; $i++) {
    if (strpos($user_results[$i]['username'], 'user') === 0) {
      $temp_user_no++;
    }
  }
  if ($temp_user_no > 0) {
  ?>
    <table border="0" cellpadding="0" cellspacing="0" style="font-size:90%; width:100%">
      <tr>
        <td class="redwarn" style="width:40px"><img src="../artwork/temp_account_warning.png" width="32" height="32" alt="Warning" /></td>
        <td class="redwarn"><?php echo $string['temporaryaccountswarning'] ?></td>
      </tr>
    </table>
  <?php
  }
}

if (($paper_type == '2' and $propertyObj->unmarked_enhancedcalc() and !$propertyObj->is_active()) or ($paper_type == '1' and $report->unmarked_enhancedcalc())) {
// Only mark calculation questions when the exam is not active.
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />

<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/toprightmenu.js"></script>
<script language="JavaScript">
	$(document).ready(function() {
		// Fire off the request to mark_all_enhancedcalc.php
   var request = $.ajax({
      url: "../ajax/reports/mark_all_enhancedcalc.php",
      type: "get",
      data: {paperID: <?php echo $paperID; ?>},
			timeout: 30000, // timeout after 30 seconds
			dataType: "html",
			success: function (data, textStatus, jqXHR) {
				data = data.replace(/(\r\n|\n|\r)/gm,"");
			  if (data == 'Complete') {
				  window.location.reload();
				} else {
					$("#msg").html(data);
				}
			},
			error: function (xhr, textStatus, errorThrown) {
				$("#msg").html('Error: ' + textStatus);
			},
			fail: function (jqXHR, textStatus) {
				$("#msg").html('Failed: ' + textStatus);
			},
    });
	});
</script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu(30);

  echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:80%\">\n";
  echo "<tr><th class=\"h\">";

  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';

  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '' ) {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

  echo "<span style=\"margin-left:10px; font-size:200%; color:black\"><strong>" . $string['classtotals'] . "</strong> - " . $string['markingcalcquestions'] . "</span></th><th class=\"h\" style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

  echo '</table>';
	
	echo "<div class=\"marking\"><img src=\"../artwork/large_spin.gif\" widht=\"32\" height=\"32\" style=\"float:left; padding-right:10px\" />\n";
	echo "<div id=\"msg\">" . $string['marking'] . "</div>\n";
	echo "</div>\n";
?>
</body>
</html>
<?php
exit();
}

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
<link rel="stylesheet" type="text/css" href="../css/list.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />

<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="../js/staff_help.js"></script>
<script type="text/javascript" src="../js/popup_menu.js"></script>
<script type="text/javascript" src="../js/toprightmenu.js"></script>
<script language="JavaScript">
  function setVars(tmpMetadataID, tmpUserID, tmpLogType, tmpReassign, tmpLogLate, tmpPercent, e) {
    $('#metadataID').val(tmpMetadataID);
    $('#userID').val(tmpUserID);
    $('#log_type').val(tmpLogType);
    $('#reassign').val(tmpReassign);
    $('#loglate').val(tmpLogLate);
    $('#percent').val(tmpPercent);

    if (tmpMetadataID == '') {
      $('#item1b').css('color', '#C0C0C0');
      $('#item2b').css('color', '#C0C0C0');
    } else {
      $('#item1b').css('color', '#000000');
      $('#item2b').css('color', '#000000');
    }

    if (tmpReassign == 'y') {
      $('#item3b').css('color', '#C0C0C0');
      $('#item5b').css('color', '#000000');
    } else {
      $('#item3b').css('color', '#000000');
      $('#item5b').css('color', '#C0C0C0');
    }

    if (tmpLogLate == 'y') {
      $('#item7b').css('color', '#000000');
      $('#log_late_icon').show();
    } else {
      $('#item7b').css('color', '#C0C0C0');
      $('#log_late_icon').hide();
    }
  }

  function confirmSubmit() {
    var agree = confirm("Are you sure you want to email everyone on this list their marks?");
    if (agree)
      return true;
    else
      return false;
  }

  function popupEmailTemplate() {
    var winwidth = 785;
    var winheight = 550;
    templatewin = window.open("emailtemplate.php","templatewin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    templatewin.moveTo(screen.width/2-350,screen.height/2-275);
  }

  function viewScript() {
    $('#menudiv').hide();
    if ($('#metadataID').val() != '') {
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../paper/finish.php?id=<?php echo $propertyObj->get_crypt_name(); ?>&userID=" + $('#userID').val() + "&metadataID=" + $('#metadataID').val() + "&log_type=" + $('#log_type').val() + "&percent=" + $('#percent').val() + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }

  function viewFeedback() {
    $('#menudiv').hide();
    if ($('#metadataID').val() != '') {
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../students/objectives_feedback.php?id=<?php echo $propertyObj->get_crypt_name(); ?>&userID=" + $('#userID').val() + "&metadataID=" + $('#metadataID').val() + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }

  function viewProfile() {
    $('#menudiv').hide();
    if ($('#reassign').val() == 'n') {
      window.top.location = '../users/details.php?paperID=<?php echo $paperID; ?>&userID=' + $('#userID').val();
    }
  }

  function newStudentNote() {
    $('#menudiv').hide();
    note = window.open("../users/new_student_note.php?userID=" + $('#userID').val() + "&paperID=<?php echo $paperID; ?>&calling=class_totals","note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      note.focus();
    }
  }

  function reassignScript() {
    $('#menudiv').hide();
    if ($('#reassign').val() == 'y') {
      reassign = window.open("check_reassign_script.php?userID=" + $('#userID').val() + "&paperID=<?php echo $paperID; ?>","reassign","width=600,height=500,left="+(screen.width/2-300)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        reassign.focus();
      }
    }
  }

<?php
  if ($paper_type == '0' or $paper_type == '1') {   // Do not allow reset of timer for Summative exams.
?>
  function resetTimer() {
    $('#menudiv').hide();
    reassign = window.open("check_reset_timer.php?userID=" + $('#userID').val() + "&paperID=<?php echo $paperID; ?>&metadataID=" + $('#metadataID').val() + "","reassign","width=550,height=200,left="+(screen.width/2-275)+",top="+(screen.height/2-100)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      reassign.focus();
    }
  }
<?php
  }
?>

  function reassignLogLate() {
    $('#menudiv').hide();
    if ($('#loglate').val() == 'y') {
      loglate = window.open("check_reassign_log_late.php?userID=" + $('#userID').val() + "&paperID=<?php echo $paperID; ?>&metadataID=" + $('#metadataID').val() + "&log_type=" + $('#log_type').val() + "","reassign","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        reassign.focus();
      }
    }
  }

  function viewNote(userID, e) {
    if (!e) var e = window.event;
	  var currentX = e.clientX;
	  var currentY = e.clientY;
    var scrOfX = $(document).scrollLeft();
    var scrOfY = $(document).scrollTop();

    dataSource = "getNote.php?paperID=<?php echo $paperID; ?>&userID=" + userID;

    $("#noteMsg").load(dataSource, function(responseTxt, statusTxt, xhr) {
      if (statusTxt == "success") {
        $("#noteDiv").show();
        $("#noteDiv").css('left', currentX + scrOfX + 16 + 'px');

        top_pos = currentY+scrOfY-16;
        if (top_pos > ($(window).height() + scrOfY - 130)) {
          top_pos = $(window).height() + scrOfY - 130;
        }
        $("#noteDiv").css('top', top_pos + 'px');
      }
    });
  }
	
	$(document).ready(function() {
    $("#maindata").tablesorter({ 
      // sort on the first column and third column, order asc 
      sortList: [[2,0],[3,0]] 
    });
    
    $(document).click(function() {
      $('#menudiv').hide();
		});
	});
</script>
</head>


<body>
<div id="noteDiv" class="studentnote">
<div style="text-align:right"><img onclick="$('#noteDiv').hide();" src="../artwork/close_note.png" style="border-left:1px solid #E6B10D; border-bottom:1px solid #E6B10D; cursor:pointer" width="26" height="14" alt="Close" /></div>
<div id="noteMsg"></div>
</div>

<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu(30);

$popup_width = 180;
if ($language != 'en') {		// Make wider for non-English languages which have longer words
  $popup_width = 300;
}
?>
<div id="menudiv" class="popupmenu" style="width:<?php echo $popup_width; ?>px" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table cellspacing="2" cellpadding="0" border="0" style="font-size:100%; width:100%">
  <tr><td>
    <table cellspacing="0" cellpadding="1" border="0" style="width:100%">
      <tr>
        <td id="item1a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" /></td><td id="item1b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><?php echo $string['examscript']; ?></td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" /></td><td id="item2b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><?php echo $string['feedback']; ?></td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right"><img src="../artwork/popup_divider.png" width="100%" height="3" alt="-" /></td>
      </tr>
      <tr>
        <td id="item3a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();">
          <img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" />
          </td>
          <td id="item3b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();">
          <?php echo $string['studentprofile']; ?>
          </td>
      </tr>
      <tr>
        <td id="item4a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="newStudentNote();">
        <img src="../artwork/notes_icon.gif" width="14" height="14" alt="" />
        </td>
        <td id="item4b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('4');" onmouseout="menuRowOff('4');" onclick="newStudentNote();">
        <?php echo $string['newnote']; ?>
        </td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"></td><td style="padding-left:8px; text-align:right">
        <img src="../artwork/popup_divider.png" width="100%" height="3" alt="-" /></td>
      </tr>
      <tr>
        <td id="item5a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('5');" onmouseout="menuRowOff('5');" onclick="reassignScript();">
        <img src="../artwork/guest_account_16.png" width="16" height="16" alt="" />
        </td>
        <td id="item5b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('5');" onmouseout="menuRowOff('5');" onclick="reassignScript();">
        <?php echo $string['reassigntouser']; ?></td>
      </tr>
      <tr>
      <?php
        if ($paper_type == '1') {   // Do not allow reset of timer for Summative exams.
          $action = 'resetTimer();';
          $text_color = 'black';
        } else {
          $action = '$(\'#menudiv\').hide()';
          $text_color = '#C0C0C0';
        }
      ?>
        <td id="item6a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('6');" onmouseout="menuRowOff('6');" onclick="<?php echo $action; ?>">
        </td>
        <td id="item6b" style="padding-left:8px; background-color:#FFFFFF; color:<?php echo $text_color; ?>; cursor:default" onmouseover="menuRowOn('6');" onmouseout="menuRowOff('6');" onclick="<?php echo $action; ?>">
        <?php echo $string['resettimer']; ?></td>
      </tr>
      <tr>
        <td id="item7a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('7');" onmouseout="menuRowOff('7');" onclick="reassignLogLate();">
        <img id="log_late_icon" style="display:none" src="../artwork/log_late_16.gif" width="16" height="16" alt="" /></td>
        <td id="item7b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('7');" onmouseout="menuRowOff('7');" onclick="reassignLogLate();">
        <?php echo $string['latesubmissions']; ?>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</div>
<?php
  for ($i=-100; $i<=100; $i++) $distribution[$i] = 0;

  $notes = array();
  // Query any student notes for the current paper
  $result = $mysqli->prepare("SELECT userID FROM student_notes WHERE paper_id = ?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($userID);
  while ($result->fetch()) {
    $notes[$userID] = 'y';
  }
  $result->close();


  // Query any student special needs for the current paper
  $special_needs = array();
  $users_in = array();
  foreach($user_results as $u) {
    $users_in[] = $u['userID'];
  }
  $users_in = implode(',',$users_in);
  if ($users_in != '') {
    $result = $mysqli->prepare("SELECT userID FROM special_needs where userID IN ($users_in)");
    $result->execute();
    $result->bind_result($special_userID);
    while ($result->fetch()) {
      $special_needs[$special_userID] = 'y';
    }
    $result->close();
  }

  if ($marking == '0') {
    $marking_label = $string['%'];
    $marking_key = 'percent';
  } else {
    $marking_label = $string['adjusted%'];
    $marking_key = 'adj_percent';
  }

  //output table heading
	if ($configObject->get('cfg_client_lookup') == 'name') {
		$table_order = array(''=>16, 'Title'=>45, $string['surname']=>170, $string['firstnames']=>270, $string['studentid']=>80, $string['course']=>55, $string['mark']=>50, $marking_label=>80, $string['classification']=>80, $string['rank']=>50, $string['decile']=>50, $string['starttime']=>170, $string['duration']=>70, $string['hostnames']=>100);
	} else {
		$table_order = array(''=>16, 'Title'=>45, $string['surname']=>170, $string['firstnames']=>270, $string['studentid']=>80, $string['course']=>55, $string['mark']=>50, $marking_label=>80, $string['classification']=>80, $string['rank']=>50, $string['decile']=>50, $string['starttime']=>170, $string['duration']=>70, $string['ipaddress']=>100);
  }
	if ($paper_type == '2') $table_order[$string['room']] = 200;
  $metadata_cols = array();
  if (isset($user_results[0])){
    foreach ($user_results[0] as $key => $val) {
      if (strrpos($key, 'meta_') !== false) {
        $key_display = ucfirst(str_replace('meta_','',$key));
        $table_order[$key_display] = 150;
        $metadata_cols[$key] = $key;
      }
    }
  }

  $cols = count($table_order);
  
  echo "<div style=\"font-size:80%\">\n";
  echo "<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></div>\n";
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';

  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif ( isset( $_GET['module'] ) and $_GET['module'] != '' ) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

  $report_title = $string['classtotals'];
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title .= ' <span style="font-weight: normal">(' . module_utils::get_moduleid_from_id($_GET['repmodule'], $mysqli) . ' ' . $string['studentsonly'] . ')</span>';
  } elseif (isset($_GET['percent']) and $_GET['percent'] < 100) {
    if ($ordering == 'desc') {
      $report_title .= ' <span style="font-weight: normal">(' . $string['top'] . ' ' . $_GET['percent'] . '%)</span>';
    } else {
      $report_title .= ' <span style="font-weight: normal">(' . $string['bottom'] . ' ' . $_GET['percent'] . '%)</span>';
    }
  }

  echo "<div class=\"page_title\">$report_title</div>";
  echo "</div>\n";
  
  // Warning display banners
  check_late_submission_warnings($log_late, $string);
  check_unmarked_textbox_warnings($report, $string);
  check_unmarked_enhancedcalc_warnings($report, $string);
  check_temp_account_warnings($user_results, $string);

  // Output table header
  echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:110%; width:100%\">\n";
  echo "<thead>\n";
  if (isset($user_results[0])) {
    echo "<tr>\n";
    foreach ($table_order as $display => $col_width) {
      echo "<th style=\"width:" . $col_width . "px\" class=\"vert_div\">$display</th>\n";
    }
    echo "</tr>\n";
  }
  echo "</thead>\n";
  
  if ($sortby == 'classification') {
    $sortby = 'mark';
  }

  $percent_decimals = $configObject->get('percent_decimals');
  $absent_no = 0;
  $scatter_data = '';

  echo "<tbody>\n";

  for ($i=0; $i<$user_no; $i++) {
    extract($user_results[$i]);

    if ($user_results[$i]['visible'] == 1) {
      if (strpos($user_results[$i]['username'], 'user') !== 0) {
        $reassign = 'n';
      } else {
        $reassign = 'y';
      }

      if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
        $bg_color = '#FFC0C0';
        $late_submissions = '';
        ?>
        <tr class="nonattend" id="res<?php echo $i+1 ?>" onclick="popMenu(6, event); setVars('', '<?php echo $userID; ?>', '<?php echo $paper_type; ?>', '<?php echo $reassign ?>', '<?php echo $late_submissions ?>', '<?php echo $percent; ?>');"><td>&nbsp;</td>
        <?php
        echo "<td class=\"padl\">$title</td>";
        echo "<td class=\"padl\">$surname</td>";
        echo "<td class=\"padl\">$first_names</td>";
        
        
        if ($user_results[$i]['student_id'] == '') {
          echo "<td class=\"padl grey\">" . $string['unknown'] . "</td>";
        } else {
          echo "<td class=\"padl\">" . $user_results[$i]['student_id'] . "</td>";
        }
        echo "<td class=\"padl\">" . $user_results[$i]['student_grade'] . "</td><td colspan=\"" . (9 + count($metadata_cols)) . "\" style=\"text-align:center\">&lt;" . $string['noattendance'] . "&gt;</td></tr>\n";
        $absent_no++;
      } else {
        if (isset($log_late[$user_results[$i]['metadataID']])) {
          $late_submissions = 'y';
        } else {
          $late_submissions = 'n';
        }
        echo '<tr id="res' . ($i+1) . '"';
        if ($user_results[$i]['questions'] < $question_no) {
          $scatter_data .= "0\n0\n";
          $class = 'redln';
        } else {
          $class = 'greyln';
          $temp_location = round($user_results[$i]['percent']);
          if (isset($distribution[$temp_location])) {
						$distribution[$temp_location]++;
          } else {
						$distribution[$temp_location] = 1;
					}
					$scatter_data .= $temp_location . "\n" . $user_results[$i]['duration'] . "\n";
        }
        if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
          $role_css = 'staff';
        } else {
          $role_css = '';
        }
        if (isset($log_late[$user_results[$i]['metadataID']])) {
          $icon = 'log_late_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['questions'] < $question_no) {
          $icon = 'incomplete_paper_icon.gif';
          $alt = $string['notcompleted'];
        } elseif ($user_results[$i]['paper_type'] == '0') {
          $icon = 'formative_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['paper_type'] == '1') {
          $icon = 'progress_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['paper_type'] == '2') {
          $icon = 'summative_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['paper_type'] == '3') {
          $icon = 'survey_16.gif';
          $alt = $string['displaysurvey'];
        } elseif ($user_results[$i]['paper_type'] == '5') {
          $icon = 'offline_16.gif';
          $alt = $string['displaypaper'];
        }
        echo " style=\"cursor:hand\" onclick=\"popMenu(5, event); setVars('" . $user_results[$i]['metadataID'] . "'," . $user_results[$i]['userID'] . ",'" . $user_results[$i]['paper_type'] . "','$reassign','$late_submissions','" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "');" . "\"";
        echo "><td class=\"$class $role_css\"><img src=\"../artwork/$icon\" class=\"picon\" /></td>";
        
        if (strpos($user_results[$i]['username'], 'user') === 0) {
          echo "<td class=\"$class padl tmpacc $role_css\">Mr</td>";
          echo "<td class=\"$class padl tmpacc $role_css\">Guest</td>";
          echo "<td class=\"$class padl tmpacc $role_css\">" . str_replace('User','Account #',$user_results[$i]['surname']);
        } else {
          echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['title'] . "</td>";
          echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['surname'] . "</td>";
          echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['first_names'];
        }
        if (isset($special_needs[$user_results[$i]['userID']]) and $special_needs[$user_results[$i]['userID']] == 'y') {
          echo '&nbsp;<img src="../artwork/accessibility_16.png" width="16" height="16" alt="' . $string['alternativearrangements'] . '" />';
        }
        $student_id = $user_results[$i]['username'];
        if ($user_results[$i]['attempt'] > 1) {
          echo '&nbsp;<img src="../artwork/resit.png" width="16" height="16" alt="Resit" />';
        }
        if (isset($notes[$user_results[$i]['userID']]) and $notes[$user_results[$i]['userID']] == 'y') {
          echo '&nbsp;<a href="" onclick="viewNote(\'' . $user_results[$i]['userID'] . '\', event); return false;"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Notes" /></a>';
        }
        echo "</td>";
        
        if ($user_results[$i]['student_id'] == '') {
          if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
            echo "<td class=\"grey $class padl $role_css\">&nbsp;</td>";
          } else {
            echo "<td class=\"grey $class padl $role_css\">" . $string['unknown'] . "</td>";
          }
        } else {
          echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['student_id'] . "</td>";
        }
        echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['student_grade'] . "</td>";
       			
				//$user_results[$i]['mark'] += 1;   // Use for testing the Class Totals/Exam Script checking script.
				
        if (round($user_results[$i]['percent'], $percent_decimals) < $pass_mark) {
          echo "<td class=\"mk $class fail r $role_css\">";
          if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
          echo $user_results[$i]['mark'] . "</td>";
          echo "<td class=\"$class fail r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class fail $role_css\">&nbsp;" . $string['fail'] . "</td>";
        } else {
          if (round($user_results[$i]['percent'], $percent_decimals) >= $distinction_mark) {
            echo "<td class=\"mk $class dist r $role_css\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td class=\"dist $class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class dist $role_css\">&nbsp;" . $string['distinction'] . "</td>";
          } else {
            echo "<td class=\"mk $class r $role_css\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td class=\"$class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class $role_css\">&nbsp;" . $string['pass'] . "</td>";
          }
        }
        // Rank column
        echo "<td class=\"$class r $role_css\">" . $user_results[$i]['rank'] . "</td>";
        // Decile column
        echo "<td class=\"$class r $role_css\">" . $user_results[$i]['decile'] . "</td>";
        // Start Time column
        echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['display_started'] . "</td>";
        // Duration column
        echo "<td class=\"$class padl $role_css\">" . $report->formatsec($user_results[$i]['duration']);
        if ($late_submissions == 'y') {
          echo '&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" />';
        }
        echo "</td>";

        echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['ipaddress'] . "</td>";
        if ($paper_type == 2) {
          echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['room'] . "</td>";
        }

        // Display any associated metadata
        if (count($metadata_cols) > 0) {
          foreach ( $metadata_cols as $type) {
            if (isset($user_results[$i][$type])) {
              echo "<td class=\"$class $role_css\">&nbsp;" . $user_results[$i][$type] . "</td>";
            } else {
              echo "<td class=\"$class $role_css\">&nbsp;</td>";
            }
          }
        }
        echo "</tr>\n";
      }
    }
  }
  echo "<tbody>\n</table>\n";
  
  // Summary information after the cohort listing.
  // ------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  $scatter_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID(). '_scatter.dat', 'w');              // Scatter plot data
  fwrite($scatter_file, $scatter_data . "\n");
  fclose($scatter_file);

  $distribution_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_distribution.dat', 'w');   // Distribution data
  fwrite($distribution_file, serialize($distribution) . "\n");
  fclose($distribution_file);
	
  if ($user_no > 0) {
    //Check for any paper notes
    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['papernotes'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    $result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'" . $configObject->get('cfg_long_date_time') . "'), note_workstation FROM paper_notes WHERE paper_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($note, $note_date, $note_workstation);
    while ($result->fetch()) {
      $lab_name = '';
      $result2 = $mysqli->prepare("SELECT name FROM labs, client_identifiers WHERE labs.id = client_identifiers.lab AND address = ?");
      $result2->bind_param('s', $note_workstation);
      $result2->execute();
      $result2->bind_result($lab_name);
      $result2->fetch();
      $result2->close();
      echo "<div class=\"papernote\"><strong>$note_date</strong><p>$note</p><br /><span style=\"font-size:80%\">$note_workstation";
      if ($lab_name != '') echo " ($lab_name)";
      echo "</span></div>\n";
    }
    echo "<br clear=\"all\" />";
    $result->close();

    $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
    $exam_announcements = $exam_announcementObj->get_announcements();
    echo "<br />\n<table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['midexamclarifications'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo "<tr><td colspan=\"" . $cols . "\" height=\"9\"><table cellspacing=\"0\" cellpadding=\"2\">\n";
    foreach ($exam_announcements as $exam_announcement) {
      $msg = $exam_announcement['msg'];
      if (substr_count($msg, '<p>')) {
        $msg = str_replace('<p>', '', $msg);
        $msg = str_replace('</p>', '', $msg);
      }

      echo "<tr><td class=\"q_no\">Q" . $exam_announcement['q_number'] . "</td><td class=\"q_msg\">(" . $exam_announcement['created'] .")<br />" . $msg . "</td></tr>\n";
    }
    echo "</table>\n";

    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['distributionchart'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

    echo "<div class=\"graph\"><img src=\"draw_distribution_chart.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark&q1=" . $stats['q1'] . "&q2=" . $stats['q2'] . "&q3=" . $stats['q3'] . "\" width=\"830\" height=\"300\" alt=\"Distribution Chart\" /></div>\n";

    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['scatterplot'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo "<div class=\"graph\"><img src=\"draw_scatter_plot.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></div>\n";


    // Display summary -------------------------------------------------------------------------------------
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" style=\"width:100%\">";
    echo "<tr><td class=\"subheading\" style=\"width:50px\">" . $string['summary'] . "</td><td style=\"width:48%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['deciles'] . "</td><td style=\"width:30%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['quartiles'] . "</td><td style=\"width:100%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr>\n";
    echo "<tr><td colspan=\"2\" style=\"width:33%\">";

    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td class=\"field\" style=\"width:170px\">" . $string['paper'] . "</td><td colspan=\"3\">$paper</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['cohortsize'];
    if ($_GET['percent'] < 100) {
      if ($ordering == 'desc') {
        echo ' ('.$string['top'].' ' . $_GET['percent'] . '%)';
      } else {
        echo ' ('.$string['bottom'].' ' . $_GET['percent'] . '%)';
      }
    }

    $size_msg = ($cohort_size < $user_no) ? $cohort_size . $string['of'] . $user_no : $user_no;
    echo "</td><td class=\"r\" style=\"width:60px\">$size_msg</td>";
    if (($stats['completed_no'] + $stats['out_of_range']) < $user_no) {
      echo '<td>(' . ($user_no - $stats['completed_no'] - $stats['out_of_range']). ' ' . $string['candidatenotcomplete'] . ')</td>';
    } else {
      echo '<td>';
      if ($absent_no == 1) {
        echo "<span style=\"color:#C00000\">($absent_no " . $string['candidateabsent'] . ")</span>";
      } elseif ($absent_no > 1) {
        echo "<span style=\"color:#C00000\">($absent_no " . $string['candidatesabsent'] . ")</span>";
      }
      echo '</td><td>&nbsp;</td>';
    }
    echo "</tr>\n";

    if ($cohort_size > 0) {
      $percent_failures = round(($stats['failures'] / $cohort_size) * 100);
      $percent_passes = round(($stats['passes'] / $cohort_size) * 100);
      $percent_honours = round(($stats['honours'] / $cohort_size) * 100);
    } else {
      $percent_failures = 0;
      $percent_passes = 0;
      $percent_honours = 0;
    }

    echo "<tr><td class=\"field\">" . $string['failureno'] . "</td><td class=\"r\">" . $stats['failures'] . "</td><td>(" . $percent_failures . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['passno'] . "</td><td class=\"r\">" . $stats['passes'] . "</td><td>(" . $percent_passes . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['distinctionno'] . "</td><td class=\"r\"> " . $stats['honours'] . "</td><td>(" . $percent_honours . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";

    echo "<tr><td class=\"field\">" . $string['totalmarks'] . "</td><td class=\"r\">";
    if ($report->get_total_marks() < $report->get_orig_total_marks()) echo "<span class=\"exclude\">" . $report->get_orig_total_marks() . "</span>&nbsp;&nbsp;";
    echo $report->get_total_marks() . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['passmark'] . "</td><td class=\"r\">$pass_mark%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    if ($marking == '1') {
      echo "<tr><td class=\"field\">" . $string['randommark'] . "</td><td class=\"r\">" . number_format($report->get_total_random_mark(), 2, '.', ',') . "</td><td>&nbsp;</td></tr>\n";
      if ($stats['completed_no'] > 0) {
        if ($report->get_total_marks() > 0) {
          echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
        } else {
          echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } elseif ($marking == '0') {
      if ($stats['completed_no'] > 0) {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } else {
      echo "<tr><td class=\"field\">" . $string['ss'] .  "</td><td class=\"r\">" . round($ss_pass, 2) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ($ss_hon > 0) echo "<tr><td class=\"field\">" . $string['ssdistinction'] . "</td><td class=\"r\">" . MathsUtils::formatNumber($ss_hon, 2) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ($stats['completed_no'] > 0) {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    }
    $mid_point = round($cohort_size / 2) - 1;
    echo "<tr><td class=\"field\">" . $string['medianmark'] . "</td><td class=\"r\">" . round($stats['median_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['median_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
    if ($stats['completed_no'] == 0) {
      echo "<tr><td class=\"field\">" . $string['stdevmark'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['stdevmark'] . "</td><td class=\"r\">" . number_format($stats['stddev_mark'], 2, '.', ',') . "</td><td>(" . MathsUtils::formatNumber($stats['stddev_percent'], 2) . "%)</td><td>&nbsp;</td></tr>\n";
    }
    echo "<tr><td class=\"field\">" . $string['maxmark'] . "</td><td class=\"r\">" . $stats['max_mark'] . "</td><td>(" . number_format($stats['max_percent']) . "%)</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['minmark'] . "</td><td class=\"r\">" . $stats['min_mark'] . "</td><td>(" . number_format($stats['min_percent']) . "%)</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['range'] . "</td><td class=\"r\">" . $stats['range'] . "</td><td>(" . number_format($stats['range_percent']) . "%)</td><td>&nbsp;</td></tr>\n";

    if ($stats['completed_no'] <= 1) {
      echo "<tr><td class=\"field\">" . $string['averagetime'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['averagetime'] . "</td><td class=\"r\">" . $report->formatsec(round($stats['total_time'] / $stats['completed_no'], 0)) . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    }
    if ($report->get_display_excluded() != '') {
      echo "<tr><td class=\"field\">" . $string['excludedquestions'] . "</td><td colspan=\"3\">" . $report->get_display_excluded() . "</td></tr>\n";
    }
    if ($report->get_display_experimental() != '') {
      echo "<tr><td class=\"field\">" . $string['skippedquestions'] . "</td><td colspan=\"3\">" . $report->get_display_experimental() . "</td></tr>\n";
    }
    echo "</table></td>\n";

    echo "<td></td>";

    // Deciles
    $suffix = array('', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th' ,'th');
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    for ($i=1; $i<10; $i++) {
      echo "<tr><td style=\"width:40px\">" . $i;
			echo ($language == 'en') ? $suffix[$i] : '.';
			echo "</td><td>" . MathsUtils::formatNumber($stats["decile$i"], 1) . "%</td></tr>\n";
    }
    echo "</table></td>\n";

    echo "<td></td>";

    // Quartiles
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td style=\"width:40px\">Q1</td><td>" . MathsUtils::formatNumber($stats['q1'], 1) . "%</td></tr>\n";
    echo "<tr><td style=\"width:40px\">Q2</td><td>" . MathsUtils::formatNumber($stats['q2'], 1) . "%</td></tr>\n";
    echo "<tr><td style=\"width:40px\">Q3</td><td>" . MathsUtils::formatNumber($stats['q3'], 1) . "%</td></tr>\n";

    echo "</table></td>\n";

    echo "</tr></table>\n<br />";

    // Email Class -----------------------------------------------------------------------------------------
    if (isset($_POST['emailclass']) and $_POST['emailclass'] == 'yes') {
      // Save the latest template to disk.
      $file = fopen("../email_templates/" . $userObject->get_user_ID(), "w");
      fwrite($file,$_POST['from'] . "\n");
      fwrite($file,$_POST['ccaddress'] . "\n");
      fwrite($file,$_POST['bccaddress'] . "\n");
      fwrite($file,$_POST['subject'] . "\n");
      fwrite($file,$_POST['emailtemplate'] . "\n");
      fclose($file);

      for ($i=0; $i<$user_no; $i++) {
        switch ($i) {
          case 25:
          case 50:
          case 75:
          case 100:
          case 125:
          case 150:
          case 175:
          case 200:
          case 225:
          case 250:
          case 275:
          case 300:
          case 325:
          case 350:
          case 375:
          case 400:
          case 425:
          case 450:
          case 475:
          case 500:
          case 525:
          case 550:
          case 575:
          case 600:
            echo "<tr><td>&nbsp;</td><td colspan=\"8\" height=\"9\">$i sent</td></tr>\n";
            flush();
            ob_flush();
        }

        // Perform replacement.
        $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">\n<html><head>\n<title>$paper</title>\n<style type=\"text/css\">\nbody {font-family: Arial,sans-serif; background-color: white; color:black}</style>\n</head>\n<body>";
        $message .= $_POST['emailtemplate'];
        $message = str_replace("{student-title}", $user_results[$i]['title'], $message);
        $message = str_replace("{student-last-name}", $user_results[$i]['surname'], $message);
        $message = str_replace("{student-mark}", $user_results[$i]['mark'], $message);
        $message = str_replace("{student-percent}", $user_results[$i]['percent'], $message);
        $message = str_replace("{total-paper-mark}", $report->get_total_marks(), $message);
        $message = str_replace("{student-time}", formatsec($user_results[$i]['duration']), $message);
        $message = str_replace("{class-mean-mark}", $stats['mean_mark'], $message);
        $message = str_replace("{class-mean-percent}", $stats['mean_percent'], $message);
        if ($stats['completed_no']-1 == 0) {
          $message = str_replace("{class-stdev}", 0, $message);
        } else {
          $message = str_replace("{class-stdev}", number_format($stats['stddev_mark'], 2, '.', ','), $message);
        }
        $message = str_replace("{class-max-mark}", $stats['max_mark'], $message);
        $message = str_replace("{class-min-mark}", $stats['min_mark'], $message);
        $message = str_replace("{class-mean-time}", formatsec(round($total_time / $stats['completed_no'],0)), $message);
        $message = str_replace("{random-mark}", number_format($report->get_total_random_mark(), 1, '.', ','), $message);
        $message = str_replace("{paper-title}", $paper, $message);

        $to = $user_results[$i]['email'];

        $subject = $_POST['subject'];
        $subject = str_replace("{total-paper-mark}", $report->get_total_marks(), $subject);
        $subject = str_replace("{class-mean-mark}", round($total_mark / $stats['completed_no'], 1), $subject);
        $subject = str_replace("{class-mean-percent}", $stats['mean_percent'], $subject);
        $subject = str_replace("{class-max-mark}", $stats['max_mark'], $subject);
        $subject = str_replace("{class-min-mark}", $stats['min_mark'], $subject);
        $subject = str_replace("{class-mean-time}", formatsec(round($total_time / $stats['completed_no'],0)), $subject);
        $subject = str_replace("{random-mark}", number_format($report->get_total_random_mark(), 1, '.', ','), $subject);
        $subject = str_replace("{paper-title}", $paper, $subject);

        $headers = "From: " . $_POST['from'] . "\n";
        $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
        if ($_POST['ccaddress'] != '') {
          $headers .= "cc: " . $_POST['ccaddress'] . "\n";
        }
        if ($_POST['bccaddress'] != '') {
          $headers .= "bcc: " . $_POST['bccaddress'] . "\n";
        }
        $message .= "</body>\n</html>\n";
        mail ($to, $subject, $message, $headers) or print "<div>" . $string['couldnotsend'] . " <strong>$to</strong>.</div>";
      }
      echo '<p>' . $string['emailssent'] . '</p>';
    } else {
      if ($paper_type < 2) {
        echo "<div>\n";
        echo "<form name=\"theform\" method=\"post\">\n";
        echo "<input type=\"button\" value=\"" . $string['emailclassmarks'] . "\" onclick=\"popupEmailTemplate();\" style=\"margin:10px; width:160px\" />\n";
        echo '<input type="hidden" name="emailclass" value="" />';
        echo '<input type="hidden" name="from" value="" />';
        echo '<input type="hidden" name="emailtemplate" value="" />';
        echo '<input type="hidden" name="ccaddress" value="" />';
        echo '<input type="hidden" name="bccaddress" value="" />';
        echo '<input type="hidden" name="subject" value="" />';
        echo "</form>\n</div>\n";
      }
    }
    echo "</table>\n";
  } else {
		$msg = sprintf($string['noattempts'], $report->nicedate($startdate), $report->nicedate($enddate));
		echo $notice->info_strip($msg, 100) . "\n</div>\n</body>\n</html>";
    exit;
  }
  $mysqli->close();
?>
<input type="hidden" id="metadataID" value="" /><input type="hidden" id="userID" value="" /><input type="hidden" id="log_type" value="" /><input type="hidden" id="reassign" value="" /><input type="hidden" id="loglate" value="" /><input type="hidden" id="percent" value="" />
</div>
</body>
</html>
