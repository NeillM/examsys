<?php
/**
*
* config file
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

if (empty($root)) $root = str_replace('/config', '/', str_replace('\\', '/', dirname(__FILE__)));
require $root . '/include/path_functions.inc.php';

/*
// PHP session security settings
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
*/

$rogo_version = '6.0';
$cfg_web_root = get_root_path() . '/';
$cfg_root_path = rtrim('/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $cfg_web_root), '/'), '/');
$cfg_secure_connection = true;    // If true site must be accessed via HTTPS
$cfg_page_charset 	   = 'UTF-8';
$cfg_company = 'University of Nottingham';
$cfg_academic_year_start = '07/01';
$cfg_tmpdir = '/tmp/';

$cfg_summative_mgmt = true;     // Set this to true for central summative exam administration.
$cfg_client_lookup = 'ipaddress';


  $cfg_web_host = '127.0.0.1';

// Local database
  $cfg_db_username = 'rogo_auth';
  $cfg_db_passwd   = 'zzu-60VHutf=22X7';
  $cfg_db_database = 'rogo';
  $cfg_db_host 	  = '127.0.0.1';
  $cfg_db_charset 	= 'utf8';
//student db user
  $cfg_db_student_user = 'rogo_stu';
  $cfg_db_student_passwd = 'zzw!33B3ulo%31Gd';
//staff db user
  $cfg_db_staff_user = 'rogo_staff';
  $cfg_db_staff_passwd = 'puj!69Z^xyc=43MM';
//external examiner db user
  $cfg_db_external_user = 'rogo_ext';
  $cfg_db_external_passwd = 'nxw^02S0msh?07X_';
//sysdamin db user
  $cfg_db_sysadmin_user = 'rogo_sys';
  $cfg_db_sysadmin_passwd = 'xmz$09Dzjbt&07A_';
//sct db user
  $cfg_db_sct_user = 'rogo_sct';
  $cfg_db_sct_passwd = 'wyt!60Syrtu$19ZL';
//invigilator db user
  $cfg_db_inv_user = 'rogo_inv';
  $cfg_db_inv_passwd = 'xic^23SRgow?62D3';
// Date formats in MySQL DATE_FORMAT format
  $cfg_short_date = '%d/%m/%y';
  $cfg_long_date = '%d/%m/%Y';
  $cfg_long_date_time = '%d/%m/%Y %H:%i';
  $cfg_short_date_time = '%d/%m/%y %H:%i';
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
  $cfg_sms_api = 'uon_saturn';

// LTI these configure the default lti integration if you want more ability than this then you will need to override the lti_integration class (in config/integration called lti-integration.class.php), UoN version is shipped in the -UoN folder
$cfg_lti_allow_module_self_reg = false; // allows rogo to auto add student to module if selfreg is set for module if from lti launch
$cfg_lti_allow_staff_module_register = false; // allows rogo to register staff onto the module team if set to true and from lti launch and staff in vle
$cfg_lti_allow_module_create = false;  // allows rogo to create module if it doesnt exist

// lti_integration variable below is set the relative path & filename of the new integration class or left as blank or default to use the built in functionality.
$lti_integration = 'default';

$authentication_fields_required_to_create_user = array('username', 'title', 'firstname', 'surname', 'email', 'role');

//Authentication settings
$authentication = array(
  array('ltilogin', array(), 'LTI Auth'),
  array('guestlogin', array(), 'Guest Login'),
  array('impersonation', array('separator' => '_'), 'Impersonation'),
  array('internaldb', array('table' => 'users', 'username_col' => 'username', 'passwd_col' => 'password', 'id_col' => 'id', 'encrypt' => 'SHA-512', 'encrypt_salt' => 'EYjSuW0jF4wvkfQu'), 'Internal Database'),
	array('ldap', array('table' => 'users', 'username_col' => 'username', 'id_col' => 'id', 'ldap_server' => 'iLDAP.nottingham.ac.uk', 'ldap_search_dn' => 'OU=University,DC=intdir,DC=nottingham,DC=ac,DC=uk', 'ldap_bind_rdn' => 'LDAP_Touchstone', 'ldap_bind_password' => 'T0uch5t0ne', 'ldap_user_prefix' => 'sAMAccountName='), 'LDAP'),
	array('languageselection', array('available_languages'=>array('English'=>'en','Polski'=>'pl','Čeština'=>'cs'),'cfg_web_root'=>$cfg_web_root), 'Language Selection')
);
$cfg_password_expire = 30;    // Set in days

$enhancedcalculation = array('host' => '10.159.248.236', 'port'=>6311,'timeout'=>5);

//Lookup settings
/*
$lookup = array(
  array('ldap', array('ldap_server' => 'iLDAP.nottingham.ac.uk', 'ldap_search_dn' => 'OU=University,DC=intdir,DC=nottingham,DC=ac,DC=uk', 'ldap_bind_rdn' => 'LDAP_Touchstone', 'ldap_bind_password' => 'T0uch5t0ne', 'ldap_user_prefix' => 'sAMAccountName=', 'ldap_attributes' => array('sAMAccountName' => 'username', 'sn' => 'surname', 'title' => 'title', 'givenName' => 'firstname', 'department' => 'school', 'UoNPrimaryEmailAlias' => 'email', 'UoNemailAlias' => 'email', 'mail' => 'email', 'UonStuID' => 'studentID', 'UoNStaffID' => 'staffID', 'cn' => 'username', 'UoNPosition' => 'role', 'employeeType' => 'role', 'UoNUPSStatus' => 'role', 'initials' => 'initials'), 'lowercasecompare' => TRUE, 'storeprepend' => 'ldap_'), 'LDAP'),
  array('XML', array('baseurl' => 'http://exports/', 'userlookup' => array( 'url' => '/student.ashx?campus=uk', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML')
);
 * 
 */
$lookup = array(
  array('ldap', array('ldap_server' => 'iLDAP.nottingham.ac.uk', 'ldap_search_dn' => 'OU=University,DC=intdir,DC=nottingham,DC=ac,DC=uk', 'ldap_bind_rdn' => 'LDAP_Touchstone', 'ldap_bind_password' => 'T0uch5t0ne', 'ldap_user_prefix' => 'sAMAccountName=', 'ldap_attributes' => array('sAMAccountName' => 'username', 'sn' => 'surname', 'title' => 'title', 'givenName' => 'firstname', 'department' => 'school', 'UoNPrimaryEmailAlias' => 'email', 'UoNemailAlias' => 'email', 'mail' => 'email', 'UonStuID' => 'studentID', 'UoNStaffID' => 'staffID', 'cn' => 'username', 'UoNPosition' => 'role', 'employeeType' => 'role', 'UoNUPSStatus' => 'role', 'initials' => 'initials'), 'lowercasecompare' => TRUE, 'storeprepend' => 'ldap_'), 'LDAP'),
  array('XML', array('baseurl' => 'http://saturn-exports.nottingham.ac.uk/', 'userlookup' => array( 'url' => '/touchstonestudent.ashx?campus=uk', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML UK'),
  array('XML', array('baseurl' => 'http://saturn-exports.nottingham.ac.uk/', 'userlookup' => array( 'url' => '/touchstonestudent.ashx?campus=malaysia', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML Malaysia'),
  array('XML', array('baseurl' => 'http://saturn-exports.nottingham.ac.uk/', 'userlookup' => array( 'url' => '/touchstonestudent.ashx?campus=china', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML China'),
  array('UoNSaturnTranslation',array(),'UoNSaturnTranslate')
);
// Objectives mapping
$vle_apis = array('UoNCM' => '', 'NLE' => '');
//Questions
  $cfg_interactive_qs = 'html5';
  //$cfg_interactive_qs = 'flash';


// Institutional email domains
// If using external authentication (e.g. LDAP) list the domains that will authenticate against the external system
// This will allow you to change the password of any users that do not match against those domains (e.g. external examiners)
  $cfg_institutional_domains = array('nottingham.ac.uk');

// Root path for JS
  $cfg_js_root = <<< SCRIPT
<script type="text/javascript">
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
    error_reporting(E_ALL);
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
  $cfg_hour_warning = 9;       // Warning for summative exams

//Paper auto saving settings
  $cfg_autosave_settimeout = 5; //Maximum time to wait for one request to succeed
  $cfg_autosave_frequency = 300; //How often to auto save in seconds
  $cfg_autosave_retrylimit = 3; //How many times to retry a failed save befor informing the user
  $cfg_autosave_backoff_factor = 1.5; //each retry is lenghtend to $cfg_autosave_settimeout + ($cfg_autosave_backoff_factor * $cfg_autosave_settimeout * retryCount);

//Assistance
  $support_email = 'learning-team@nottingham.ac.uk';
  $emergency_support_numbers = array('0115 9515881'=>'Exams Office','0115 846 6122'=>'Learning Technology Section');
  $midexam_clarification = array('invigilators', 'students');

//Global DEBUG OUTPUT
  //require_once $_SERVER['DOCUMENT_ROOT'] . '/rogo/include/debug.inc';   // Uncomment for debugging output (after uncommenting, comment out line below)
  $dbclass = 'mysqli';

  $display_auth_debug = false; // set this to display debug on failed authentication
//used for debugging 
  $debug_lang_string = false;

  $displayerrors = false;  // overrides settings in php for errors not to be shown to screen (true enables)

  $displayallerrors = false; // display/logs any error the system has including notices (true enables)

  $errorshutdownhandling=true; //enables log at shutdown (allows you to catch reasons behind fatal errors etc including mysqli errors (true enables)

  $errorcontexthandling = 'improved'; //improved gives a good capture of context variables while filtering for security of display/saved data, basic captures all but doesnt run and security routines, none doesnt capture any context variables
  ?>