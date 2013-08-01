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
* This script presents a list of all the unique entries (words) entered for a particular blank in
* a fill-in-the-blank question with textboxes. The interface allows staff to tick correct alternative
* spellings and have the system remark student scripts (only works with summative exams).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require_once '../classes/logger.class.php';
require_once '../classes/paperproperties.class.php';
require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';

$q_id  = check_var('q_id', 'GET', true, false, true);
$paperID  = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli);

if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$paper_type = $propertyObj->get_paper_type();

// Read question from database.
$result = $mysqli->prepare("SELECT leadin, settings FROM questions WHERE q_id = ?");
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($leadin, $settings);
$result->fetch();
$result->close();

// Read user answers from log.
$log_answers = array();
if ($paper_type == '0') {
  $result = $mysqli->prepare("(SELECT 0 AS type, l.id, l.mark, l.user_answer FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ?) UNION ALL (SELECT 1 AS type, l.id, l.mark, l.user_answer FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ?)");
  $result->bind_param('iissiiss', $q_id, $paperID, $_GET['startdate'], $_GET['enddate'], $q_id, $paperID, $_GET['startdate'], $_GET['enddate']);
} else {
  $result = $mysqli->prepare("SELECT $paper_type AS type, l.id, l.mark, l.user_answer FROM log$paper_type l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ?");
  $result->bind_param('iiss', $q_id, $paperID, $_GET['startdate'], $_GET['enddate']);
}
$result->execute();
$result->bind_result($type, $id, $mark, $user_answer);
while ($result->fetch()) {
  if ($mark != '') {
    $answer_obj = new enhancedcalc($configObject);
    $answer_obj->setuseranswer($user_answer);
    $answer_obj->setsettings($settings);
    $dist = $answer_obj->get_answer_distance();
    if ($dist === false) $dist = $string['unknown'];
    $log_answers[$dist] = array('paper_type' => $type, 'id' => $id, 'answer_obj' => $answer_obj);
  }
}
$result->close();

// Sort by distance
asort($log_answers);

// Get any existing overrides
// $sql = 'SELECT * FROM marking_override WHERE ';

$question_obj = new enhancedcalc($configObject);
$question_obj->setsettings($settings);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['remark'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#F1F5FB}
    th {font-weight:normal; color:#001687; text-align: left; vertical-align: bottom}
    .shortcolumn {width: 50px}
    .longcolumn {width: 90px}
    .alpha {padding-left: 8px;}
    .omega {padding-right: 8px;}
    .o {text-align:right; padding-right:10px}
    .c1 {width:65px; text-align:center}
    .c2 {width:250px}
    .r1 {background-color:white}
    .r2 {background-color:#B3C8E8}
    .msg {text-align:justify; margin:5px; font-size:90%; color:#001687}
  </style>

  <script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript">
    function toggle(objectID) {
      if (document.getElementById(objectID).className == 'r2') {
        document.getElementById(objectID).className = 'r1';
      } else {
        document.getElementById(objectID).className = 'r2';
      }
    }

    function resizeList() {
      var winW = 630, winH = 460;
      if (document.body && document.body.offsetWidth) {
        winW = document.body.offsetWidth;
        winH = document.body.offsetHeight;
      }
      if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
        winW = document.documentElement.offsetWidth;
        winH = document.documentElement.offsetHeight;
      }
      if (window.innerWidth && window.innerHeight) {
        winW = window.innerWidth;
        winH = window.innerHeight;
      }
      winH -= 160;
      document.getElementById('list').style.height = winH + 'px';
    }

    var doSuccess = function (data) {
      if (data != 'OK') {
        alert(langStrings['saveerror']);
        return false;
      }

      alert('OK!');

      // if ($('#mark' + id).val() == 'NULL') {
      //   $('#ans_' + id).removeClass('marked').effect("highlight", {}, 1500);;
      // } else {
      //   $('#ans_' + id).addClass('marked').effect("highlight", {}, 1500);;
      // }

    }


    var doError = function () {
      alert(langStrings['saveerror']);
    }

    var saveRow = function (e) {
      var logID = $(this).data('logid');
      var newMark = $('input[name=mark_' + logID + ']:checked').val();
      var reason = $('#reason_' + logID).val();
      var logType = $('#log_type_' + logID).val();

      if (typeof newMark == 'undefined') {
        alert('<?php echo $string['nomarkmsg'] ?>');
      } else {
        $.post('../ajax/reports/save_enhancedcalc_override.php',
          {
            log_id: logID,
            q_id: $('#q_id').val(),
            paper_id: $('#paper_id').val(),
            marker_id: $('#marker_id').val(),
            mark_type: newMark,
            reason: reason,
            log: logType
          },
          doSuccess
        ).fail(doError);
      }
    }

    $(function () {
      resizeList();
      $(window).resize(resizeList);

       $.ajaxSetup({ timeout: 3000 });
       $('#list').ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
         doError();
       });

      $('.save-row').click(saveRow);
    })

    langStrings = {'saveerror': '<?php echo $string['saveerror'] ?>'};
  </script>
</head>

<body>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id'] . '&paperID=' . $_GET['paperID']; ?>">
  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/dictionary.png" width="32" height="32" alt="Word List" /></td><td style="background-color:white; font-size:150%; color:#5582D2; border-bottom:1px solid #CCD9EA"><strong><?php echo $string['useranswers']; ?></strong></td></tr>
  </table>

  <div class="msg"><?php echo $string['msg']; ?></div>

  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
  <tr>
<?php
$q_vars = $question_obj->get_question_vars();
$alpha = ' alpha';
foreach ($q_vars as $var => $dummy) {
?>
  <th class="shortcolumn<?php echo $alpha ?>"><?php echo $var ?></th>
<?php
  if ($alpha != '') {
    $alpha = '';
  }
}
?>
      <th class="longcolumn"><?php echo $string['useranswer']; ?></th>
      <!-- <th class="shortcolumn"><?php echo $string['units']; ?></th> -->
      <th class="longcolumn"><?php echo $string['correctans']; ?></th>
      <th class="longcolumn"><?php echo $string['distance']; ?></th>
      <th class="shortcolumn"><?php echo $string['fullmarks']; ?></th>
      <th class="shortcolumn"><?php echo $string['partialmarks']; ?></th>
      <th class="shortcolumn"><?php echo $string['nomarks']; ?></th>
      <th><?php echo $string['reason']; ?></th>
      <th class="omega">&nbsp;</th>
    </tr>
  </table>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:0px 4px 8px 4px; font-size:90%" id="list">
  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
<?php
foreach ($log_answers as $distance => $answer) {
  echo '<tr>';
  $u_vars = $answer['answer_obj']->get_user_vars();
  foreach ($u_vars as $label => $value) {
    echo "<td class=\"shortcolumn\">$value</td>";
  }
  echo "<td class=\"longcolumn\">{$answer['answer_obj']->get_user_answer_raw()}</td>";
  // echo '<td class=\"shortcolumn\"></td>';
  echo "<td class=\"longcolumn\">{$answer['answer_obj']->get_real_answer()}</td>";
  echo '<td class="longcolumn">' . htmlentities($distance) . '</td>';
?>
  <td class="shortcolumn"><input type="radio" name="mark_<?php echo $answer['id'] ?>" value="correct" /></td>
  <td class="shortcolumn"><input type="radio" name="mark_<?php echo $answer['id'] ?>" value="partial" /></td>
  <td class="shortcolumn"><input type="radio" name="mark_<?php echo $answer['id'] ?>" value="incorrect" /></td>
  <td><input type="textbox" id="reason_<?php echo $answer['id'] ?>" name="reason_<?php echo $answer['id'] ?>" size="30" maxlength="255" /></td>
  <td>
    <button id="save_<?php echo $answer['id'] ?>" type="button" data-logid="<?php echo $answer['id'] ?>" class="save-row"><?php echo $string['save'] ?></button>
    <input type="hidden" id="log_type_<?php echo $answer['id'] ?>" name="log_type_<?php echo $answer['id'] ?>" value="<?php echo $answer['paper_type'] ?>" />
  </td>
  </tr>
<?php
}
?>
</table>
</div>
<div style="text-align:center"><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" onclick="window.close();" /></div>

  <input type="hidden" id="q_id" name="q_id" value="<?php echo $q_id ?>" />
  <input type="hidden" id="paper_id" name="paper_id" value="<?php echo $paperID ?>" />
  <input type="hidden" id="marker_id" name="marker_id" value="<?php echo $userObject->get_user_ID() ?>" />
</form>
</body>
</html>