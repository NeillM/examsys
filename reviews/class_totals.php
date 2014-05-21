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
* Class Totals (for externals) report.
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

$id         = check_var('id', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli);

$paper            = $propertyObj->get_paper_title();
$paperID          = $propertyObj->get_property_id();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();
$startdate        = $propertyObj->get_raw_start_date();
$enddate          = $propertyObj->get_raw_end_date();

$percent      = 100;
$ordering     = 'asc';
$absent       = 0;
$sortby       = 'name';
$studentsonly = 1;
$repcourse    = '%';
$repmodule    = '';

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli);
$report->compile_report(false);

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

  function viewNote(userID, e) {
    $('#menudiv').hide();
    if (!e) var e = window.event;
	  var currentX = e.clientX;
	  var currentY = e.clientY;
    var scrOfX = $(document).scrollLeft();
    var scrOfY = $(document).scrollTop();

    dataSource = "../reports/getNote.php?paperID=<?php echo $paperID; ?>&userID=" + userID;

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
    e.stopPropagation();
  }
	
	$(document).ready(function() {
    $("#maindata").tablesorter({ 
      // sort on the first column and third column, order asc 
      sortList: [[4,0]] 
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
<table cellspacing="2" cellpadding="0" border="0" style="font-size:90%; width:100%">
  <tr><td>
    <table cellspacing="0" cellpadding="1" border="0" style="width:100%">
      <tr>
        <td id="item1a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" /></td><td id="item1b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><?php echo $string['examscript']; ?></td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" /></td><td id="item2b" style="padding-left:8px; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><?php echo $string['feedback']; ?></td>
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
		$table_order = array(''=>16, $string['studentid']=>80, $string['course']=>55, $string['mark']=>50, $marking_label=>80, $string['classification']=>80, $string['rank']=>50, $string['decile']=>50, $string['starttime']=>170, $string['duration']=>70, $string['hostnames']=>100);
	} else {
		$table_order = array(''=>16, $string['studentid']=>80, $string['course']=>55, $string['mark']=>50, $marking_label=>80, $string['classification']=>80, $string['rank']=>50, $string['decile']=>50, $string['starttime']=>170, $string['duration']=>70, $string['ipaddress']=>100);
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
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';

  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif ( isset( $_GET['module'] ) and $_GET['module'] != '' ) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

  $report_title = $string['classtotals'];
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
        $student_id = $user_results[$i]['username'];
        
        if ($user_results[$i]['student_id'] == '') {
          if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
            echo "<td class=\"grey $class padl $role_css\">&nbsp;";
          } else {
            echo "<td class=\"grey $class padl $role_css\">" . $string['unknown'];
          }
        } else {
          echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['student_id'];
        }
        
        // Add icons
        if ($user_results[$i]['attempt'] > 1) {
          echo '&nbsp;<img src="../artwork/resit.png" width="16" height="16" alt="Resit" />';
        }
        if (isset($notes[$user_results[$i]['userID']]) and $notes[$user_results[$i]['userID']] == 'y') {
          echo '&nbsp;<a href="" onclick="viewNote(\'' . $user_results[$i]['userID'] . '\', event); return false;"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Notes" /></a>';
        }
        if (isset($special_needs[$user_results[$i]['userID']]) and $special_needs[$user_results[$i]['userID']] == 'y') {
          echo '&nbsp;<img src="../artwork/accessibility_16.png" width="16" height="16" alt="' . $string['alternativearrangements'] . '" />';
        }        
        echo '</td>';
        
        echo "<td class=\"$class padl $role_css\">" . $user_results[$i]['student_grade'] . "</td>";
       			
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

    echo "<div class=\"graph\"><img src=\"../reports/draw_distribution_chart.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark&q1=" . $stats['q1'] . "&q2=" . $stats['q2'] . "&q3=" . $stats['q3'] . "\" width=\"830\" height=\"300\" alt=\"Distribution Chart\" /></div>\n";

    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['scatterplot'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo "<div class=\"graph\"><img src=\"../reports/draw_scatter_plot.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></div>\n";


    // Display summary -------------------------------------------------------------------------------------
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" style=\"width:100%; font-size:110%\">";
    echo "<tr><td class=\"subheading\" style=\"width:50px\">" . $string['summary'] . "</td><td style=\"width:48%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['deciles'] . "</td><td style=\"width:30%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['quartiles'] . "</td><td style=\"width:100%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr>\n";
    echo "<tr><td colspan=\"2\" style=\"width:33%\">";

    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td class=\"field\" style=\"width:170px\">" . $string['paper'] . "</td><td colspan=\"3\">$paper</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['cohortsize'];
    
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
