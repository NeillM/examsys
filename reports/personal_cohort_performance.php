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
require_once '../include/calculate_marks.inc';

require_once '../classes/paperproperties.class.php';
require_once '../classes/results_cache.class.php';

$paperID  = check_var('paperID', 'GET', true, false, true);
$userID   = check_var('userID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);

if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$log_type = $propertyObj->get_paper_type();

$results_cache = new ResultsCache($mysqli);

$medians       = $results_cache->get_median_question_marks_by_paper($paperID);

$student_marks = $results_cache->get_student_question_marks_by_paper($userID, $log_type, $paperID);

$old_q_id           = 0;
$old_display_pos    = -1;
$old_q_type         = '';
$old_option_text    = array();
$old_correct        = array();
$old_display_method = '';
$old_score_method   = '';
$old_marks          = 0;
$tmp_exclude        = '';
$total_marks        = 0;

$questions_marks    = array();

// Get the questions (if any).
$result = $mysqli->prepare("SELECT theme, ownerID, p_id, q_id, q_type, screen, leadin, scenario, option_text, o_media, correct, display_method, score_method, q_media, q_media_width, q_media_height, marks_correct, marks_incorrect, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_last_edited, display_pos, status, correct_fback, feedback_right, locked, settings FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos, o_id");
$result->bind_param('i', $paperID);
$result->execute();
$result->store_result();
$result->bind_result($theme, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $scenario, $option_text, $o_media, $correct, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $marks_correct, $marks_incorrect, $display_last_edited, $display_pos, $status, $correct_fback, $feedback_right, $locked, $settings);
while ($result->fetch()) {
  if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
    $question_marks[$old_q_id] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);

    $old_correct      = array();
    $old_option_text  = array();
  }
  
  $old_q_id           = $q_id;
  $old_display_pos    = $display_pos;
  $old_q_type         = $q_type;
  $old_option_text[]  = $option_text;
  $old_correct[]      = $correct;
  $old_marks          = $marks_correct;
  $old_display_method = $display_method;
  $old_score_method   = $score_method;
}
$result->close();

$question_marks[$old_q_id] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php $string['personalcohortperformance'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style>
body {font-size:90%}
li {padding-bottom:10px}
</style>
</head>
<body>

<ol>
<?php

// Get the questions on the paper
$q_no = 1;

$result = $mysqli->prepare("SELECT q_id, theme, leadin, q_type FROM questions, papers WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type != 'info' ORDER BY screen, display_pos");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $theme, $leadin, $q_type);
while ($result->fetch()) {
  echo "<li>$leadin";
  
  if (substr($leadin, -4) != '</p>' and substr($leadin, -6) != '</div>') {
    echo '<br /><br />';
  }
  
  echo '<img src="draw_barchart.php?tpm=' . $question_marks[$q_id] . '&mark=' . $student_marks[$q_id] . '&median=' . $medians[$q_id] . '" width="400" height="85" alt="" />';
  echo "</li>\n";
}
$result->close();


$mysqli->close();
?>
</ol>
</body>
</html>
