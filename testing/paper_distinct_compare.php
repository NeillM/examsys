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
 * This script takes the papers and compares the count of records in the appropriate log table by distinct and non distinct
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package Rogō
 */

require '../classes/configobject.class.php';
require '../classes/dbutils.class.php';
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);

@ob_implicit_flush(1);
$configObject = Config::get_instance();
$notice = null;
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_staff_user'), $configObject->get('cfg_db_staff_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

$sql = "select property_id,date_format(start_date,'%Y%m%d%H%i%S') as start_date, date_format(end_date,'%Y%m%d%H%i%S') as end_date, paper_title from properties where paper_type='2' limit 3";

$result = $mysqli->prepare($sql);
if ($mysqli->error) {
  try {
    throw new Exception("MySQL error $mysqli->error <br> Query:<br> ", $mysqli->errno);
  } catch (Exception $e) {
    echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
    echo nl2br($e->getTraceAsString());
    exit();
  }
}
$result->execute();
$result->store_result();
$result->bind_result($propertyid, $start_date, $end_date, $papertitle);

$records = $result->num_rows;




echo <<<HTML
	<html>
<body>
<h1>Initial selection: $sql</h1><br />
<h2>$records Rows Found</h2>
<table>
<tr><th>PaperID</th><th>Paper Name</th><th>Count</th><th>Distinct Count</th><th>Error</th><th></th><th></th></tr>
HTML;


$roles_sql = " AND (users.roles='Student' OR users.roles='graduate')";
while ($result->fetch()) {

  //	  $log_query = $mysqli->prepare("SELECT DISTINCT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$configObject->get('cfg_long_date_time')}') AS display_started, year, title, surname, initials, first_names, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY userID, started, screen");
  $log_query = $mysqli->prepare("SELECT DISTINCT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$configObject->get('cfg_long_date_time')}') AS display_started, year, title, surname, initials, first_names, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ?");
  if ($mysqli->error) {
    try {
      throw new Exception("MySQL error $mysqli->error <br> Query:<br> ", $mysqli->errno);
    } catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());
      exit();
    }
  }
  $log_query->bind_param('iss', $propertyid, $start_date, $end_date);
  $log_query->execute();
  $log_query->bind_result($q_id, $paper_type, $grade, $tmp_roles, $screen, $duration, $started, $user_answer, $display_started, $year, $title, $surname, $initials, $first_names, $gender, $ipaddress, $lab_name, $username, $tmp_userID, $student_id, $user_answer, $q_type, $tmp_userID, $mark, $status, $attempt);


  $log_query->store_result();
  $distinctCNT = $log_query->num_rows;
  $log_query->close();
  $log_query = $mysqli->prepare("SELECT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$configObject->get('cfg_long_date_time')}') AS display_started, year, title, surname, initials, first_names, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ?");
  if ($mysqli->error) {
    try {
      throw new Exception("MySQL error $mysqli->error <br> Query:<br> ", $mysqli->errno);
    } catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());
      exit();
    }
  }
  $log_query->bind_param('iss', $propertyid, $start_date, $end_date);
  $log_query->execute();
  $log_query->bind_result($q_id, $paper_type, $grade, $tmp_roles, $screen, $duration, $started, $user_answer, $display_started, $year, $title, $surname, $initials, $first_names, $gender, $ipaddress, $lab_name, $username, $tmp_userID, $student_id, $user_answer, $q_type, $tmp_userID, $mark, $status, $attempt);


  $log_query->store_result();
  $NOTdistinctCNT = $log_query->num_rows;
  $log_query->close();

  $value = array($papertitle, $distinctCNT, $NOTdistinctCNT,$start_date, $end_date);
  $same = true;
  if ($value[1] != $value[2]) {
    $same = false;

  }
  if ($same == false) {
    $extra = ' style="background-color:red" ';
    $error = 'ERROR';
  } else {
    $extra = ' style="background-color:green" ';
    $error = '';
  }
  echo <<<HTML
	<tr><td $extra>$propertyid</td><td $extra>$value[0]</td><td>$value[1]</td><td>$value[2]</td><td $extra>$error</td><td>$value[3]</td><td>$value[4]</td></tr>
HTML;
  @flush();

}


echo <<<HTML
		</table></body></html>
HTML;


?>


