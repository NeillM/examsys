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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
 
require_once '../include/invigilator_auth.inc';
require_once '../classes/usernotices.class.php';
require_once '../include/errors.inc';
require_once '../include/sort.inc';

require_once '../classes/stringutils.class.php';
require_once '../classes/paperproperties.class.php';
require_once '../classes/log_lab_end_time.class.php';
require_once '../classes/lab_factory.class.php';
require_once '../classes/lab.class.php';
require_once '../classes/log_extra_time.class.php';
require_once '../classes/logmetadata.class.php';
require_once '../classes/noteutils.class.php';

function get_students($modules, $property_object, $log_lab_end_time, $allow_timing, $string, $db) {
  $paperID = $property_object->get_property_id();

  $configObject = Config::get_instance();

  //create a caching LogExtraTime gets all the results in one hit
  $log_extra_time = new LogExtraTime($log_lab_end_time, array(), $db, true);

  // Get any student notes.
  $notes_array = PaperNotes::get_all_notes_by_paper($paperID, $db);

  ?>

<div class="cohortlist">
  <table style="font-size:100%; line-height:150%" cellpadding="2" cellspacing="0" border="0" width="100%">
    <tr>
      <th>
      </th>

      <th>
        <?php echo $string['title'] ?>
      </th>

      <th>
        <?php echo $string['surname'] ?>
      </th>

      <th>
        <?php echo $string['forenames'] ?>
      </th>

      <th>
        <?php echo $string['endtime'] ?>
      </th>

      <th>
        <?php echo $string['extension_mins'] ?>
      </th>

      <th>
      </th>
    </tr>

    <?php
      // Get all students who should are able to access this paper
      $sql = "SELECT DISTINCT extra_time, medical, breaks, modules_student.userID, surname, first_names, title FROM modules_student, users LEFT JOIN special_needs ON users.id = special_needs.userID WHERE idMod IN ( " . $modules . ") AND calendar_year = ? AND modules_student.userID = users.id";
      $results = $db->prepare($sql);
      $session = $property_object->get_calendar_year();
      $results->bind_param('s', $session);
      $results->execute();
      $results->store_result();
      $results->bind_result($extra_time_percentage, $medical, $breaks, $userID, $surname, $first_names, $title);
      $student_object = array();

      while ($results->fetch()) {
        $student_object[$userID]['user_ID'] = $userID;
        $student_object[$userID]['surname'] = $surname;
        $student_object[$userID]['first_names'] = $first_names;
        $student_object[$userID]['title'] = $title;
        $student_object[$userID]['extra_time_percentage'] = $extra_time_percentage;
        $student_object[$userID]['medical'] = $medical;
        $student_object[$userID]['breaks'] = $breaks;
      }
      $results->close();

      // Merge in all students who have submitted records for this paper
      $sql = 'SELECT DISTINCT sn.extra_time, sn.medical, sn.breaks, lm.userID, u.surname, u.first_names, u.title FROM log_metadata lm INNER JOIN users u ON lm.userID = u.id LEFT JOIN special_needs sn ON u.id = sn.userID WHERE lm.paperID = ? AND u.username LIKE "user%"';
      $results = $db->prepare($sql);
      $results->bind_param('i', $paperID);
      $results->execute();
      $results->store_result();
      $results->bind_result($extra_time_percentage, $medical, $breaks, $userID, $surname, $first_names, $title);
      while ($results->fetch()) {
        $student_object[$userID]['user_ID'] = $userID;
        $student_object[$userID]['surname'] = $surname;
        $student_object[$userID]['first_names'] = $first_names;
        $student_object[$userID]['title'] = $title;
        $student_object[$userID]['extra_time_percentage'] = $extra_time_percentage;
        $student_object[$userID]['medical'] = $medical;
        $student_object[$userID]['breaks'] = $breaks;
      }
      $results->close();
      
      $column = 'surname';
      $sort_order = 'asc';
      $student_object = array_csort($student_object, $column, $sort_order);

      foreach( $student_object as $student_id => $student_obj) {
        process_student_list($log_lab_end_time, $log_extra_time, $student_obj, $property_object, $configObject, $notes_array, $allow_timing, $string, $db);
      }

      ?>
    </table>
</div>
<?php
}

/*
* @param LogLabEndTime  $log_lab_end_time
* @param UserObject     $student_object
* @param PropertyObject $property_object
* @param Config         $configObject
* @param int            $extra_time_percentage
* @param array          $notes_array
* @param string         $string
* @param mysqli         $mysqli
 */
function process_student_list($log_lab_end_time, $log_extra_time, $student_object, $property_object, $configObject, $notes_array, $allow_timing, $string, $mysqli) {

  // Determine when the current exam session will end

  $lab_session_end_datetime = $log_lab_end_time->get_session_end_date_datetime();

  if ($lab_session_end_datetime == false) {
    $lab_session_end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
  }

  $exam_duration_mins = $property_object->get_exam_duration();

  $class = '';

  if ($exam_duration_mins == NULL) {
    throw new ErrorException('Exam duration is mandatory in summative exams');
  }

  if (is_int($exam_duration_mins) === false) {
    throw new ErrorException('$exam_duration_mins ' . $exam_duration_mins . ' must be an integer');
  }

  $exam_duration_interval = new DateInterval('PT' . $exam_duration_mins . 'M');
  $lab_session_start_datetime = clone $lab_session_end_datetime;
  $lab_session_start_datetime->sub($exam_duration_interval);

  // Determine when the student's exam session will end

  //set userID log_extra_time as we are in cached mode
  $log_extra_time->set_student_object($student_object);

  $student_end_datetime = $lab_session_end_datetime;

  // Calculate whether student's extended 'end time' is before the current session's start time
  // Currently unused but could be altered to exit if student's extra end time is before session's start time

  $is_student_end_before_session_start = $student_end_datetime < $lab_session_start_datetime;

  // Highlight student's who have gone over time

  $current_datetime = new DateTime();

  // Calculate extra time

  $extra_time_secs = $log_extra_time->get_extra_time_secs();
  $extra_time_mins = round($extra_time_secs / 60);

  $special_needs_extra_time_mins = ($exam_duration_mins / 100) * $student_object['extra_time_percentage'];
  $special_needs_extra_time_secs = (int)($special_needs_extra_time_mins * 60);
  $total_extra_time = $extra_time_secs + $special_needs_extra_time_secs;

  $total_extra_time_interval = new DateInterval('PT' . $total_extra_time . 'S');

  $student_end_datetime = $student_end_datetime->add($total_extra_time_interval);

  $paper_end_datetime = $log_lab_end_time->get_session_end_date_datetime();

  $ft = clone $student_end_datetime;
  $ft->setTimezone(new DateTimeZone($property_object->get_timezone()));
  $formatted_end_time = $ft->format($configObject->get('cfg_short_time_php'));

  if ($extra_time_secs > 0 or $special_needs_extra_time_secs > 0) {
    $formatted_end_time = '<strong>' . $formatted_end_time . '</strong>';
  }

  // Get student description
  $tmp_userID = $student_object['user_ID'];
  $surname = $student_object['surname'];
  $first_names = $student_object['first_names'];
  $title = $student_object['title'];

  $paperID = $property_object->get_property_id();

  $has_student_exceeded_end = ($student_end_datetime < $current_datetime);

  if ($has_student_exceeded_end) {
    $class = 'redwarn';
  }

  ?>
<tr class="<?php echo $class; ?>">
  <td>
<?php
  if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == 'y') {
    echo '<img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" />';
  }
?>
  </td>
  
    <td style="cursor:hand" onclick="popMenu('<?php echo $tmp_userID ?>', '<?php echo $paperID ?>', <?php echo $allow_timing ? 'true' : 'false'; ?>, event);"/><?php echo $title ?></td>
    <td style="cursor:hand" onclick="popMenu('<?php echo $tmp_userID ?>', '<?php echo $paperID ?>', <?php echo $allow_timing ? 'true' : 'false'; ?>, event);"/><?php echo $surname ?></td>
    <td style="cursor:hand" onclick="popMenu('<?php echo $tmp_userID ?>', '<?php echo $paperID ?>', <?php echo $allow_timing ? 'true' : 'false'; ?>, event);"/><?php echo $first_names ?></td>

    <td style="text-align:center">
      <?php echo $formatted_end_time; ?>
    </td>


    <td style="text-align:center">
      <?php
      if ($special_needs_extra_time_mins != '') {
        ?>
          <span style=""><?php echo $special_needs_extra_time_mins ?></span>
        <?php
      }
      if ($special_needs_extra_time_mins != '' and $extra_time_mins != '') {
        echo ' + ';
      }
      if ($extra_time_mins != '') {
        ?>
          <img src="../artwork/clock_16.png" class="small_icon" alt="<?php echo $string['extratime'] . '\\'; ?>" />
          <span style=""><?php echo $extra_time_mins; ?></span>
        <?php
      }
      ?>
    </td>
    <td>
    <?php
      $cellID = $paperID . '_' . $tmp_userID;
      echo "<div id=\"p$cellID\">";
      if ($student_object['medical'] != '') {
        echo '<img src="../artwork/medical_16.gif" class="small_icon" alt="Medical" onmouseover="showCallout(\'' . $cellID . '\', \'' . $student_object['medical'] . '\')" onmouseout="hideCallout()" />';
      }
      if ($student_object['breaks'] != '') {
        echo '<img src="../artwork/moon_16.gif" class="small_icon" alt="Breaks" onmouseover="showCallout(\'' . $cellID . '\', \'' . $student_object['breaks'] . '\')" onmouseout="hideCallout()" />';
      }
      ?>
    </div>
    </td>
</tr>
<?php
}

function emergencyNumbers($support_numbers, $string) {
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; float:right; line-height:100%; margin-right:20px\">\n";
  echo "<tr><td colspan=\"2\" class=\"en\">Emergency Numbers</td></tr>\n";
  foreach ($support_numbers as $number => $contact) {
    echo "<tr><td><img src=\"../artwork/phone.png\" width=\"28\" height=\"28\" alt=\"call\" /></td><td><strong>$number</strong><br />$contact</td></tr>\n";
  }
  echo "</table>\n";
}

function get_timestamp_from_time($hours, $minutes, $timezone) {
  $tmp_datetime = new DateTime(date('Y-m-d') . $hours . ':' . $minutes . ':00', $timezone);
  return $tmp_datetime->getTimestamp();
}

if (isset($_POST['start_exam_form'])) {
  check_var('paper_id', 'POST', true, false, false);
}

$current_address = NetworkUtils::get_client_address();

$lab = new LabFactory($mysqli);

$lab_object = $lab->get_lab_based_on_client($current_address);
$lab_id = $lab_object->get_id();
$room_name = $lab_object->get_name();

$properties_list = array();

$properties_list = PaperProperties::get_paper_properties_by_lab($lab_object, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

<title>Rog&#333;: <?php echo $string['invigilatoraccess']; ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css"/>
<link rel="stylesheet" type="text/css" href="../css/header.css"/>
<link rel="stylesheet" type="text/css" href="../css/invigilator.css"/>

<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
<script type="text/javascript">

  var ns6 = document.getElementById && !document.all;
  var isMenu = false;
  var overpopupmenu = false;

  function mouseSelect(e) {
    var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
    if (isMenu) {
      if (overpopupmenu == false) {
        isMenu = false;
        overpopupmenu = false;
        $('#menudiv').hide();
        return true;
      }
      return true;
    }
  }

  function popMenu(tmpUserID, paperID, showExtension, e) {
    if (!e) var e = window.event;
    var currentX = e.clientX;
    var currentY = e.clientY;
    var scrOfX = $('body,html').scrollLeft();
    var scrOfY = $('body,html').scrollTop();

    $('#userID').val(tmpUserID);
    $('#paperID').val(paperID);

    top_pos = currentY + scrOfY;

    if (top_pos > ($(window).height() + scrOfY - 130)) {
      top_pos = $(window).height() + scrOfY - 130;
    }

    if (showExtension) {
      $('.menu-time').show();
    } else {
      $('.menu-time').hide();
    }

    $('#menudiv').css('left', currentX + scrOfX);
    $('#menudiv').css('top', top_pos);

    $('#menudiv').show();

    isMenu = true;
    return false;
  }

  function showCallout(cellID, displayTxt) {
		var p = $('#p' + cellID);
		var position = p.position();
		
		var left_pos = position.left;
		if (left_pos + 302 > $(window).width()) {
			left_pos = $(window).width() - 302;
		}
		$('#callout').css('left', left_pos);
		$('#callout').css('top', position.top + p.height() + 12); 
    
    $('#calloutTxt').text(displayTxt);
    $('#callout').show();
  }

  function hideCallout() {
    $('#callout').hide();
  }

  // please keep these lines on when you copy the source
  // made by: Nicolas - http://www.javascript-page.com
  var clockID = 0;
  function UpdateClock() {
    if (clockID) {
      clearTimeout(clockID);
      clockID = 0;
    }
    var tDate = new Date();
    $('.theTime').text(((tDate.getHours() < 10) ? "0" : "") + tDate.getHours() +
            ((tDate.getMinutes() < 10) ? ":0" : ":") + tDate.getMinutes() +
            ((tDate.getSeconds() < 10) ? ":0" : ":") + tDate.getSeconds());
    clockID = setTimeout("UpdateClock()", 1000);
  }

  function StartClock() {
    clockID = setTimeout("UpdateClock()", 500);
  }

  function KillClock() {
    if (clockID) {
      clearTimeout(clockID);
      clockID = 0;
    }
  }

  function newStudentNote() {
	  $('#menudiv').hide();
    studentnote = window.open("new_student_note.php?userID=" + $('#userID').val() + "&paperID=" + $('#paperID').val() + "", "studentnote", "width=650,height=430,left=" + (screen.width / 2 - 300) + ",top=" + (screen.height / 2 - 200) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

    if (window.focus) {
      studentnote.focus();
    }
  }

  function newPaperNote(paperID) {
    papernote = window.open("new_paper_note.php?paperID=" + paperID + "","papernote","width=650,height=410,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      papernote.focus();
    }
  }
  
  function viewRubric(paperID) {
    $('#rubric_' + paperID).show();
  }

  function extendTime() {
	  $('#menudiv').hide();
    papernote = window.open("extend_time.php?paperID=" + $('#paperID').val() + "&userID=" + $('#userID').val(), "extendtime", "width=250,height=150,left=" + (screen.width / 2 - 300) + ",top=" + (screen.height / 2 - 200) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

    if (window.focus) {
      papernote.focus();
    }
  }

  function resizeLists() {
    var myHeight = $(window).height() - 230;
    var mysheet = document.styleSheets[0];
    var totalrules = mysheet.cssRules ? mysheet.cssRules.length : mysheet.rules.length
    if (mysheet.deleteRule) { //if Firefox
      mysheet.insertRule(".cohortlist {height:" + myHeight + "px; overflow:auto}", totalrules);
      mysheet.insertRule(".clarifymsgtbl {height:" + myHeight + "px; overflow:auto}", totalrules);
    } else if (mysheet.removeRule) { //else if IE
      document.styleSheets[0].addRule(".cohortlist", "height:" + myHeight + "px; overflow:auto");
      document.styleSheets[0].addRule(".clarifymsgtbl", "height:" + myHeight + "px; overflow:auto");
    }
  }

  function clarifyMethod() {
  <?php
	  if (is_array($properties_list)) {
			foreach ($properties_list as $property_object) {
				$paperID = $property_object->get_property_id();
		?>
			$.get("check_exam_announcements.php", {paperID:"<?php echo $paperID; ?>"}, function(data) {
				if ($('#msg<?php echo $paperID; ?>').html() != data) {
					$('#msg<?php echo $paperID; ?>').html(data);
					$('#msg<?php echo $paperID; ?>').effect("highlight", {}, 20000);
				}
			});
		<?php
      }
		}
  ?>
  }

  document.onmousedown = mouseSelect;

  function changeTab() {
    if (!$(this).parent().hasClass('disabled')) {
      if (!$(this).parent().hasClass('on')) {
        $('.tab-area').hide();
        $('.tabs li').each(function () {
          $(this).removeClass('on');
        });
        $(this).parent().addClass('on');

        var id = $(this).attr('rel');
        $('#' + id).fadeIn();
      }
    }

    return false;
  }

  // Register the events we need
  $(document).ready(function() {
    $('.menu-time').click(extendTime);
    $('.menu-note').click(newStudentNote);
    StartClock();
    resizeLists();
    $(window).unload(KillClock);
    $(window).resize(resizeLists);
    
    $('.tabs li a').click(changeTab);

<?php
  if (in_array('invigilators', $configObject->get('midexam_clarification'))) {
    echo "var clarificationCall = setInterval(clarifyMethod, 10000);\n";
  }
?>
 });
</script>

</head>

<body>
  
<div id="callout" class="callout border-callout">
<b class="border-notch notch"></b>
<b class="notch"></b>
<div id="calloutTxt"></div>
</div>

  <?php
$popup_width = 180;
if ($language != 'en') {
  $popup_width = 300;
}

if ($properties_list !== false and count($properties_list) > 0) {
?>

<div id="menudiv" style="width:<?php echo $popup_width; ?>px; background-color:white; padding:1px; font-size:80%; position:absolute; display:none; top:0; left:0; z-index:10000; border:1px solid #868686; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
  <table cellspacing="2" cellpadding="0" border="0" style="font-size:100%; background-color:white; width:100%">
    <tr>
      <td>
        <ul>
          <li class="menu-time"><?php echo $string['extendtime']; ?></li>
          <li class="menu-note"><?php echo $string['addnote']; ?></li>
        </ul>
      </td>
    </tr>
  </table>
</div>
  
<div class="tab-bar">
  <div class="tab-holder">
    <ol class="tabs">
      <li class="on"><a href="#" rel="checklist">Exam Checklist</a></li>
      <?php
      foreach ($properties_list as $property_object) {
        $paper_title = $property_object->get_paper_title();
        $paperID = $property_object->get_property_id();
        echo "<li><a href=\"#\" rel=\"paper$paperID\">$paper_title</a></li>\n";
      }
      ?>
    </ol>
    <div style="float:right; padding-right:5px"><a href="../logout.php"><img src="../artwork/student_logout.png" width="24" height="24" /></a></div>
  </div>
</div>
<?php
  foreach ($properties_list as $property_object) {
    $title          = $property_object->get_paper_title();
    $property_id    = $property_object->get_property_id();
    $exam_duration  = $property_object->get_exam_duration();
    $start_date     = $property_object->get_display_start_time();
    $calendar_year  = $property_object->get_calendar_year();
    $rubric         = $property_object->get_rubric();
    
    echo "<div class=\"rubric\" id=\"rubric_$property_id\"><div class=\"rubrictitle\">Exam Rubric<img onclick=\"$('#rubric_$property_id').hide();\" src=\"../artwork/lrg_close.png\" class=\"rubricclose\" alt=\"Close\" /></div><div class=\"rubric_txt\">$rubric</div>\n</div>\n";

    // Get modules for this paper and check if timing is allowed
    $timed_modules = $all_modules = 0;
    $sql = 'SELECT m.id, m.timed_exams FROM properties_modules pm INNER JOIN modules m ON pm.idMod = m.id WHERE pm.property_id = ?';

    $module_results = $mysqli->prepare($sql);
    $module_results->bind_param('i', $property_id);
    $module_results->execute();
    $module_results->store_result();
    $module_results->bind_result($moduleID, $timed_exams);

    $modules = array();

    while ($module_results->fetch()) {
      $modules[] = $moduleID;
      $all_modules++;
      if ($timed_exams == true) {
        $timed_modules++;
      }
    }

    $allow_timing = ($timed_modules == $all_modules);

    $exam_started = false;

    // Has 'Start' button been submitted

    $log_lab_end_time = new LogLabEndTime($lab_object->get_id(), $property_object, $mysqli);

    $end_datetime = $log_lab_end_time->get_session_end_date_datetime();

    if ($end_datetime == false) {
      $end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
    } else {
      $exam_started = true;
      $started_timestamp = $log_lab_end_time->get_started_timestamp();
      //$start_date = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php'), $started_timestamp);
      $start_date = date($configObject->get('cfg_long_time_php'), $started_timestamp);
    }

    $disptimezone = new DateTimeZone($property_object->get_timezone());

    if ($allow_timing and isset($_POST['start_exam_form'])) {
      $paper_id = (int)$_POST['paper_id'];

      // Does the submitted paperID correspond it to the currently iterated paper?
      if ($paper_id == (int)$property_id) {
        $invigilator_id = $userObject->get_user_ID();
        $end_datetime = $log_lab_end_time->save($invigilator_id);
        $exam_started = true;
        //$start_date = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php'));
        $start_date = date($configObject->get('cfg_long_time_php'));
      }
    }

    echo "<div id=\"paper$property_id\" style=\"display: none\" class=\"tab-area\">\n";
    if ($allow_timing and isset($_POST['end_exam_form'])) {

      $paper_id = (int)$_POST['paper_id'];

      // Does the submitted paperID correspond it to the currently iterated paper?

      if ($paper_id == (int)$property_id) {
        $end_timestamp = get_timestamp_from_time($_POST['hour'], $_POST['minute'], $disptimezone);
        $exam_duration_s = $exam_duration * 60;

        if (($end_timestamp - $started_timestamp) > $exam_duration_s) {
          // End time is past start time + duration so is OK
          $invigilator_id = $userObject->get_user_ID();
          $time = 'PT' . $_POST['hour'] . 'H' . $_POST['minute'] . 'M';
          $end_datetime = $log_lab_end_time->save($invigilator_id, $time);
        } else {
          $notice = UserNotices::get_instance();
          $notice->display_notice($string['timeerror'], sprintf($string['timeerrormsg'], $exam_duration), '../artwork/summative_scheduling.png', '#C00000', false, false);
        }
      }
    }

    $end_datetime->setTimezone($disptimezone);

    $end_date = $end_datetime->format($configObject->get('cfg_long_time_php'));

    $end_time_h = $end_datetime->format('H');
    $end_time_m = $end_datetime->format('i');
    
    $password = $property_object->get_password();
    ?>
          
          <div class="exam_details">
            <table style="width:100%" cellpadding="2" cellspacing="0" border="0">
              <tr>
                <td rowspan="5" style="width:50px; vertical-align:top">
                  <img src="../artwork/summative.png" align="left" width="48" height="48" alt="paper icon" />
                </td>
                <td style="font-weight:bold; width:150px"><?php echo $string['currenttime'] ?></td>
                <td style="font-weight:bold" class="theTime"></td>
                <td rowspan="5" style="vertical-align: top">
                  <?php
                  if ($allow_timing) {
                  ?>
                  <form id="start_exam_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <input name="paper_id" type="hidden" value="<?php echo $property_id; ?>"/>
                    <?php
                      if ($exam_started) {
                    ?>
                      <fieldset id="set_end">
                        <input id="end_exam_button" name="end_exam_form" type="submit" value="<?php echo $string['endat_but'] ?>" class="exam-button" /><br />
                        <?php echo $string['time'] ?>:<select id="hour" name="hour">
                            <?php for($hr=0; $hr<24; $hr++) { $selected = ''; if($hr == $end_time_h) { $selected = 'selected'; } echo '<option value="' . $hr . '"' . $selected . '>' . str_pad($hr, 2, '0', STR_PAD_LEFT) . '</option>'; } ?>
                        </select>
                        :
                        <select id="minute" name="minute">
                          <?php for($hr=0; $hr<60; $hr++) { $selected = ''; if($hr == $end_time_m) { $selected = 'selected'; } echo '<option value="' . $hr . '"' . $selected . '>' . str_pad($hr, 2, '0', STR_PAD_LEFT) . '</option>'; } ?>
                        </select>
                      </fieldset>
                    <?php
                      } else {
                    ?>
                      <fieldset id="start_exam">
                        <input id="start_exam_button" name="start_exam_form" type="submit" value="<?php echo $string['start_but'] ?>" class="exam-button" />
                      </fieldset>
                    <?php
                      }
                    ?>
                  </form>
                  <?php
                  }
                  ?>
                </td>
                <td rowspan="5">
                  <?php
                    emergencyNumbers($configObject->get('emergency_support_numbers'), $string);
                  ?>
                </td>
              </tr>
              
              <tr>
                <td><?php echo $string['start'] ?></td>
                <td><?php echo $start_date ?></td>
              </tr>

              <tr>
                <td><?php echo $string['end'] ?></td>
                <td><?php echo $end_date ?></td>
              </tr>
              <tr>
                <td><?php echo $string['duration'] ?></td>
                <td><?php echo StringUtils::nice_duration($exam_duration, $string) ?></td>
              </tr>
              <tr>
                <?php
                if ($password == '') {
                  echo "<td>&nbsp;</td><td></td>\n";
                } else {
                  echo "<td class=\"password\">" . $string['password'] . " <img src=\"../artwork/key.png\" width=\"16\" height=\"16\" /></td><td style=\"font-family:'Lucida Console', monospace\">$password</td>\n";
                }
                ?>
              </tr>
              
              <tr>
                <td colspan="2"><input type="button" onclick="newPaperNote(<?php echo $property_id; ?>);" value="<?php echo $string['papernote'] ?>" class="ok" /><input type="button" onclick="viewRubric(<?php echo $property_id; ?>);" value="Show Rubric" class="ok" /></td>
              </tr>

            </table>            
          </div>
        <?php
        if (in_array('invigilators', $configObject->get('midexam_clarification'))) {
          echo "<div id=\"clarifymsgtbl\" class=\" cohortlist\" style=\"float:left; width:50%\"><table cellpadding=\"2\" cellspacing=\"0\" style=\"width:100%; line-height:150%\">\n<tr><th>" . $string['midexamclarrifications'] . "</th></tr>\n</table>\n";
          echo "<div id=\"msg$property_id\" class=\"clarifymsg\"><span class=\"blankclarification\">" . $string['examquestionclarifications'] . "</span></div>\n</div>\n";
        }
        $modules = implode('\',\'', $modules);

        $modules = '\'' . $modules . '\'';

        get_students($modules, $property_object, $log_lab_end_time, $allow_timing, $string, $mysqli);
        ?>
      </div>
    <?php
  }

  ?>

    <div id="checklist" class="tab-area" style="padding:50px;">
      <div class="preexam">
        <h1><?php echo $string['preexam'] ?></h1>
        <?php echo $string['preexamlist'] ?>
      </div>
      <br />
      <div class="midexam">
        <h1><?php echo $string['midexam'] ?></h1>
        <?php echo $string['midexamlist'] ?>
      </div>
      <br />
      <div class="postexam">
        <h1><?php echo $string['postexam'] ?></h1>
        <?php echo $string['postexamlist'] ?>
      </div>
    
    <br/>
    </div>
  <?php
} else {
  echo "<div style=\"background-color:white\">\n";
  emergencyNumbers($configObject->get('emergency_support_numbers'), $string, 68);
  echo "<p><img src=\"../artwork/page_not_found.png\" width=\"48\" height=\"48\" alt=\"!\" style=\"float:left; padding-left:10px; padding-right:10px\" /><span style=\"font-weight:bold; color:#C00000; font-size:150%\">" . $string['nopapersfound'] . "</span><br /><br />" . $string['nopapersfoundmsg'] . "</p><br clear=\"all\" />";

  echo "</div>\n";
}

$mysqli->close();
?>
  <input type="hidden" id="userID" value="" />
  <input type="hidden" id="paperID" value="" />
</body>
</html>
