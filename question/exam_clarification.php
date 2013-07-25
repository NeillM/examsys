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

require '../include/admin_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/exam_announcements.class.php';
require_once '../classes/paperutils.class.php';
require_once '../classes/questionutils.class.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$q_id = check_var('q_id', 'REQUEST', true, false, true);
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
if (!QuestionUtils::question_exists($q_id, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli);
  
if (isset($_POST['submit'])) {
  $screenNo = check_var('screenNo', 'POST', true, false, true);
  $questionNo = check_var('questionNo', 'POST', true, false, true);
  $msg = check_var('msg', 'POST', true, false, true);

  $exam_announcementObj->replace_announcement($q_id, $questionNo, $screenNo, $msg);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['midexamclarification'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
</head>
<body onload="window.close()">
</body>
</html>
exit();
<?php
} else {
  $screenNo = check_var('screenNo', 'GET', true, false, true);
  $questionNo = check_var('questionNo', 'GET', true, false, true);

  $exam_announcements = $exam_announcementObj->get_announcements();
  
  if (isset($exam_announcements[$q_id]['msg'])) {
    $msg = $exam_announcements[$q_id]['msg'];
  } else {
    $msg = '';
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['midexamclarification'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%; text-align:center}
    h1 {text-align:left; font-size:150%; margin-left:4px; font-weight:normal}
  </style>
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script language="JavaScript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script language="JavaScript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script language="JavaScript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config_announcements.js"></script>
  <script language="JavaScript">
    $(document).ready(function() {
      var new_height = $(window).height() - 100;
      $('#msg').height(new_height);
    });
  </script>
</head>

<body>
<form name="myform" id="myform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<h1 class="dkblue_header"><?php echo sprintf($string['questionscreen'], $questionNo, $screenNo); ?></h1>
<textarea class="mceEditor" id="msg" name="msg" cols="80" rows="4" style="width:100%; height:340px"><?php echo htmlspecialchars($msg, ENT_NOQUOTES); ?></textarea><br />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" style="width:140px" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="window.close()" style="width:80px" /></div>
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="q_id" value="<?php echo $q_id; ?>" />
<input type="hidden" name="screenNo" value="<?php echo $screenNo; ?>" />
<input type="hidden" name="questionNo" value="<?php echo $questionNo; ?>" />
</form>

</body>
</html>
<?php
}
?>
