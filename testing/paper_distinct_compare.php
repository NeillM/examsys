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
$configObject = Config::get_instance();
$notice = null;
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_staff_user'), $configObject->get('cfg_db_staff_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

$sql = "select property_id,date_format(start_date,'%Y%m%d%H%i%S') as start_date, date_format(end_date,'%Y%m%d%H%i%S') as end_date, paper_title from properties where paper_type=2";

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

$roles_sql = " AND (users.roles='Student' OR users.roles='graduate')";
while ($result->fetch()) {

  //	  $log_query = $mysqli->prepare("SELECT DISTINCT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$configObject->get('cfg_long_date_time')}') AS display_started, year, title, surname, initials, first_names, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY userID, started, screen");
  $log_query = $mysqli->prepare("SELECT DISTINCT count(log2.q_id) as count FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ?");
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
  $log_query->bind_result($count);
  $log_query->fetch();
  $log_query->close();
  $distinctCNT = $count;
  $log_query = $mysqli->prepare("SELECT  count(log2.q_id) as count FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ?");
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
  $log_query->bind_result($count);
  $log_query->fetch();
  $NOTdistinctCNT = $count;

  $data[$propertyid] = array($papertitle, $distinctCNT, $NOTdistinctCNT);

}

echo <<<HTML
	<html>
<body>
<h1>Initial selection: $sql</h1><br />
<h2>$records Rows Found</h2>
<table>
<tr><th>PaperID</th><th>Paper Name</th><th>Count</th><th>Distinct Count</th><th>Error</th></tr>
HTML;
if (isset($data)) {
  foreach ($data as $key => $value) {
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
	<tr><td $extra>$key</td><td $extra>$value[0]</td><td>$value[1]</td><td>$value[2]</td><td $extra>$error</td></tr>
HTML;
  }
}
echo <<<HTML
		</table></body></html>
HTML;


?>


