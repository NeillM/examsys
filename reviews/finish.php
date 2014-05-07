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

require '../include/staff_auth.inc';
require '../include/reviews.inc';
require '../include/errors.inc';

check_var('id', 'GET', true, false, false);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
  
if ($stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid = ?")) {
  $stmt->bind_param('i', $userObject->get_user_ID());
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
}
$stmt->close();

$screen_data = array();
$stmt = $mysqli->prepare("SELECT property_id, paper_title, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, UNIX_TIMESTAMP(external_review_deadline) AS external_review_deadline, UNIX_TIMESTAMP(internal_review_deadline) AS internal_review_deadline FROM properties WHERE crypt_name = ?");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $external_review_deadline, $internal_review_deadline);
$stmt->fetch();
$stmt->close();

if ($bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
if ($fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
if ($textsize == 'NULL' or $textsize == '') $textsize = 90;
if ($marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
if ($themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
if ($labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;

if ($userObject->has_role('External Examiner')) {
  $review_type = 'External';
} else {
  $review_type = 'Internal';
  $external_review_deadline = $internal_review_deadline; //this is to fix internal reviews did not know where else $external_review_deadline was used!!
}

$userid = $userObject->get_user_ID();

$review = new Review($paperID, $userid, $review_type, $mysqli);

if (isset($_POST['close'])) {
  record_general_comments($_POST['paper_comments'], $property_id, $userid, false, $review_type, $mysqli);
  echo close_window();
  exit;
} elseif (isset($_POST['finish'])) {
  record_general_comments($_POST['paper_comments'], $property_id, $userid, true, $review_type, $mysqli);
  echo close_window();
  exit;  
}

function close_window() {
  $html = "<html>\n<head>\n<title>Rog&#333;</title>\n</head>\n<body onload=\"window.close();\"></body>\n</html>";
  
  return $html;
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="imagetoolbar" content="no">
  <meta http-equiv="imagetoolbar" content="false">
  
  <title>Rog&#333;</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; font-size:<?php echo $textsize; ?>%}
    li {margin-left:15px; margin-right:15px; font-size:100%}
    blockquote {font-size:90%}
    .paper {font-size:180%; color:white; font-weight:bold}
  </style>

  <script src="../js/ie_fix.js" type="text/javascript"></script>
  <script language="JavaScript">
    window.history.go(1);
  </script>
</head>

<body oncontextmenu="return false;">
  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?id=<?php echo $_GET['id'] ?>">
<?php
  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div><div style="color:white; font-weight:bold">Review Complete</div></td><td align="center" class="raised_tbl" width="50"><img src="../config/logo.png" width="160" height="67" alt="University Logo" border="0" /></td></tr>';
  echo '</table>';

  $configObject = Config::get_instance();
  $start_of_day_ts = strtotime('midnight');


  if ($_POST['old_screen'] != '' and $start_of_day_ts <= $external_review_deadline) {
    record_comments($property_id, $_POST['old_screen'], $mysqli, $userid, $review_type);
  } else {
    echo "Deadline = " . date($configObject->get('cfg_long_date_php'), $external_review_deadline);
  }
  ?>
  <blockquote>
    <h1>General Comments</h1>
    <p>Please use the area below to record any general comments about the difficulty, appropriateness or other comments about the paper as a whole.</p>
    <textarea name="paper_comments" width="80" rows="6" style="width:100%"><?php get_paper_comments() ?></textarea>
  
  </blockquote>
  <div style="text-align:center"><input type="submit" name="close" value="Save &amp; Close" class="ok" /><input type="submit" name="finish" value="Save &amp; Finish" class="ok" /></div>
<?php
  $mysqli->close();
?>
  </form>
</body>
</html>