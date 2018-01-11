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
* Handles paper display and the recording of marks to the 'logX' tables. Uses functions within 'display_functions.inc' to process specific
* types of questions. Start.php continues calling itself while there are further screens to be displayed and then calls 'finish.php'
* to end.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
require_once '../include/staff_student_auth.inc';
require_once '../include/paper_security.php';
require_once '../include/display_functions.inc';
require_once '../include/media.inc';
require_once '../include/errors.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
$userObject = UserObject::get_instance();

if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {    // Special users have their own separate UI.
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['accessdenied'], $msg, $string['accessdenied'], $configObject->get('cfg_root_path') . '/artwork/access_denied.png', '#C00000', true, true);
}

// Get parameters.
$id = check_var('id', 'GET', true, false, true, param::ALPHANUM); // While it is an int, the numbers are too large for 32-bit PHP.
$mode = param::optional('mode', '', param::ALPHA);
$getmode = param::optional('mode', '', param::ALPHA, param::FETCH_GET);
$post_screen = param::optional('current_screen', null, param::INT, param::FETCH_POST);
$get_qid = param::optional('q_id', null, param::INT, param::FETCH_GET);
$q_number = param::optional('qNo', null, param::INT, param::FETCH_GET);
$do_not_record = param::optional('dont_record', false, param::BOOLEAN, param::FETCH_GET);
$refpane = param::optional('refpane', 0, param::INT, param::FETCH_POST);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$deleted = $propertyObj->get_deleted();

// If the paper has been deleted we should exit as this is an invalid page
// and Deny access to offline papers.
if ($deleted != NULL or $propertyObj->get_paper_type() == '5') {
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/exclamation_48.png', '#C00000', true, true);
}

$paperID = $propertyObj->get_property_id();

/*
 *
 * Setup some feature related flags
 *
 */

// Are we in a staff test and preview mode?
$is_preview_mode = ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')) and $mode === 'preview');

// Are we on the first screen?
$is_first_launch = is_null($post_screen);

// Are we in a staff test and preview mode and on the first screen?
$is_preview_mode_first_launch = ($is_preview_mode == true and $getmode === 'preview');

// Are we in a staff single question test mode?
$is_question_preview_mode = !is_null($get_qid);

if (!$is_first_launch) require '../include/marking_functions.inc';

$screen_data = $propertyObj->get_screens($is_question_preview_mode);
$no_screens = $propertyObj->get_max_screen();

//store the original paper type - needed to retrieve answers from the correct log and functionality related decisions
$original_paper_type = $propertyObj->get_paper_type();

// Is this a type of paper that allows only one attempt?
$do_restart = ($is_first_launch and ($original_paper_type == 1 or $original_paper_type == 2 or $original_paper_type == 3));

/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

$attempt = 1;                 //default attempt to 1 overwritten if the student is resit candidate by (check_modules)
$low_bandwidth = 0;           //default to off overwritten by (check_labs) if lab has low_bandwidth set
$lab_name = NULL;             //default overwritten by (check_labs)
$lab_id = NULL;
$current_address = NULL;   //default overwritten by (check_labs)

$current_address = NetworkUtils::get_client_address();

//get the module Ids for this paper
$modIDs = array_keys(Paper_utils::get_modules($paperID, $mysqli));
$moduleID = $propertyObj->get_modules();

if ($userObject->has_role('Staff') and check_staff_modules($moduleID, $userObject)) {
  // No further security checks.
} else {    // Treat as student with extra security checks.

  // Check for additional password on the paper.
  check_paper_password($propertyObj->get_property_id(), $propertyObj->get_password(), $string, $mysqli);

  // Check time security.
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli, $is_first_launch);

  //Check room security.
  $low_bandwidth = check_labs(  $propertyObj->get_paper_type(),
                                $propertyObj->get_labs(),
                                $current_address,
                                $propertyObj->get_password(),
                                $string,
                                $mysqli
                              );

  // Check modules if the user is a student and the paper is not formative.
  $attempt = check_modules($userObject, $modIDs, $propertyObj->get_calendar_year(), $string, $mysqli);

  // Check for any metadata security restrictions.
  check_metadata($paperID, $userObject, $modIDs, $string, $mysqli);

  // Check if the student has clicked 'Finish'.
  check_finished($propertyObj, $userObject, $string, $mysqli);
}

// Get lab info used in log metadata.
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)) {
  $lab_name = $lab_object->get_name();
  $lab_id = $lab_object->get_id();
}

/*
* Set the default state
*/
$log_metadata = null;
$current_screen = 1;
$is_fire_alarm = param::optional('fire_alarm', false, param::BOOLEAN, param::FETCH_POST);
$summative_exam_session_started = false; //lab timing stated by invigilators
$allow_timing = false;

/*
* Extract the posted variables.
*/
if (!$is_first_launch) {
  $button_pressed = param::optional('button_pressed', '', param::TEXT, param::FETCH_POST);
  if ($button_pressed == 'next') {
    $current_screen = $post_screen;
  } elseif ($button_pressed == 'previous') {
    $current_screen = $post_screen - 2;
  } elseif ($button_pressed == 'jumpscreen') {
    $current_screen = param::optional('jumpscreen', 0, param::INT, param::FETCH_POST);
  } elseif ($is_fire_alarm) {
    $current_screen = $post_screen;
  }
}

// Set up new metadata record or get existing one.
$log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);

if ($is_preview_mode_first_launch == true or ($is_first_launch and !$do_restart)) {
  //in preview mode or for non-restartable papers always start a new session if we have relaunched the window
  $log_metadata->create_new_record($current_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);

} elseif ($log_metadata->get_record() == false) { //load the data and check for no records
  // Check the time again, just in case the user realised they can add a post screen check to get around the first launch check.
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli, true);
  //we have no log_metadata record so make one
  $log_metadata->create_new_record($current_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);
}
$metadataID = $log_metadata->get_metadata_id();

// Foramtive or Progressive papers that have a duration set should use the timer.
if ($propertyObj->get_paper_type() == '0' || $propertyObj->get_paper_type() == '1') {
    if ($propertyObj->get_exam_duration() != null) {
        $allow_timing = true;
    }
// Summative exams only allow timing if ALL the modules of the paper allow it.
} else if ($propertyObj->get_paper_type() == '2'){
    $allow_timing = module_utils::modules_allow_timing($modIDs, $mysqli);
}

/*
* BP Determine the student's end_date timestamp for a summative exam that has been 'Started'.
* This is also used further down to make sure that the timer does not close the window if the exam session hasn't been 'started' by an invigilator
* If a summative exam session has been started  then record late answers in log_late
*/
$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2' and !$is_question_preview_mode) {
  // Has this lab had an end time set?
  $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
  $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

// Check for submissions after the end date and set them to save in log_late if we are not in preview_mode or a summative exam session as not been started
if ($is_preview_mode === false and time() > $propertyObj->get_end_date() and ($propertyObj->get_paper_type() == '1' or ($propertyObj->get_paper_type() == '2' and $paper_scheduled and $summative_exam_session_started === false))) {
  $propertyObj->set_paper_type('_late');
}

/*
* Save any posted answers
*
* Note: if Ajax saving is enabled: After a successful Ajax save the form is posted as the user moves to the next screen
*                                with dont_record set to true so this is not executed
*/
if (!$is_question_preview_mode) {
  if (!$is_first_launch and !$do_not_record) {
    record_marks($paperID, $mysqli, $propertyObj->get_paper_type(), $metadataID);
  }
}

/*
* Load up any previously submitted user answers from the appropriate log table(s)
*
* Note: If the user has gone passed the end of the exam (possible in some cases if security is relaxed)
*       records could exist in 2 logs the original paper type log and log_late
*
*/
$log = new log();
$l = $log->get_previous_answers($original_paper_type, $propertyObj->get_paper_type(), $metadataID, $do_restart, $current_screen);
$user_answers = $l['user_answers'];
$user_dismiss = $l['user_dismiss'];
$user_order = $l['user_order'];
$used_questions[$l['used_question']] = $l['used_question'];
$previous_duration = $l['previous_duration'];
$screen_pre_submitted = $l['screen_pre_submitted'];
$current_screen = $l['current_screen'];

if ($propertyObj->get_bidirectional() == 0 and $do_restart) {   // Linear
  $current_screen = $log_metadata->get_highest_screen() + 1;
  if ($current_screen > $no_screens) {
    $current_screen = $no_screens;
  }
}

// Load any reference materials.
$reference_materials = $propertyObj->load_reference_materials();
$max_ref_width = $propertyObj->get_max_reference_width($reference_materials);

require '../config/start.inc';

$url_mod = ($is_question_preview_mode) ? '&q_id=' . $get_qid . '&qNo=' . $q_number : '';

$render = new render($configObject);
$headerdata = array(
  'css' => array(
    '/css/start.css',
  ),
  'scripts' => array(
    '/js/jquery-1.11.1.min.js',
    '/js/jquery.validate.min.js',
    '/js/validation/jquery.paper.enhancedcalc.min.js',
    '/js/start.min.js',
  ),
  'metadata' => array(
    'pragma' => 'no-cache',
  ),
);
if ($propertyObj->get_paper_type() == '3') {
  $lang['title'] = $string['survey'];
} else {
 $lang['title'] = $string['assessment'];
}
if (Paper_utils::need_interactiveQ($screen_data, $current_screen, $mysqli)) {
  $headerdata['scripts'][] = '/js/html5.images.min.js';
  $headerdata['scripts'][] = '/js/qsharedf.js';
  $headerdata['scripts'][] = '/js/qlabelling.js';
  $headerdata['scripts'][] = '/js/qhotspot.js';
  $headerdata['scripts'][] = '/js/qarea.js';
}
if ($propertyObj->get_latex_needed() == 1) {
  $headerdata['scripts'][] = '/js/jquery-migrate-1.2.1.min.js';
  $headerdata['scripts'][] = '/tools/mee/mee/js/mee_src.js';
}
if($configObject->get_setting('core', 'paper_mathjax')) {
  $headerdata['scripts'][] = '/js/mathjax-config.min.js';
  $headerdata['scripts'][] = '/node_modules/mathjax/MathJax.js?config=TeX-MML-AM_HTMLorMML';
}
if($propertyObj->get_calculator()) {
  $headerdata['scripts'][] = '/js/jquery-ui-1.10.4.min.js';
  $headerdata['scripts'][] = '/js/jcalc98.min.js';
  $headerdata['scripts'][] = '/js/jcalc98uon.min.js';
  $headerdata['css'][] = '/css/jcalc98.css';
}
$render->render($headerdata, $lang, 'header.html');
?>

<script>
  var lang_string = <?php echo json_encode($jstring); ?>;
</script>

<?php

  /*
  *
  * Build the paper structure
  *
  */
  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';
  $tmp_questions_array = $propertyObj->build_paper($is_question_preview_mode, $get_qid);

  // Look for random questions and overwrite as needed
  $questions_array = array();
  $hidden_html = '';
  foreach ($tmp_questions_array as $question) {
    if ($question['q_type'] == 'random') {
      $question = Paper_utils::randomQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
      if ($current_screen == $question['screen']) {
        $hidden_html .= "\n<input type=\"hidden\" name=\"q" . $question['no_on_screen'] . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
      }
    } elseif ($question['q_type'] == 'keyword_based') {
      $question = Paper_utils::keywordQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
      if ($current_screen == $question['screen'] and $question['q_id'] != -1) {
        $hidden_html .= "\n<input type=\"hidden\" name=\"q" . $question['no_on_screen'] . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
      }
    }
    if ($question['q_type'] == 'enhancedcalc') {
      require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
      if (!isset($configObj)) {
        $configObj = Config::get_instance();
      }
      $question['object'] = new EnhancedCalc($configObj);
      $question['object']->load($question);
    }
    $questions_array[] = $question;
  }
  unset($tmp_questions_array);

  $unanswered = false;

  $incomplete_screens = get_unanswered_screens($no_screens, $screen_data, $user_answers, $questions_array, $paperID, $mysqli);

  // BP If the duration is set then show timer
  $timer_label = '';
  $timed = false;
  $special_needs_percentage = $userObject->get_special_needs_percentage();
  if ($allow_timing and $propertyObj->get_exam_duration() != null) {
    $timed = true;
    // Summative type. Time is only active in live.
    if (($propertyObj->get_paper_type() == '2' or $original_paper_type == 2) and $is_preview_mode === false) {

      // Has the student been allotted extra time by an invigilator?
      $student_object['user_ID'] = $userObject->get_user_ID();
      $student_object['special_needs_percentage'] = $special_needs_percentage;
      $log_extra_time = new LogExtraTime($log_lab_end_time, $student_object, $mysqli);

      // Do not time the exam if the invigilator has not clicked on the 'Start' button
      if ($summative_exam_session_started !== false) {
        $summative_timer  = new SummativeTimer($log_extra_time);
        $remaining_time   = $summative_timer->calculate_remaining_time_secs();
        $timer_label      = $string['timeremaining'] . ':';
      }

    } else {

      $timer          = new Timer($log_metadata, $propertyObj->get_exam_duration(), $special_needs_percentage);
      $start_datetime = $timer->get_start_datetime();

      if ($start_datetime === false) {
        $timer->start();
      }

      $remaining_time = $timer->calculate_remaining_time();
      $timer_label    = $string['timeremaining'] . ':';
    }
  }

  if($propertyObj->get_calculator()) {
    $render->render(null, null, 'jcalc98.html');
  }
  echo "<div id=\"maincontent\">\n";

  if ($current_screen < $no_screens) {
    echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $id . $url_mod . "\" autocomplete=\"off\">";
  } else {
    echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"finish.php?id=" . $id . $url_mod . "\" autocomplete=\"off\">";
  }
  echo $hidden_html;
  ?>
    <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<?php
  if (!$is_question_preview_mode) {
    echo "<tr><td valign=\"top\">\n";
    echo $top_table_html;
    echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div>';
    $question_offset = 0;
    if ($no_screens > 1) {
      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == $current_screen) {
          echo '<div class="scr_cur"';
        } else {
          if ($incomplete_screens[$i] == 1) {
            echo '<div class="scr_un"';
          } else {
            echo '<div class="scr_ans"';
          }
        }
        $no_questions = 0;
        if (isset($screen_data[$i])) {
          foreach ($screen_data[$i] as $screen_question) {
            $no_questions++;
          }
        }
        if ($no_questions == 1) {
          echo ' title="' . $no_questions . ' question">';
        } else {
          echo ' title="' . $no_questions . ' questions">';
        }

        if ($i < $current_screen and isset($screen_data[$i])) {
          foreach ($screen_data[$i] as $screen_question) {
            if ($screen_question[0] != 'info' ) {
              $question_offset++;
            }
          }
        }
        echo "$i</div>\n";
      }
      echo "<div style=\"clear:both\"></div>\n";


      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == $current_screen) {
          echo '<div class="scr_arrow"></div>';
        } else {
          echo '<div class="scr_spacer"></div>';
        }
      }

    }
    echo '</td>';
    echo $logo_html;
  } else {
    echo '<tr><td>';
  }

  $midexam_clarification = $configObject->get_setting('core', 'summative_midexam_clarification');

  if ($propertyObj->get_paper_type() === '3') {
    $calculator = 0;
  } else {
    $calculator = $propertyObj->get_calculator();
  }

  if (in_array('students', $midexam_clarification)) {
    $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
    echo $exam_announcementObj->display_student_announcements();
  }

  // Start displaying questions
  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";

  // Display each question
  foreach ($questions_array as &$question) {
    // Previous question type
    $previous_q_type = $question['q_type'];

    // Question not on this screen, don't display
    if ($question['screen'] != $current_screen) {
      continue;
    }

    // Flag original for telling if this is a linked question, since this flag is abandoned, set to 0
    $is_enhancedcalc = 0;
    // refer to all questions on displayed question
    $question['paper_questions'] = &$questions_array;
    if ($screen_pre_submitted == 1 and $q_displayed == 0) {
      echo "<tr style=\"display:none\" id=\"unansweredkey\">"
        . "<td colspan=\"2\"><span class=\"unans\">&nbsp;&nbsp;&nbsp;&nbsp;</span> "
        . $string['unansweredquestion']
        . "</td></tr>\n";
    }

    // Attempt to display paper prolog
    if ($q_displayed == 0 and $current_screen == 1 and $propertyObj->get_paper_prologue() != '') {
      echo '<tr><td colspan="2" style="padding:20px; text-align:justify">'
        . $propertyObj->get_paper_prologue()
        . '</td></tr>';
    }

    if ($q_displayed == 0 and $question['theme'] == '') {
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    }

    display_question($configObject, $question, $propertyObj->get_paper_type(), $calculator, $current_screen, $previous_q_type, $question_no, $user_answers, $unanswered);

    $q_displayed++;
  }

  // End of questions display
  echo "</table></td></tr>\n<tr><td>\n<br />\n";

  $current_screen++;
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($current_screen - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
  echo "<input type=\"hidden\" id=\"button_pressed\" name=\"button_pressed\" value=\"next\" />\n";
  echo "<input type=\"hidden\" id=\"randomPageID\" name=\"randomPageID\" value=\"\" />\n";
  echo "<input type=\"hidden\" id=\"isEnhancedCalc\" name=\"isEnhancedCalc\" value=\"{$is_enhancedcalc}\" />\n";
  echo "<input type=\"hidden\" name=\"refpane\" id=\"refpane\" value=\"{$refpane}\" />\n";

  if ($is_question_preview_mode === true) {
    $submitype = "preview";
  } elseif ($propertyObj->get_bidirectional() == 0) {
    $submitype = "linear";
  } else {
    $submitype = "bidirectional";
  }
  if ($is_question_preview_mode) {
    echo "<input type=\"hidden\" id=\"mode\" name=\"mode\" value=\"preview\" />\n";
  } else {
    if ($is_preview_mode) {
      echo "<input type=\"hidden\" id=\"mode\" name=\"mode\" value=\"preview\" />\n";
    }
    if ($current_screen > $no_screens) {
      echo "<div class=\"callout\">\n<div id=\"calloutTxt\">" . $string['finishnote'] . "</div><b class=\"notch\"></b></div>\n";
    } elseif ($propertyObj->get_bidirectional() == 0) {
      echo "<div class=\"callout\">\n<div id=\"calloutTxt\">" . sprintf($string['pleasecomplete'], $current_screen) . "</div><b class=\"notch\"></b></div>\n";
    }
  }

  echo '<div id="saveError"><img src="' . $configObject->get('cfg_root_path') . '/artwork/no_save.png" width="60" height="60" alt="Warning" /> <div><span style="color:#C42828; font-weight:bold">' .  $string['savefailed'] . '</span><br />' . $string['tryagain'] . '</div></div>';

  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff')) and $is_question_preview_mode) {
    echo "<input id=\"finish\" type=\"button\" value=\"" . $string['finish'] . "\" />";
  } else {
    echo $bottom_html;
    ?>
    <span style="color:white">
    <?php
    if ($propertyObj->get_exam_duration() != null) {
      echo $timer_label;
    }

    ?>
    <span id="theTime" type="text" class="thetime"></span>
    </span>
    <?php
    echo '</td><td align="right">';

    echo '<span id="savemsg"></span>';
    if ($propertyObj->get_bidirectional() == 1 and $no_screens > 1) {
      if ($current_screen > 2) {
        echo "<input id=\"previous\" type=\"button\" value=\"&lt; " . $string['screen'] . " " . ($current_screen - 2) . "\" />";
      }
      if (in_array($original_paper_type, array('0', '1', '2'))) {
        echo '<select name="jumpscreen" id="jumpscreen">';
        for ($i = 1; $i <= $no_screens; $i++) {
          $selected = $i == ($current_screen - 1) ? ' selected' : '';
          echo "<option value=\"$i\"$selected>$i</option>";
        }
        echo '</select>';
      }
    }
    if ($current_screen > $no_screens) {
      echo "<input id=\"finish\" type=\"button\" value=\"" . $string['finish'] . "\" />";
    } else {
      echo "<input id=\"next\" type=\"button\" value=\"" . $string['screen'] . " " . ($current_screen) . " &gt;\" />";
    }
    echo '</td></tr></table>';
  }
?>
</td></tr></table>

<textarea id="save_failed" name="save_failed" style="display:none"></textarea>

</form>
</div>
<div id="overlay">
  <div id="submit_dialog" class="dialogs">
    <div id="submit_dialog_icon"><img src="../artwork/question_mark_64.png" width="64" height="64" alt="?" /></div><p id="submit_dialog_msg"></p>
    <div id="submit_dialog_buttons"><input type="button" name="dialog_ok" id="dialog_ok" class="ok" value="OK" /><input type="button" name="dialog_cancel" id="dialog_cancel" class="cancel" value="Cancel" />&nbsp;&nbsp;</div>
  </div>
  <div id="enhancedcalc_warning" class="dialogs">
    <div id="enhancedcalc_warning_icon">
        <img src="../artwork/question_mark_64.png" width="64" height="64" alt="?" />
    </div>
    <p id="enhancedcalc_warning_msg"></p>
    <div id="enhancedcalc_warning_buttons">
        <input type="button" name="dialog_cancel" id="enhancedcalc_warning_cancel" value="Go back" />
        <input type="button" name="dialog_ok" id="enhancedcalc_warning_ok" value="Pass" />
        &nbsp;&nbsp;
    </div>
  </div>
</div>
<div id="info_overlay">
  <div id="info_submit_dialog">
    <div id="info_submit_dialog_icon"><img src="../artwork/question_mark_64.png" width="64" height="64" alt="?" /></div><p id="info_submit_dialog_msg"></p>
    <div id="info_submit_dialog_buttons"><input type="button" name="info_dialog_ok" id="info_dialog_ok" class="ok" value="OK" /></div>
  </div>
</div>
<div id="paper"
     data-pid="<?php echo $id; ?>"
     data-urlmod="<?php echo html_entity_decode($url_mod); ?>"
     data-submittype="<?php echo $submitype; ?>"
     data-refcount="<?php echo count($reference_materials); ?>"
     data-savefreq="<?php echo (($configObject->get_setting('core', 'paper_autosave_frequency') + rand(-5,5)) * 1000); ?>"
     data-savetimeout="<?php
       // Set the time out of one requst to be the maximum total time plus 5s for network latency
       // PHP handles normal timeouts. This is just to make sure the user won't wait forever if somthing
       // weird happens.
       $settimeout = $configObject->get_setting('core', 'paper_autosave_settimeout');
       $retrylimit = $configObject->get_setting('core', 'paper_autosave_retrylimit');
       $backofffactor = $configObject->get_setting('core', 'paper_autosave_backoff_factor');
       echo ceil((($retrylimit * $backofffactor * $settimeout) + $settimeout + 5)) * 1000; ?>"
     data-saveretry="<?php echo $retrylimit; ?>"
     data-timed="<?php echo $timed; ?>"
     >
</div>
<div id="css"
     data-bgcolor="<?php echo $bgcolor; ?>"
     data-fgcolor="<?php echo $fgcolor; ?>"
     data-font="<?php echo $font; ?>"
     data-textsize="<?php echo $textsize; ?>"
     data-unanswered_color="<?php echo $unanswered_color; ?>"
     data-themecolor="<?php echo $themecolor; ?>"
     data-marks_color="<?php echo $marks_color; ?>"
     data-dismiss_color="<?php echo $dismiss_color; ?>"
     data-max_ref_width="<?php echo $max_ref_width; ?>"
     data-special_needs="<?php echo $userObject->is_special_needs(); ?>"
     >
</div>
<div id="user"
     data-student="<?php echo $userObject->has_role('Student'); ?>"
     data-remaining_time="<?php echo $remaining_time; ?>"
     >
</div>
<?php

if (count($reference_materials) > 0) {
  $top = 0;
  $ref_no = 0;
  foreach ($reference_materials as $reference_material) {
    echo "<div class=\"refhead\" id=\"refhead" . $ref_no . "\" onclick=\"changeRef(" . $ref_no . ")\" style=\"top:{$top}px\">" . $reference_material['title'] . "</div>\n";
    echo "<div class=\"framecontent\" id=\"framecontent" . $ref_no . "\" style=\"top:" . (31 + $top) . "px\">\n" . $reference_material['material'] . "</div>\n";
    $top += 31;
    $ref_no++;
  }
  echo "<script>\n";
  echo "  changeRef(" . $refpane . ");\n";
  echo "</script>\n";
}
$mysqli->close();

if ($unanswered) {
  echo "<script>\n";
  echo "  $('#unansweredkey').show();\n";
  echo "</script>\n";
}

$render->render(array(), array(), 'footer.html');