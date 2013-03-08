<?php

$root = __DIR__ . '/'; //str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));

include_once $root . 'include/load_config.php';
require_once $root . 'config/config.inc.php';
require_once $root . 'include/auth.inc';
require_once $root . 'classes/dbutils.class.php';


$username = (isset($_GET['user'])) ? $_GET['user'] : 'nazrji';
$password = (isset($_GET['password'])) ? $_GET['password'] : '@Password1';

$authentication = $configObject->get('authentication');
$salt = $authentication[3][1]['encrypt_salt'];

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $configObject->get('dbclass'));

$pass = encpw($salt, $username, $password, $type = 'SHA-512');

$sql = "UPDATE users set password='$pass' WHERE username='$username'";
$mysqli->query($sql);
if ($mysqli->error) {
  try {
    throw new Exception("MySQL error $mysqli->error <br> Query:<br> ", $mysqli->errno);
  } catch (Exception $e) {
    echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br >";
    echo nl2br($e->getTraceAsString());
    exit();
  }
}
var_dump($mysqli);
