<?php
require_once '../include/invigilator_auth.inc';
require_once '../classes/exam_announcements.class.php';

$paperID = $_GET['paperID'];
$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli);

$exam_announcements = $exam_announcementObj->get_announcements();

if (count($exam_announcements) == 0) {
  echo '<span style="color:#C0C0C0; font-size:200%; font-weight:bold">Exam question clarifications</span>';
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