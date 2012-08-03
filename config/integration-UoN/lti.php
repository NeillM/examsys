<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 31/07/12
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
require_once $cfg_web_root . 'classes/userutils.class.php';

function i_lti_user_add($username,$password) {
// take user and password and add user to system

  // UoN version looks up data via ldap and correctly gets the correct fields and adds them into the system

global $mysqli;

  $data=array();

  $returned=ldap_lookup($username,$password, $data, 1);
  if($returned === false) {
    // no ldap user found
    return false;
  }

  $title=$returned[0]['title'][0];
  $forname=$returned[0]['givenname'][0];
  $surname=$returned[0]['sn'][0];
  $email=$returned[0]['uonprimaryemailalias'][0];
  $initials=$returned[0]['initials'][0];
  $employeetype=$returned[0]['employeetype'][0];

  if($employeetype == 'S') {
    //staff
    $course='University Lecturer';
    $role='Staff';
    $year=1;
    $gender='';
    $id=UserUtils::createUser($username, $password, $title, $forname, $surname, $email, $course, $gender, $year, $role, $sid, $mysqli);
  }
  else {
  $user_data = $SMS->getUserData($username);
  if (count($user_data) > 0) {
    //valid acount found create user
    UserUtils::createUser(
      $username,
      $password,
      $user_data['Title'],
      $user_data['Forename'],
      $user_data['Surname'],
      $user_data['Email'],
      $user_data['CourseCode'],
      $user_data['Gender'],
      $user_data['YearofStudy'],
      'Student',
      $user_data['StudentID'],
      $mysqli
    );
  }
    else {
      //error looking up student
      display_error($string['noaccountfound'], '', false, true);
    }
  }


}


function i_lti_user_time_check ($time) {
  $time1=strtotime($time);
  $time2=time();
  $timediff=$time2-$time1;
//  if($timediff>(60*60*24*7*10)) {
  if($timediff>(60*60*1)) {
    return true;
  }
  return false;
}

function i_lti_allow_staff_edit_link() {
  return false;
}

function i_lti_allow_module_self_reg($module_id) {
  return true;
}

function i_lti_module_code_translate($c_internal_id) {

  // only get the shortname through  (courseID is only probably accessible via specific moodle webservices api

  // shortname for real module try XXXXXX-YY-ZZZWWWW  WHERE XXXXXX is saturn code YY is country rest we dont care about.

  // shortname for non module VV-XXXXX-XXXXX-YY-WWWW WHERE XXXXXXXXXX is the fake 'module code'  YYY is country VV is DEPT 2 letter code

  // shortname for metamodules is XXXXXX-YY-XXXXXX-YY-XXXXXXX-YYY-ZZZWWWWW where the set of XXXXXX, YY are unknown

  // convert vle module format into rogo format

}

function i_lti_determine_std_module($modulecode,$campus,$year,$semstart) {
  // return false if not, else return string with a campus.
}

/* The slef_enrol how it creates users.
 *
 * if (UserUtils::usernameExists($_SERVER['PHP_AUTH_USER'],$mysqli) === false ) {
  //the user has no Rogo Account but has an LDAP acount so lets make one !
  $SMS = SMSutils::GetSmsUtils();
  $user_data = $SMS->getUserData($_SERVER['PHP_AUTH_USER']);
  if (count($user_data) > 0) {
    //valid acount found create user
    UserUtils::createUser(
                          $_SERVER['PHP_AUTH_USER'],
                          $_SERVER['PHP_AUTH_PW'],
                          $user_data['Title'],
                          $user_data['Forename'],
                          $user_data['Surname'],
                          $user_data['Email'],
                          $user_data['CourseCode'],
                          $user_data['Gender'],
                          $user_data['YearofStudy'],
                          'Student',
                          $user_data['StudentID'],
                          $mysqli
                         );
    db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $mysqli);
  } else {
    //no account information found
    display_error($string['noaccountfound'], '', false, true);
  }
}
 */

