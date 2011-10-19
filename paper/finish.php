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
* Completes final log of the last screen to the 'logX' table and then will display feedback if the paper is in 'formative' 
* mode or will display a confirmation notice to the examinee stating all answers and marks have been successfully recorded.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_student_auth.inc';
  require '../include/marking_functions.inc';
  require '../include/calculate_marks.inc';
  require '../include/errors.inc';
  require '../include/mapping.inc';
  require '../include/finish_functions.inc';

  getSpecialSettings($userID, $mysqli);
    
  if ($paper_properties = $mysqli->prepare("SELECT property_id, labs, moduleID, calendar_year, display_correct_answer, display_question_mark, display_students_response, display_feedback, hide_if_unanswered, paper_title, paper_type, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, marking, paper_postscript, pass_mark, latex_needed, password FROM properties WHERE crypt_name=?")) {
    $paper_properties->bind_param('s', $_GET['id']);
    $paper_properties->execute();
    $paper_properties->store_result();
    $paper_properties->bind_result($paperID, $labs, $moduleID, $calendar_year, $display_correct_answer, $display_question_mark, $display_students_response, $display_feedback, $hide_if_unanswered, $paper_title, $paper_type, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $marking, $paper_postscript, $pass_mark, $latex_needed, $password);
    while ($paper_properties->fetch()) {
      // If set overwrite the default colours with the current users' special settings
      if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
      if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
      if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
      if (!isset($marks_color) or $marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
      if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
      if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
      if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';
      $attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
      
      $log_type = $paper_type;
      $low_bandwidth = 0;
      
      if (strpos($userroles,'Staff') !== false and isset($_GET['userid']) and $_GET['userid'] != $userID) {
        // Turn on all feedback if staff and a student exam script is being reviewed.
        $display_correct_answer = 1;
        $display_question_mark = 1;
        $display_students_response = 1;
        $display_feedback = 1;
        $hide_if_unanswered = 0;
      }

      if ($userroles == 'Student') {
        if ($paper_type == 2) $latex_needed = 0;  // Students get no feedback for summative exams so don't load the Latex library

        // Check for additional password on the paper
        if ($password != '') {
          if ($password != $_COOKIE['paperpwd']) {
            access_denied($string['specificpassword'], $output_header = false);
          }
        }
 
        // Check time security
        if ((time()+120) < $start_date or (time()-3600) > $end_date) {
          $tmp_string = sprintf($string['error_time'], date('d/m/Y H:i',$start_date), date('d/m/Y H:i',$end_date));
          access_denied($tmp_string, $output_header = false);
        }
        //Check room security
        if ($labs != '') {
          $lab_info = $mysqli->prepare("SELECT address, low_bandwidth FROM ip_addresses WHERE address=? AND lab IN ($labs)");
          $lab_info->bind_param('s', $_SERVER['REMOTE_ADDR']);
          $lab_info->execute();
          $lab_info->bind_result($address, $low_bandwidth);
          $lab_info->store_result();
          $lab_info->fetch();
          if ($lab_info->num_rows == 0) {
            access_denied($string['denied_location'], false);
          }
          $lab_info->close();
        }
        
        // get modules if the user is a student and the paper is not formative
        if (stripos($_SERVER['PHP_AUTH_USER'], 'user') !== 0) {
           if ($moduleID != '') {
            $cal_year_sql = '';
            if($calendar_year != '') $cal_year_sql = "AND calendar_year = '$calendar_year'";
            $module_info = $mysqli->query("SELECT moduleid,attempt FROM student_modules WHERE userID=$userID AND moduleid IN ('" . str_replace(",","','",$moduleID) . "') $cal_year_sql");
            if ($module_info->num_rows == 0) {
              $tmp_string = sprintf($string['notregistered'], $title, $surname, $username, $moduleID, $calendar_year);
              access_denied($tmp_string, $output_header = false);
            } else {
              $row = $module_info->fetch_array(MYSQLI_ASSOC);
              if (is_array($row)) {
                $attempt = $row['attempt'];
              }
            }
            $module_info->close();
          } else {
            access_denied($string['error_module'], false);
          }
        }
        if (time() > $end_date and ($paper_type == '1' or $paper_type == '2')) {
          $paper_type = '_late';
        }
        
        // Check for any metadata security restrictions
        $metadata_security = $mysqli->prepare("SELECT name, value FROM paper_metadata_security WHERE paperID=?");
        $metadata_security->bind_param('i', $paperID);
        $metadata_security->execute();
        $metadata_security->bind_result($security_type, $security_value);
        $metadata_security->store_result();
        while ($metadata_security->fetch()) {
          $check_security = $mysqli->prepare("SELECT users_metadata.id FROM users_metadata, modules WHERE users_metadata.moduleid=modules.id AND modules.moduleid IN ('" . str_replace(",", "','", $moduleID) . "') AND userID=? AND type=? AND value=?");
          $check_security->bind_param('iss', $userID, $security_type, $security_value);
          $check_security->execute();
          $check_security->store_result();
          if ($check_security->num_rows == 0) {
            $tmp_string = sprintf($string['error_metadata'], $security_type, $security_value);
            access_denied($tmp_string, false);
          }
          $check_security->close();
        }      
        $metadata_security->close();
        
      }
      if (isset($_GET['type'])) $log_type = $_GET['type'];
    }
    $paper_properties->close();
  } else {
    display_error("Properties Query Error", $mysqli->error);
  }
  require '../config/finish.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['examscript'] . ' ' . $cfg_install_type; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<style type="text/css">
body {background-color:<?php echo $bgcolor; ?>;color:<?php echo $fgcolor; ?>;padding:0px;margin:0px;border:0px;font-family:<?php echo $font; ?>,sans-serif;font-size:<?php echo $textsize; ?>%}
p {margin-top:0px;padding-top:0px}
li {margin-left:15px;margin-right:15px;font-family:<?php echo $font; ?>,sans-serif;font-size:100%}
select,input {font-family:<?php echo $font; ?>,sans-serif;font-size:100%}
blockquote {font-size:90%}
table {font-size:100%}
.paper {margin-left:0px;font-family:<?php echo $font; ?>,sans-serif;font-size:180%;color:white;font-weight:bold}
.q_no {width:40px;text-align:right;vertical-align:top}
.theme {margin-left:15px;font-size:150%;font-weight:bold;color:<?php echo $themecolor; ?>}
.objH {font-weight:bold;color:<?php echo $themecolor; ?>}
.notes {color:<?php echo $labelcolor; ?>}
.fback {font-family:<?php echo $font; ?>,sans-serif; font-style:italic; color:<?php echo $labelcolor; ?>}
.label {color:<?php echo $labelcolor; ?>}
.mk {padding-left:8px;padding-right:8px;background-color:#FFFF00}
.mkpad {padding-top:10px}
.answerindent {margin-left:17px;margin-right:15px}
.std {display:block;background-color:#f27000;color:white;width:35px;text-align:center}
.matrix {border:1px solid #808080; border-collapse:collapse}
.matrix td {border:1px solid #808080}
</style>
<?php
  if ($latex_needed == 1) {
   echo "<script type=\"text/javascript\" src=\"/javascript/jquery-1.6.1.min.js\"></script>";
   echo "<script type=\"text/javascript\" src=\"/tools/mee/mee/js/mee_src.js\"></script>";
  }
  if (($userroles == 'Student' and $paper_type < 2) or strpos($userroles,'Staff') !== false) {
    echo "<script src=\"../javascript/ie_fix.js\" type=\"text/javascript\"></script>\n";
  }
?>
<script language="JavaScript" src="../javascript/flash_include.js"></script>
<script language="JavaScript">
  window.history.go(1);

  function launchHelp(pageID) {
    helpwin=window.open("/help/student/index.php?id=" + pageID + "","help","width="+(screen.width-30)+",height="+(screen.height-100)+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
    helpwin.moveTo(10,10);
    if (window.focus) {
      helpwin.focus();
    }
  }
</script>
</head>

<?php
  if (strpos($userroles,'Student') !== false) {
    echo '<body oncontextmenu="return false;">';
  } else {
    echo '<body>';
  }
  if (isset($_POST['current_screen'])) {
    $current_screen = $_POST['current_screen'];
  } else {
    $current_screen = 1;
  }
  if ($current_screen > 1 and (!isset($_GET['dont_record']) or $_GET['dont_record'] != true)) {
    // Record answers from the previous screen.
    record_marks($paperID, $mysqli, $userID, $paper_type, $grade, $year, $attempt, $userroles);
  }

  if (isset($_GET['userid'])) {
    $temp_userID = $_GET['userid'];
  } else {
    $temp_userID = $userID;
  }
  $old_q_id = 0;
  $old_screen = 0;
  if (isset($_GET['previous'])) {
    $sessionid = $_GET['previous'];
    $log_type = $_GET['log_type'];
  } else {
    $sessionid = $_POST['sessionid'];
  }

  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  if ($paper_type < 2 or strpos($userroles,'Staff') !== false or strpos($userroles,'SysAdmin') !== false) {
    echo '<span style="font-size:90%; color:white; font-weight:bold">' . $string['answersscreen'];
    if (isset($_GET['surname'])) echo ' for ' . $_GET['surname'];
    echo '</span>';
  }
  echo '</td>';
  echo $logo_html;
  echo '</table>';

  if ($paper_type == '0' or (($paper_type == '1' or $paper_type == '2' or $paper_type == '5') and (strpos($userroles,'Staff') !== false or strpos($userroles,'SysAdmin') !== false))) {
    display_feedback($sessionid, $temp_userID, $paperID, $paper_type, $log_type, $paper_title, $paper_postscript, $marking, $userroles, $mysqli);
  } else {
    echo '<blockquote>';
    echo '<p style="font-size:450%;font-family:Rage,\'Brush Script MT\',\'Lucida Handwriting\',sans-serif">' . $string['thankyou'] . '</p>';
    echo '<p>' . sprintf($string['msg'], $paper_title) . '</p><br />';
    if ($paper_postscript != '') echo "<p>$paper_postscript</p>\n";
    echo '</blockquote>';
    if ($paper_type == '2') {
      echo '<br /><div style="text-align:center;border:1px #C0C0C0 solid;background-color:#E6E6DF;padding:10px;margin-left:100px;margin-right:100px" align="center">' . $leaving_rules . '<br /><br /><input type="button" name="close" value="&nbsp;' . $string['closewindow'] . '&nbsp;" onclick="window.close();" /></div>';
    } else {
      echo '<br /><div align="center"><input type="button" name="close" value="&nbsp;' . $string['closewindow'] . '&nbsp;" onclick="window.close();" /></div>';
    }
  }
  echo "</body>\n</html>";
  $mysqli->close();
?>