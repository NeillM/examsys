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
* Displays an HTML page in a suitable way that it could be printed
* with the intention of making a student answer booklet (i.e. only
* questions, no answers).
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/print_functions.inc';
require '../config/index.inc';
require_once '../include/errors.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';

$id = check_var('id', 'GET', true, false, true, param::ALPHANUM); // While it is an int, the numbers are too large for 32-bit PHP.

if (isset($_POST['sessionid'])) require '../include/marking_functions.inc';

$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

// Get how many screens make up the question paper.
$screen_data = array();
$row_no = 0;
$stmt = $mysqli->prepare("SELECT property_id, labs, paper_title, paper_type, paper_prologue, marking, screen, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, bidirectional, calendar_year, password FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND crypt_name=? AND papers.question=questions.q_id AND q_type != 'info' ORDER BY screen");
$stmt->bind_param('s', $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $labs, $paper_title, $paper_type, $paper_prologue, $marking, $screen, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $calendar_year, $password);
if ($stmt->num_rows == 0) {  // No record found, the paper can't exist
  $stmt->close();
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
while ($stmt->fetch()) {
  $row_no++;
  $no_screens = $screen;
  if (!isset($screen_data[$no_screens])) {
    $screen_data[$no_screens] = 1;
  } else {
    $screen_data[$no_screens]++;
  }
  if ($row_no == 1) {
    $original_paper_type = $paper_type;
  }
}
$stmt->free_result();
$stmt->close();

$current_screen = 1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
  <?php
  if ($paper_type == '3') {
    echo "<title>" . $string['survey'] . "</title>\n";
  } else {
    echo "<title>" . $string['assessment'] . "</title>\n";
  }
  ?>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="imagetoolbar" content="no">
  <meta http-equiv="imagetoolbar" content="false">
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="pragma" content="no-cache" />

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/print.css" />
  <link rel="stylesheet" type="text/css" href="../css/html5.css" />

  <style type="text/css">
  <?php
    if (isset($_GET['break']) and $_GET['break'] == 1) {
      echo ".qtable {page-break-after:always}\n";
    } else {
      echo ".qtable {page-break-after:auto}\n";
    }
  ?>
  </style>
    <script id="rogoconfig"
            data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>"
            data-three="<?php echo $configObject->get_setting('core', 'paper_threejs'); ?>">
    </script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src='../js/printinit.min.js'></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
  $texteditorplugin->display_header();
  // Check if any 3d file types are enabled and render js.
  threed_handler::render_js($string);
?>
</head>
<body>

  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td valign="top">
<?php
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  echo '</td><td align="right" width="167">' . $logo_html . '</td></tr></table>';

  $user_answers = array();
  $previous_duration = 0;

  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';
  $hide_notes = param::optional('hidenotes', false, param::BOOLEAN, param::FETCH_GET);
  $tmp_questions_array = $propertyObj->build_paper(false, null, null, $hide_notes);
  //look for braching and random questions and overwrite as needed
  $questions_array = array();
  $tmp_q_no = 0;
  foreach ($tmp_questions_array as &$question) {
    if ($question['q_type'] != 'info') {
      $tmp_q_no++;
    }
    if ($question['q_type'] == 'random') {
      $questions_array[] = $propertyObj->randomQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
    } elseif ($question['q_type'] == 'keyword_based') {
      $questions_array[] = $propertyObj->keywordQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
    } else {
      $questions_array[] = $question;
    }
  }
  unset($tmp_questions_array);

  //display the questions
  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\" class=\"qtable\">\n";
  echo "<col width=\"40\"><col>\n";
  foreach($questions_array as &$question) {
    if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    display_question($question, $paper_type, $current_screen, $previous_q_type, $question_no, $question_offset, $user_answers);
    $previous_q_type = $question['q_type'];
    $q_displayed++;
  }
  echo "</table>\n";

  $mysqli->close();

?>
<?php
  // JS utils dataset.
  $jsdataset['name'] = 'jsutils';
  $jsdataset['attributes']['xls'] = json_encode($string);
  $render = new render($configObject);
  $render->render($jsdataset, array(), 'dataset.html');
  // Dataset.
  $miscdataset['name'] = 'dataset';
  $miscdataset['attributes']['language'] = $language;
  $miscdataset['attributes']['rootpath'] = $cfg_root_path;
  $render->render($miscdataset, array(), 'dataset.html');

  $render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');
?>
</body>
</html>
