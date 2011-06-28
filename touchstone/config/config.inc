<?php
/**
* 
* config file
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$ts_version = '4.0';
define('TOUCHSTONE', 'true');
define('DIR_SEPARATOR', '/');
$cfg_web_root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == DIR_SEPARATOR) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . DIR_SEPARATOR;
$protocol = 'https://';

$news_msg = '';

// Local database
  $cfg_db_username = '';
  $cfg_db_passwd   = '';
  $cfg_db_database = '';
  $cfg_db_host 	   = '';

// SMS Imports
  $cfg_sms_sources = array();
  $cfg_ldap_server        = '';
  $cfg_ldap_search_dn     = '';
  $cfg_ldap_bind_rdn      = '';
  $cfg_ldap_bind_password = '';
  $cfg_use_ldap           = false;

//Editor
  $cfg_editor_name = 'tinymce';
  $cfg_editor_javascript = "<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_config.js\"></script>\n";

//Server specific configuration basaed on hostname.
switch (strtolower($_SERVER['HTTP_HOST'])) {
  case 'touchstone.nottingham.ac.uk':
    $cfg_install_type = '';
    break;
}

//Warnings
  $cfg_hour_warning = 10;       // Warning for summative exams
  error_reporting(-1);          // PHP error reporting 
  
//Assistance
  $support_email = '';
  $emergency_support_numbers = array();

//Global DEBUG OUTPUT
  if (isset($_SERVER['PHP_AUTH_USER']) AND ($_SERVER['PHP_AUTH_USER'] == '') {
    require_once $_SERVER['DOCUMENT_ROOT'] . 'touchstone/include/debug.inc';
  } else {
    $dbclass = 'mysqli';
  }
?>