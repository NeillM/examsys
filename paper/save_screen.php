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
* This script can only be called from a paper in 'summative' mode from one of the four green fire exit icons displayed in 'start.php'.
*  It does three main things: 
*        1) record the current screen data to the 'log' table, 
*        2) blank the screen to prevent plagiarism among evacuating examinees, and 
*        3) has a 'continue' button at the bottom of the screen with passes the correct parameters back to 'start.php' if the 
*           examinees are allowed to re-enter the building.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require '../include/marking_functions.inc';
require '../include/errors.inc';
require '../include/paper_security.inc';

$displayDebug = false; //ajax call so debug info messes up the output
check_var('id', 'GET', true, false);

$stmt = $mysqli->prepare("SELECT property_id, paper_type, labs, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), moduleID, calendar_year, password FROM properties WHERE crypt_name=?");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $paper_type, $labs, $start_date, $end_date, $moduleID, $calendar_year, $password);
while ($stmt->fetch()) {
  $attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
  if (strpos($userroles,'Student') !== false) {
  
    // Check for additional password on the paper
    check_paper_password($password);
    
    // Check time security
    check_datetime($start_date, $end_date);

    //Check room security
    $low_bandwidth = check_labs($paper_type, $labs, $mysqli);
      
    //get modules if the user is a student and the paper is not formative
    $attempt = check_modules($userID, $moduleID, $calendar_year, $mysqli);

    // Check for any metadata security restrictions
    check_metadata($property_id, $userID, $moduleID, $mysqli);

    if (time() > $end_date and ($paper_type == '1' or $paper_type == '2')) {
      $paper_type = '_late';
    }
  }
}
$stmt->free_result();
$stmt->close();

//TODO we need to add some error checking in here. maby wrap this whole function in a transaction ??
$ret = record_marks($property_id, $mysqli, $userID, $paper_type, $grade, $year, $attempt, $userroles);

echo $_POST['randumPageID'];
?>