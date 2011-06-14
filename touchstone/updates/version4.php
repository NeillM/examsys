<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  //require '../include/sysadmin_auth.inc';
  if (!defined('STDIN')) {
    exit;
  }
  require '../config/config.inc';
  set_time_limit(0);
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  echo "\nStarting update from version 4.0 to 4.1\n";
  ob_start();
  
  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\nFinished!\n";
?>