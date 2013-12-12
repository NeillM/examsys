<?php
require_once '../include/invigilator_auth.inc';
require_once '../classes/exam_announcements.class.php';

$paperID = $_GET['paperID'];
if (!isset($string)) $string = array();

$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);

$exam_announcements = $exam_announcementObj->get_announcements();

if (count($exam_announcements) == 0) {
  echo '<span class="blankclarification">' . $string['examquestionclarifications'] . '</span>';
  exit();
}

echo "<table><tbody>";
foreach ($exam_announcements as $exam_announcement) {
  $msg = $exam_announcement['msg'];
  if (substr_count($msg, '<p>')) {
    $msg = str_replace('<p>', '', $msg);
    $msg = str_replace('</p>', '', $msg);
  }
  echo "<tr><td class=\"q_no\">Q" . $exam_announcement['q_number'] . "</td><td class=\"q_msg\">" . $msg . "</td></tr>";
}
echo "</tbody></table>";
?>