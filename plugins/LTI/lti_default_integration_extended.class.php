<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 17/01/14
 * Time: 13:31
 * To change this template use File | Settings | File Templates.
 */
class lti_default_integration_extended  extends lti_integration {


  public function user_time_check($time, $user='') {

    // takes laast time logged in and optionally the user and decides if reauthentication should be done (true)

    return false;
  }

  /**
   * Convert VLE module shortcode into Rogo moduleid 
   * @param mysqli $mysqli db connection
   * @param string $moduleshortcode VLE module shortcode
   * @param string $course_title VLE module title
   * @return array rogo module information
   */
  public function module_code_translate($mysqli, $c_internal_id, $course_title = '') {

    // this function translates the incoming course code and course title it returns an array (containing possibly multiple records) of an array containing string if Manual or SMS for sms ones, the module code, a campus code (text) , school as a string (gets lookedup against rogo to get id later, a 1 for self reg enable [0 for disable] and the course title

    return array(array('Manual', $c_internal_id, 'CampusTODO', 'SchoolTODO', 0, "MISSING:$course_title"));
  }

  static function sms_api($data) {

    // this returns the sms url appropriate for the item element (inner array) of the return from module_code_translate function

    if ($data[0] != 'SMS') {
      return '';
    }
    $SMS = SmsUtils::GetSmsUtils();

    $SMS->set_module($data[2]);
    return $SMS->url;
  }

}
