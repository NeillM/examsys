<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* This script takes the database structure, as modified by /updates/version4.php and
* checks it with a secondary database as created by /install/index.php. It assumes
* the same root username/password between the two databases.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';

function get_tables($db_name, $db) {
  $tables = array();

  $result = $db->prepare("SHOW TABLES FROM $db_name");
  $result->execute();
  $result->store_result();
  $result->bind_result($name);
  while ($result->fetch()) {
    $tables[] = $name;
  }
  $result->close();
  
  return $tables;
}

function get_table_structure($db_name, $table_name, $db) {
  $details = array();

  $result = $db->prepare("DESCRIBE $db_name.$table_name");
  $result->execute();
  $result->store_result();
  $result->bind_result($field, $type, $null, $key, $default, $extra);
  while ($result->fetch()) {
    $details[] = array($field, $type, $null, $key, $default, $extra);
  }
  $result->close();
  
  return $details;
}

function format_text($txt) {
  $txt = str_replace("','", "', '", $txt);
  if ($txt == '') {
    $txt = '&nbsp;';
  }
  return $txt;
}

function compare_tables($db_master, $db_test, $table_name, $masterdb, $testdb) {
  $master_details = get_table_structure($db_master, $table_name, $masterdb);
  $master_field_no = count($master_details);
    
  $test_details = get_table_structure($db_test, $table_name, $testdb);
  $test_field_no = count($test_details);
  
  if ($test_field_no == 0) {
    echo "<table class=\"nonexist\" cellpadding=\"1\" cellspacing=\"0\" border=\"1\">";
    echo "<tr><th class=\"dkred\" colspan=\"6\">$table_name</th></tr>\n";
  } else {
    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"1\">";
    echo "<tr><th colspan=\"6\">$table_name</th></tr>\n";
  }
  
  for ($i=0; $i<$master_field_no; $i++) {
    echo "<tr>";
    $master_line = $master_details[$i];
    if (isset($test_details[$i])) {
      $test_line = $test_details[$i];
      for ($col=0; $col<6; $col++) {
        $text = format_text($master_line[$col]);
        if ($master_line[$col] === $test_line[$col]) {
          echo "<td>$text</td>";
        } else {
          echo "<td class=\"err\">$text</td>";
        }
      }
    } else {
      for ($col=0; $col<6; $col++) {
        $text = format_text($master_line[$col]);
        echo "<td class=\"err\">$text</td>";
      }
    }
    echo "</tr>";
  }
  
  // Display extra fields in test table.
  if (count($test_details) > count($master_details)) {
    for ($i=$master_field_no; $i<$test_field_no; $i++) {
      $test_line = $test_details[$i];
      for ($col=0; $col<6; $col++) {
        $text = format_text($test_line[$col]);
        echo "<td class=\"err\">$text</td>";
      }
    }
  }
  
  echo "</table>\n<br />\n";
}
?>
<html>
<head>
<title>Database Test</title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <style>
  body {font-size:80%}
  th {background-color:#EAEAEA}
  .nonexist {background-color:#FFC0C0}
  .dkred {background-color:#C00000; color:white}
  .err {color:#C00000}
  </style>
</head>

<body>
<?php
if (isset($_POST['submit'])) {
  $mysqli->close();

  $master_username = $_POST['master_username'];
  $master_password = $_POST['master_password'];
  $master_mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $master_username, $master_password, $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

  $test_username = $_POST['test_username'];
  $test_password = $_POST['test_password'];
  $test_mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $test_username, $test_password, $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

  $table_list = get_tables($_POST['master_dbname'], $master_mysqli);

  foreach ($table_list as $table) {
    compare_tables($_POST['master_dbname'], $_POST['test_dbname'], $table, $master_mysqli, $test_mysqli);
  }
} else {
  echo "<form method=\"post\" action=\"". $_SERVER['PHP_SELF'] . "\">\n";
?>
  <table>
  <tr>
    <td colspan="2"><strong>Master Database</strong></td>
    <td colspan="2"><strong>Test Database</strong></td>
  </tr>
  <tr>
    <td>Name</td>
    <td><input type="text" name="master_dbname" size="20" value="rogo" /></td>
    <td>Name</td>
    <td><input type="text" name="test_dbname" size="20" value="rogo2" /></td>
  </tr>
  <tr>
    <td>Username</td>
    <td><input type="text" name="master_username" size="20" value="root" /></td>
    <td>Username</td>
    <td><input type="text" name="test_username" size="20" value="root" /></td>
  </tr>
  <tr>
    <td>Password</td>
    <td><input type="password" name="master_password" size="20" /></td>
    <td>Password</td>
    <td><input type="password" name="test_password" size="20" /></td>
  </tr>
  <tr>
    <td colspan="4"><input type="submit" name="submit" value=" Test " /></td>
  </tr>
  </table>
<?php
  echo "</form>\n";
}
?>
</body>
</html>
