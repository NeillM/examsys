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
* This is a homepage for External Examiners to land on.
* It looks up and presents only papers that they have been
* selected to review and are in the future.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../config/index.inc';  // Get the logo
require_once '../classes/paperutils.class.php';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['externalexaminerarea']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <style type="text/css">
    body {font-size:90%}
    p {line-height:150%}
  </style>
</head>

<body>

<table cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#F1F5FB">
<tr>
<td><div style="padding-left:15px">
  <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />
  <div class="logo_lrg_txt">Rog&#333;</div>
  <div class="logo_small_txt"><?php echo $string['externalexamineraccess']; ?> (<?php echo $userObject->get_title() . ' ' . $userObject->get_initials() . ' ' . $userObject->get_surname(); ?>)</div>
</div>
</td>
<td align="right"><?php echo $logo_html; ?></td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>

<p style="font-size:130%; font-weight:bold; margin-left:15px"><?php echo $string['instructions']; ?></p>
<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg1']; ?></p>

<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg2']; ?></p>
<p style="margin-left:15px; font-weight:bold"><?php echo $string['yourpapersforreview']; ?></p>
<table cellpadding="0" cellspacing="2" border="0" style="margin-left:10px; font-size:90%">
<?php
  $start_of_day_ts = strtotime('midnight');

  $result = $mysqli->prepare("SELECT paper_type, paper_title, property_id, bidirectional, fullscreen, MAX(screen) AS max_screen, UNIX_TIMESTAMP(external_review_deadline) AS external_review_deadline, crypt_name FROM (properties, properties_reviewers, papers) WHERE properties.property_id = properties_reviewers.paperID AND deleted IS NULL AND (DATE_ADD(start_date, INTERVAL 1 WEEK) > NOW() OR start_date IS NULL) AND properties.property_id = papers.paper AND reviewerID = ? GROUP BY paper ORDER BY paper_title");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_type, $paper_title, $property_id, $bidirectional, $fullscreen, $max_screen, $external_review_deadline, $crypt_name);
  while ($result->fetch()) {
    $reviewed = '';
    if ($fullscreen == '') $fullscreen = 0;
    $log_results = $mysqli->prepare("SELECT UNIX_TIMESTAMP(MAX(reviewed)) AS started FROM review_comments WHERE reviewer = ? and q_paper = ?");
    $log_results->bind_param('ii', $userObject->get_user_ID(), $property_id);
    $log_results->execute();
    $log_results->store_result();
    $log_results->bind_result($reviewed);
    $log_results->fetch();
    $log_results->close();
    
    $restartdate = '';
    $display_deadline = date($configObject->get('cfg_long_date_php'), $external_review_deadline);
    
    echo "<tr><td align=\"center\"><a href=\"../user_index.php?id=$crypt_name\">" . Paper_utils::displayIcon($paper_type, $paper_title, '', '', '', '') . "</a></td>\n";
    echo "  <td><a href=\"../user_index.php?id=$crypt_name\">$paper_title</a><br /><div style=\"color:#C00000\">" . $string['deadline'] . " ";
    if ($start_of_day_ts > $external_review_deadline) {
      printf($string['expired'], $configObject->get('cfg_company'));
    } else {
      if ($display_deadline == '00/00/0000') {
        echo $string['notset'];
      } else {
        echo $display_deadline;
      }
    }
    echo '</div>';
    if ($reviewed == '') {
      echo '<span style="color:white; background-color:#FF4040; padding-left:5px; padding-right:5px">' . $string['notreviewed'] . '</span>';
    } else {
      echo '<span style="color:#808080">' . sprintf($string['reviewed'], date($configObject->get('cfg_short_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $reviewed)) . '</span>';
    }
    echo "</td></tr>\n<tr><td colspan=\"2\" style=\"font-size:80%\">&nbsp;</td>\n</tr>\n";
  }

  if ($result->num_rows == 0) {
    echo "<tr><td colspan=\"2\"><p style=\"color:red\">" . $string['nopapersfound'] . "</p></td></tr>\n";
  }
  $result->close();
  echo "</td></tr>\n<tr><td colspan=\"2\">&nbsp;</td></tr>\n<tr><td colspan=\"2\" style=\"text-align:left\"><hr noshade=\"noshade\" align=\"left\" style=\"text-align:left; background-color:#C0C0C0; color:#C0C0C0; height:1px; border:0; width:400px\" /></td>\n</tr>\n";

  echo "<tr><td width=\"66\" style=\"text-align:center\"><a href=\"mailto:" . $configObject->get('support_email') . "\"><img src=\"../artwork/email_icon_48.png\" width=\"48\" height=\"48\" alt=\"" . $string['help'] . "\" /></a></td>\n</td><td><a href=\"mailto:" . $configObject->get('support_email') . "\">" . $configObject->get('support_email') . "</a><br /><span style=\"color:#808080\">" . $string['helpandsupportext'] . "</span></td></tr>\n";

  echo "<tr><td>&nbsp;</td><td style=\"font-size:80%\">&nbsp;</td></tr>\n";
  echo "<tr><td width=\"66\" style=\"text-align:center\"><img src=\"../artwork/osi_logo.png\" width=\"56\" height=\"66\" alt=\"Open Source Initiative\" /></td>\n</td><td><span style=\"color:#808080\">" . sprintf($string['rogodetails'], $configObject->get('rogo_version')) . "</a> <a href=\"http://rogo-oss.nottingham.ac.uk\">rogo-oss.nottingham.ac.uk</a></td></tr>\n";
  $mysqli->close();
?>

</table>
<br />&nbsp;<br />

<div style="margin-left:10px; font-size:80%; color:#808080"><?php printf($string['copyrightmsg'], $configObject->get('cfg_company')); ?></div>

</body>
</html>