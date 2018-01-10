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
$user_answers = array();
$previous_duration = 0;
$screen_pre_submitted = 0;

// Get users previous answers from the log.
if ($propertyObj->get_paper_type() == '_late') {
  // If we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below
  $log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log$original_paper_type WHERE metadataID = ?");
  $log_data->bind_param('i', $metadataID);
  $log_data->execute();
  $log_data->store_result();
  $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
  $user_answers = array();
  $used_questions[$log_q_id] = $log_q_id;
  while ($log_data->fetch()) {
    $user_answers[$log_screen][$log_q_id] = $log_user_answer;
    $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
    $user_order[$log_screen][$log_q_id] = $option_order;
    // Bump up the current screen if restarting
    if ($do_restart and $log_screen > $current_screen) {
      $current_screen = $log_screen;
    }
    if ($log_screen == $current_screen) {
      $previous_duration = $log_duration;
      $screen_pre_submitted = 1;
    }
  }
  $log_data->close();
}
// Get user answers from whichever log is pointed to by log$paper_type
if ($propertyObj->get_paper_type() == '5') {
  // There is no user answer in Log5 (offline papers) so put NULL instead.
	$log_data = $mysqli->prepare("SELECT id, q_id, NULL AS user_answer, NULL AS duration, NULL AS screen, NULL AS dismiss, NULL AS option_order FROM log" . $propertyObj->get_paper_type() . " WHERE metadataID = ? ORDER BY id");
} else {
	$log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log" . $propertyObj->get_paper_type() . " WHERE metadataID = ? ORDER BY id");
}
$log_data->bind_param('i', $metadataID);
$log_data->execute();
$log_data->store_result();
$log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
if ($log_data->num_rows > 0) {
  while ($log_data->fetch()) {
    $user_answers[$log_screen][$log_q_id] = $log_user_answer;
    $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
    $user_order[$log_screen][$log_q_id] = $option_order;
    $used_questions[$log_q_id] = $log_q_id;

    // Bump up the current screen if restarting
    if ($do_restart and $log_screen > $current_screen) {
      $current_screen = $log_screen;
    }
    if ($log_screen == $current_screen) {
      $previous_duration = $log_duration;
      $screen_pre_submitted = 1;
    }
  }
}
$log_data->close();

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
echo "<!DOCTYPE html>\n<html>\n<head>\n";

$url_mod = ($is_question_preview_mode) ? '&q_id=' . $get_qid . '&qNo=' . $q_number : '';
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="pragma" content="no-cache" />
<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/start.css" />
<?php
if ($propertyObj->get_paper_type() == '3') {
  echo "<title>" . $string['survey'] . "</title>\n";
} else {
  echo "<title>" . $string['assessment'] . "</title>\n";
}
?>

<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../js/jquery.validate.min.js"></script>
<script type="text/javascript" src="../js/validation/jquery.paper.enhancedcalc.min.js"></script>

<?php if ($propertyObj->get_latex_needed() == 1) : ?>
<script type="text/javascript" src="../js/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<?php endif; ?>

<?php
  if (Paper_utils::need_interactiveQ($screen_data, $current_screen, $mysqli)) {
    $render = new render($configObject);
    $render->render_html5_js(json_encode($jstring));
  }

  echo $configObject->get('cfg_js_root');
?>

<script>
  var lang = <?php echo json_encode($jstring); ?>;
</script>
<script type="text/javascript" src="../js/start.min.js"></script>
<?php
$render = new render($configObject);
if($configObject->get_setting('core', 'paper_mathjax')) {
  $render->render(null, null, 'mathjax.html');
}
if($propertyObj->get_calculator()) {
  $render->render(null, null, 'jcalc98_header.html');
}
?>
</head>
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
  if ($is_question_preview_mode) {
    $question_data = $mysqli->prepare("SELECT
                                          1,
                                          q_type,
                                          q_id,
                                          score_method,
                                          display_method,
                                          settings,
                                          marks_correct,
                                          marks_incorrect,
                                          marks_partial,
                                          theme,
                                          scenario,
                                          leadin,
                                          correct,
                                          REPLACE(option_text,'\t','') AS option_text,
                                          q_media,
                                          q_media_width,
                                          q_media_height,
                                          o_media,
                                          o_media_width,
                                          o_media_height,
                                          notes,
                                          display_pos,
                                          q_option_order
                                      FROM
                                          papers, questions LEFT JOIN options ON questions.q_id = options.o_id
                                      WHERE
                                        paper = ? AND
                                        q_id = ? AND
                                        papers.question = questions.q_id
                                      ORDER BY
                                      display_pos,
                                      id_num");
    $question_data->bind_param('ii', $paperID, $get_qid);
  } else {
    $question_data = $mysqli->prepare("SELECT
                                            screen,
                                            q_type,
                                            q_id,
                                            score_method,
                                            display_method,
                                            settings,
                                            marks_correct,
                                            marks_incorrect,
                                            marks_partial,
                                            theme,
                                            scenario,
                                            leadin,
                                            correct,
                                            REPLACE(option_text,'\t','') AS option_text,
                                            q_media,
                                            q_media_width,
                                            q_media_height,
                                            o_media,
                                            o_media_width,
                                            o_media_height,
                                            notes,
                                            display_pos,
                                            q_option_order
                                        FROM
                                            papers, questions LEFT JOIN options ON questions.q_id = options.o_id
                                        WHERE
                                          paper = ? AND
                                          papers.question = questions.q_id
                                        ORDER BY
                                        display_pos,
                                        id_num");
    $tmp_pid = $paperID;
    $question_data->bind_param('i', $tmp_pid);
  }
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($screen, $q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;

  $q_no = 0;
  $assigned_number = 0;
  $no_on_screen = 0;
  $old_screen = 0;
  // Build the questions_array
  $tmp_questions_array = array();
  while ($question_data->fetch()) {
    if ($q_no == 0 or $tmp_questions_array[$q_no]['q_id'] != $q_id or $tmp_questions_array[$q_no]['display_pos'] != $display_pos) {
      $q_no++;
      if ($screen != $old_screen) {
        $no_on_screen = 0;
      }
      if ($q_type != 'info') {
        $assigned_number++;
        $no_on_screen++;
      }
      if (!is_null($q_number)) {
        $tmp_questions_array[$q_no]['assigned_number'] = $q_number;   // Preview mode, use the number that is passed in.
      } else {
        $tmp_questions_array[$q_no]['assigned_number'] = $assigned_number;
      }
      $tmp_questions_array[$q_no]['no_on_screen'] = $no_on_screen;
      $tmp_questions_array[$q_no]['screen'] = $screen;
      $tmp_questions_array[$q_no]['theme'] = trim($theme);
      $tmp_questions_array[$q_no]['scenario'] = trim($scenario);
      $tmp_questions_array[$q_no]['leadin'] = trim($leadin);
      $tmp_questions_array[$q_no]['notes'] = trim($notes);
      $tmp_questions_array[$q_no]['q_type'] = $q_type;
      $tmp_questions_array[$q_no]['q_id'] = $q_id;
      $tmp_questions_array[$q_no]['display_pos'] = $display_pos;
      $tmp_questions_array[$q_no]['score_method'] = $score_method;
      $tmp_questions_array[$q_no]['display_method'] = $display_method;
      $tmp_questions_array[$q_no]['settings'] = $settings;
      $tmp_questions_array[$q_no]['q_media'] = $q_media;
      $tmp_questions_array[$q_no]['q_media_width'] = $q_media_width;
      $tmp_questions_array[$q_no]['q_media_height'] = $q_media_height;
      $tmp_questions_array[$q_no]['q_option_order'] = $q_option_order;
      $tmp_questions_array[$q_no]['dismiss'] = '';
      $used_questions[$q_id] = 1;
    }
    $tmp_questions_array[$q_no]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
    $old_screen = $screen;
  }
  $question_data->close();

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

  $method = 'StartClock()';
  $timer_label = '';

  $special_needs_percentage = $userObject->get_special_needs_percentage();
  if ($allow_timing and $propertyObj->get_exam_duration() != null) {
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
        $method           = 'StartTimer(' . $remaining_time . ', true)';
        $timer_label      = $string['timeremaining'] . ':';
      }

    } else {

      $timer          = new Timer($log_metadata, $propertyObj->get_exam_duration(), $special_needs_percentage);
      $start_datetime = $timer->get_start_datetime();

      if ($start_datetime === false) {
        $timer->start();
      }

      $remaining_time = $timer->calculate_remaining_time();
      $method         = 'StartTimer(' . $remaining_time . ', true)';
      $timer_label    = $string['timeremaining'] . ':';
    }
  }

  if ($userObject->has_role('Student')) {
    echo '<body oncontextmenu="return false;"onload="' . $method . ';" onclose="KillClock();">';
  } else {
    echo '<body onload="' . $method . ';" onunload="KillClock();">';
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
    if ($propertyObj->get_paper_type() != '5') { // Do not allow saving for offline papers.
      echo "<input id=\"finish\" type=\"button\" value=\"" . $string['finish'] . "\" />";
    }
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
?>
</body>
</html>