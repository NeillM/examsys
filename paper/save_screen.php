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
require_once '../classes/logmetadata.class.php';
require_once '../classes/lab_factory.class.php';
require_once '../classes/lab.class.php';
require_once '../classes/log_extra_time.class.php';
require_once '../classes/log_lab_end_time.class.php';
require_once '../classes/paperproperties.class.php';


$displayDebug = false; //ajax call so debug info messes up the output

check_var('id', 'GET', true, false, false);

$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'],$mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $notice->access_denied($mysqli, $string, $string['error_paper'], $output_header = false);
  //this will exit php
}

$original_paper_type = $propertyObj->get_paper_type(); //store the original paper type - needed to retrieve answers from the correct log and functionality related decisions

$attempt = 1;                 //default attempt to 1 overwritten if the student is resit candidate by (check_modules)
$low_bandwidth = 0;           //default to off overwritten by (check_labs) if lab has low_bandwidth set
$lab_name = NULL;             //default overwritten by (check_labs)
$lab_id = NULL;
$current_ip_address = NULL;   //default overwritten by (check_labs)

$current_ip_address = NetworkUtils::get_ipaddress();
$lab_factory = new LabFactory($mysqli);
if($lab_object = $lab_factory->get_lab_based_on_ip($current_ip_address)){
    $lab_name = $lab_object->get_name();
    $lab_id = $lab_object->get_id();
}

if ($userObject->has_role('Student')) {

  //get the module Ids for this paper 
  $modIDs = array_keys(Paper_utils::get_modules($propertyObj->get_property_id(), $mysqli));

  // Check for additional password on the paper
  check_paper_password($propertyObj->get_password(), $string);

  // Check time security
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date());

  //Check room security
  $low_bandwidth = check_labs(  $propertyObj->get_paper_type(), 
                                $propertyObj->get_labs(), 
                                $current_ip_address,
                                $propertyObj->get_password(), 
                                $string, 
                                $mysqli
                              );

  // check modules if the user is a student and the paper is not formative
  $attempt = check_modules($userObject, $modIDs, $propertyObj->get_calendar_year(), $mysqli);

  // Check for any metadata security restrictions
  check_metadata($propertyObj->get_property_id(), $userObject, $modIDs, $string, $mysqli);

  $summative_exam_session_started = false;

}

if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2'){
  $log_lab_end_time   = new LogLabEndTime($lab_object->get_id(), $propertyObj, $mysqli );
  $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

if ( time() > $propertyObj->get_end_date() and ( $propertyObj->get_paper_type() == '1' or ( $propertyObj->get_paper_type() == '2' and $summative_exam_session_started == false) ) ) {
  $paper_type = '_late';
}

$preview_q_id = (isset($_GET['q_id'])) ? $_GET['q_id'] : null;

//TODO we need to add some error checking in here. maybe wrap this whole function in a transaction ??
$ret = record_marks($propertyObj->get_property_id(), $mysqli, $userObject->get_user_ID(), $propertyObj->get_paper_type(), $userObject->get_grade(), $userObject->get_year(), $attempt, $userObject->list_user_roles(), $preview_q_id);
echo $_POST['randomPageID'];
?>