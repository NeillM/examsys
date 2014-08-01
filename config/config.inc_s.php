<?php
/**
*
* config file
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

if (empty($root)) $root = str_replace('/config', '/', str_replace('\\', '/', dirname(__FILE__)));
require $root . '/include/path_functions.inc.php';

$rogo_version = '5.1';
$cfg_web_root = get_root_path() . '/';
$cfg_root_path = rtrim('/' . trim(normalise_path(str_replace($_SERVER['DOCUMENT_ROOT']), '', $cfg_web_root), '/'), '/');
$cfg_secure_connection = true;    // If true site must be accessed via HTTPS
$cfg_page_charset 	   = 'UTF-8';
$cfg_company = 'University of Nottingham';
$cfg_academic_year_start = '07/01';
$cfg_tmpdir = '/tmp/';

$cfg_summative_mgmt = false;     // Set this to true for central summative exam administration.
$cfg_client_lookup = 'ipaddress';

// Local database
  $cfg_db_username = 'rogo_sel_t_auth';
  $cfg_db_passwd   = 'mii~89RZcoe.12Q8';
  $cfg_db_database = 'rogo_selenium_t';
  $cfg_db_host 	  = '127.0.0.1';
  $cfg_db_charset 	= 'utf8';
//student db user
  $cfg_db_student_user = 'rogo_sel_t_stu';
  $cfg_db_student_passwd = 'osy!23G2pbu?70H9';
//staff db user
  $cfg_db_staff_user = 'rogo_sel_t_staff';
  $cfg_db_staff_passwd = 'wbs=65K1bik_45M^';
//external examiner db user
  $cfg_db_external_user = 'rogo_sel_t_ext';
  $cfg_db_external_passwd = 'mdx+30H8yjz-50M_';
//sysdamin db user
  $cfg_db_sysadmin_user = 'rogo_sel_t_sys';
  $cfg_db_sysadmin_passwd = 'gra.10H1tky!56C2';
//sct db user
  $cfg_db_sct_user = 'rogo_sel_t_sct';
  $cfg_db_sct_passwd = 'ctg~09K!jlr.55Aj';
//invigilator db user
  $cfg_db_inv_user = 'rogo_sel_t_inv';
  $cfg_db_inv_passwd = 'unz*21Z0rnu.32KM';
// Date formats in MySQL DATE_FORMAT format
  $cfg_short_date = '%d/%m/%y';
  $cfg_long_date_time = '%d/%m/%Y %H:%i';
  $cfg_long_date_php = 'd/m/Y';
  $cfg_short_date_php = 'd/m/y';
  $cfg_long_time_php = 'H:i:s';
  $cfg_short_time_php = 'H:i';
  $cfg_timezone = 'Europe/London';
  date_default_timezone_set($cfg_timezone);

// Reports
  $percent_decimals = 2;

// Standard Setting
  $hofstee_defaults = array('pass'=>array(0, 'median', 0, 100), 'distinction'=>array('median', 100, 0, 100));
  $hofstee_whole_numbers = true;

// SMS Imports
  $cfg_sms_api = '';

// LTI these configure the default lti integration if you want more ability than this then you will need to override the lti_integration class (in config/integration called lti-integration.class.php), UoN version is shipped in the -UoN folder
$cfg_lti_allow_module_self_reg = false; // allows rogo to auto add student to module if selfreg is set for module if from lti launch
$cfg_lti_allow_staff_module_register = false; // allows rogo to register staff onto the module team if set to true and from lti launch and staff in vle
$cfg_lti_allow_module_create = false;  // allows rogo to create module if it doesnt exist

$authentication_fields_required_to_create_user = array('username', 'title', 'firstname', 'surname', 'email', 'role');

//Authentication settings
$authentication = array(
  array('ltilogin', array(), 'LTI Auth'),
  array('guestlogin', array(), 'Guest Login'),
  array('impersonation', array('separator' => '_'), 'Impersonation'),
  array('internaldb', array('table' => 'users', 'username_col' => 'username', 'passwd_col' => 'password', 'id_col' => 'id', 'encrypt' => 'SHA-512', 'encrypt_salt' => 'GGJaQSqFImzv86Jn'), 'Internal Database')
);
$cfg_password_expire = 30;    // Set in days

$enhancedcalculation = array('host' => 'localhost', 'port'=>6311,'timeout'=>5);

//Lookup settings
$lookup = array(
  array('ldap', array('ldap_server' => '', 'ldap_search_dn' => '', 'ldap_bind_rdn' => '', 'ldap_bind_password' => '', 'ldap_user_prefix' => '', 'ldap_attributes' => array('sAMAccountName' => 'username', 'sn' => 'surname', 'title' => 'title', 'givenName' => 'firstname', 'department' => 'school', 'mail' => 'email',  'cn' => 'username',  'employeeType' => 'role',  'initials' => 'initials'), 'lowercasecompare' => true, 'storeprepend' => 'ldap_'), 'LDAP'),
  array('XML', array('baseurl' => 'http://exports/', 'userlookup' => array( 'url' => '/student.ashx?campus=uk', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML')
);

// Objectives mapping
$vle_apis = array();


// Institutional email domains
// If using external authentication (e.g. LDAP) list the domains that will authenticate against the external system
// This will allow you to change the password of any users that do not match against those domains (e.g. external examiners)
  $cfg_institutional_domains = array('nottingham.ac.uk');

// Root path for JS
  $cfg_js_root = <<< SCRIPT
<script>
  if (typeof cfgRootPath == 'undefined') {
    var cfgRootPath = '$cfg_root_path';
  }
</script>
SCRIPT;

//Editor
  $cfg_editor_name = 'tinymce';
  $cfg_editor_javascript = <<< SCRIPT
$cfg_js_root
<script type="text/javascript" src="$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_config.js"></script>
SCRIPT;

//Server specific configuration based on hostname.
switch (strtolower($_SERVER['HTTP_HOST'])) {
  case 'rogo.local':
    $cfg_install_type = ' (local)';
    break;
  case 'rogotest.local':
    $cfg_install_type = ' (local testing)';
    error_reporting(E_ALL);
    break;
  default:
    $cfg_install_type = '';
    error_reporting(0);
    break;
}

//Warnings
  $cfg_hour_warning = 10;       // Warning for summative exams

//Paper auto saving settings
  $cfg_autosave_settimeout = 5; //Maximum time to wait for one request to succeed
  $cfg_autosave_frequency = 30; //How often to auto save in seconds
  $cfg_autosave_retrylimit = 3; //How many times to retry a failed save befor informing the user
  $cfg_autosave_backoff_factor = 1.5; //each retry is lenghtend to $cfg_autosave_settimeout + ($cfg_autosave_backoff_factor * $cfg_autosave_settimeout * retryCount);

//Assistance
  $support_email = '';
  $emergency_support_numbers = array();

//Global DEBUG OUTPUT
  //require_once $_SERVER['DOCUMENT_ROOT'] . 'include/debug.inc';   // Uncomment for debugging output (after uncommenting, comment out line below)
  $dbclass = 'mysqli';

  //$display_auth_debug = true; // set this to deisplay debug on failed authentication
  ?>