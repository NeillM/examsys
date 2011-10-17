<?php
require_once '../config/config.inc.php';

$mysqli = new $dbclass($cfg_db_host, 'root', 'touchstone', 'touchstone');

if ($mysqli->connect_error) {
  echo "<div>Failded to contect to mysql using " . $_POST['mysql_admin_user'] . '' .  $_POST['mysql_admin_pass'] . '</div>';
  echo "</body>";
  echo "</html>";
  exit;
}

$mysqli->query("CREATE USER  'test1'@'". $cfg_db_host . "' IDENTIFIED BY 'ynx?81XnwyY9johd'");
echo "<li>NEW DB USER:: test1 created</li>";
$priv_SQL[] = "GRANT SELECT, UPDATE ON " . $cfg_db_database . ".users TO 'test1'@'". $cfg_db_host . "'";

?>
  