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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require_once '../classes/stateutils.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli, $configObject->get('cfg_root_path') . '/reports/textbox_header.php');

$paperID    = check_var('paperID', 'GET', true, false, true);
$q_id       = check_var('q_id', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);
$ws         = check_var('ws', 'GET', true, false, true);
$phase      = check_var('phase', 'GET', true, false, true);

// Check the paper actually exists.
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Check the question exists.
if (!QuestionUtils::question_exists($q_id, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function displayMarks($id, $default, $log_record_id, $log, $halfmarks, $tmp_username, $marks, $string) {
  $html = '<select id="mark' . $id . '" name="mark' . $id . '" class="tbmark"><option value="NULL"></option>';
  $inc = 1;
  if ($halfmarks == true) $inc = 0.5;
  for ($i=0; $i<=$marks; $i+=$inc) {
    $display_i = $i;
    if ($i == 0.5) {
      $display_i = '&#189;';
    } elseif ($i - floor($i) > 0) {
      $display_i = floor($i) . '&#189;';
    }
    if ($i == $default and is_numeric($default)) {
      $html .= "<option value=\"$i\" selected>$display_i</option>";
    } else {
      $html .= "<option value=\"$i\">$display_i</option>";
    }
  }
  $html .= <<< HTML
</select>&nbsp;<span style="color:black">{$string['marks']}</span><br />&nbsp;
<input type="hidden" id="logrec{$id}" name="logrec{$id}" value="{$log_record_id}">
<input type="hidden" id="log{$id}" name="log{$id}" value="{$log}">
<input type="hidden" id="username{$id}" name="username{$id}" value="{$tmp_username}">
HTML;
  return $html;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Textbox Marking</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%}
    td {line-height:150%; text-align:justify}
    .heading {background-color:#EBEADB; color:black}
    #answers {
      width: 100%;
      margin-right:10px
    }
    .number {
      vertical-align:top;
      text-align:right;
      border-bottom:1px solid #CBC7B8;
      width: 30px;
    }
  <?php
  if (isset($state['hidemarked']) and $state['hidemarked'] == 'true') {
    echo ".marked {color:#808080;display:none}\n";
  } else {
    echo ".marked {color:#808080}\n";
  }
  ?>
  </style>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui.1.8.16.min.js"></script>
  <script type="text/javascript" src="../js/jquery.textbox.js"></script>
  <script type="text/javascript" src="../js/ie_fix.js"></script>
  <script type="text/javascript">
    langStrings = {'saveerror': '<?php echo $string['saveerror'] ?>'};
  </script>
</head>

<body style="margin:0px">
<form id="content" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $paperID; ?>&amp;q_id=<?php echo $_GET['q_id']; ?>&amp;startdate=<?php echo $startdate; ?>&amp;enddate=<?php echo $enddate; ?>&amp;module=<?php echo $_GET['module']; ?>&amp;folder=<?php echo $_GET['folder']; ?>&amp;ws=<?php echo $ws; ?>&amp;phase=<?php echo $phase; ?>&amp;action=mark" method="post">
<input type="hidden" id="marker_id" name="marker_id" value="<?php echo $userObject->get_user_ID(); ?>" />
<input type="hidden" id="paper_id" name="paper_id" value="<?php echo $paperID; ?>" />
<input type="hidden" id="q_id" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input type="hidden" id="phase" name="phase" value="<?php echo $phase; ?>" />
<?php
// Get some paper properties
if ($result = $mysqli->prepare("SELECT paper_type FROM properties WHERE property_id=?")) {
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_type);
  $result->fetch();
  $result->close();
} else {
  display_error("Properties Query Error","SELECT paper_type FROM properties WHERE property_id=$paperID");
  $mysqli->close();
}

// Get the marks for the question
if ($result = $mysqli->prepare("SELECT marks_correct, marks_incorrect, leadin, correct_fback FROM (questions, options) WHERE questions.q_id=options.o_id AND o_id = ? LIMIT 1")) {
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->bind_result($marks_correct, $marks_incorrect, $leadin, $correct_fback);
  $result->fetch();
  $result->close();
} else {
  display_error("Marks Query Error",$mysqli->close());
}

if ($phase == 2) {
  // Get the usernames of papers to second mark.
  $second_mark = array();

  $result = $mysqli->prepare("SELECT userID FROM textbox_remark WHERE paperID = ?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($remark_userID);
  while ($row = $result->fetch()) {
    $second_mark[] = $remark_userID;
  }
  $result->close();
}

$half_marks = true;
?>
<table id="answers" cellpadding="4" cellspacing="0" border="0">
<?php
  if ($paper_type == '0') {

    $sql = <<< SQL
SELECT 0 AS logtype, l.id, lm.userID, l.user_answer, t.mark
  FROM (log0 l, log_metadata lm, users u)
  LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND (u.roles = 'Student' OR u.roles = 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
  AND lm.started <= ?
UNION ALL
SELECT 1 AS logtype, l.id, lm.userID, l.user_answer, t.mark
  FROM (log1 l, log_metadata lm, users u)
  LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND (u.roles = 'Student' OR u.roles = 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
  AND lm.started <= ?
SQL;
    $result = $mysqli->prepare($sql);
    $result->bind_param('iiissiiiss', $phase, $paperID, $q_id, $startdate, $enddate, $phase, $paperID, $q_id, $startdate, $enddate);
  } else {
    $sql = <<< SQL
SELECT $paper_type AS logtype, l.id, lm.userID, l.user_answer, t.mark
FROM (log{$paper_type} l, log_metadata lm, users u)
LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
WHERE lm.paperID = ?
AND l.metadataID = lm.id
AND (u.roles = 'Student' OR u.roles = 'graduate')
AND u.id = lm.userID
AND l.q_id = ?
AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
AND lm.started <= ?;
SQL;

    $result = $mysqli->prepare($sql);
    $result->bind_param('iiiss', $phase, $paperID, $q_id, $startdate, $enddate);
  }
  $answer_no = 0;
  $result->execute();
  $result->store_result();
  $result->bind_result($logtype, $id, $tmp_userID, $user_answer, $student_mark);
  if ($result->num_rows == 0) {
    echo "<p>" . $string['nostudents'] . "</p>";
  }
  while ($result->fetch()) {
    if ($phase == 1 or ($phase == 2 and in_array($tmp_userID, $second_mark))) {
      $style = '';
      if (trim($user_answer) != '') {
        $answer_no++;
        if (is_numeric($student_mark)) {  // Marked previously so grey out.
           $style = ' class="marked"';
        }
        echo "<tr id=\"ans_" . $answer_no . "\"" . $style . "><td class=\"number\">$answer_no.</td><td style=\"border-bottom:1px solid #CBC7B8\">" . nl2br($user_answer) . "<br />" . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID, $marks_correct, $string) . "</td></tr>\n";
      } else {
        $answer_no++;
        if (is_numeric($student_mark)) {  // Marked previously so grey out.
          $style = ' class="marked"';
        }
        echo "<tr" . $style . "><td style=\"vertical-align:top; text-align:right; border-bottom:1px solid #CBC7B8\">$answer_no.</td><td style=\"border-bottom:1px solid #CBC7B8; color:#C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" />".$string['noanswer']."<br />" . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID, $marks_correct, $string) . "</td></tr>\n";
      }
    }
  }
  $result->close();
?>
</table>

<div align="center"><input type="hidden" name="answer_no" value="<?php echo $answer_no; ?>" />
<input type="hidden" name="paper_type" value="<?php echo $paper_type; ?>" />
<table cellpadding="0" cellspacing="0" border="0">
<?php
  echo '<tr><td><input onclick="javascript:window.top.location=\'./textbox_select_q.php?paperID=' . $_GET['paperID'] . '&startdate=' . $_GET['startdate'] . '&enddate=' . $_GET['enddate'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '&ws=1&phase=' . $phase . '&action=mark&repcourse=%\'" type="button" name="cancel" value="' . $string['ok'] . '" style="width:90px" /></td></tr>';
?>
</table>
</div>
</form>
</body>
</html>
