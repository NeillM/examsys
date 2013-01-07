<?php
// This file is part of Rogō
//
// Rog? is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rog? is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rog?.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* This script can only be called from start.php for ajax saving'.
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/marking_functions.inc';
require_once '../include/errors.inc';
require_once '../include/paper_security.inc';
require_once '../classes/paperutils.class.php';
require '../classes/logmetadata.class.php';
require '../classes/lab.class.php';
require '../classes/labobject.class.php';
require '../classes/propertyobject.class.php';
require '../classes/property.class.php';
require '../classes/log_extra_time.class.php';
require '../classes/log_lab_end_time.class.php';

$displayDebug = false; //ajax call so debug info messes up the output

check_var('id', 'GET', true, false);

$stmt = $mysqli->prepare("SELECT property_id, paper_type, labs, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), exam_duration as duration, calendar_year, password FROM properties WHERE crypt_name=? LIMIT 1");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->bind_result($property_id, $paper_type, $labs, $start_date, $end_date, $exam_duration, $calendar_year, $password);
$stmt->fetch();
$stmt->close();

$attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
$original_paper_type = $paper_type; //store the original paper type - needed to retrieve answers from the correct log and functionality related decisions

$modIDs = array_keys(Paper_utils::get_modules($property_id, $mysqli));

if ($userObject->has_role('Student')) {

  // Check for additional password on the paper
  check_paper_password($password, $string);

  // Check time security
  check_datetime($start_date, $end_date);

  //Check room security
  $low_bandwidth = check_labs($paper_type, $labs, $password, $string, $mysqli);

  //get modules if the user is a student and the paper is not formative
  $attempt = check_modules($userObject, $modIDs, $calendar_year, $mysqli);

  // Check for any metadata security restrictions
  check_metadata($property_id, $userObject, $modIDs, $mysqli);


  $summative_exam_session_started = false;

  if( $exam_duration != null and (int) $paper_type == 2 ){

    $current_ip_address = NetworkUtils::get_ipaddress();

    $lab                = new Lab( $mysqli );
    $lab_object         = $lab->get_lab_based_on_ip( $current_ip_address );

    $property_object    = new PropertyObject();

    $property_object->set_property_id( $property_id );

    $property           = new Property( $property_object
        , $mysqli );

    $property_object    = $property->get_property();

    $log_lab_end_time   = new LogLabEndTime( $lab_object
                                            , $property_object
                                            , $mysqli );

    $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();

  }

  if ( time() > $end_date and ( $paper_type == '1' or ( $paper_type == '2' and $summative_exam_session_started == false) ) ) {
    $paper_type = '_late';
  }

  $log_metadata = new LogMetadata( $userObject, $property_id, $mysqli );

}

$preview_q_id = (isset($_GET['q_id'])) ? $_GET['q_id'] : null;

//TODO we need to add some error checking in here. maybe wrap this whole function in a transaction ??
$ret = record_marks($property_id, $mysqli, $userObject->get_user_ID(), $paper_type, $userObject->get_grade(), $userObject->get_year(), $attempt, $userObject->list_user_roles(), $preview_q_id);
echo $_POST['randomPageID'];
?>