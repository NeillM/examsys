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


}


function i_lti_user_time_check ($time) {
  return false;
}

function i_lti_allow_staff_edit_link() {
  return false;
}

function i_lti_allow_module_self_reg($module_id) {
  return true;
}

function i_lti_module_code_translate($c_internal_id) {
return $c_internal_id;
}

function i_lti_determine_std_module($modulecode,$campus,$year,$semstart) {
  // return false if not, else return string with a campus.
}
