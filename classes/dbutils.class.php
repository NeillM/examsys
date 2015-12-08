<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Utility class for database related functionality
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

Class DBUtils {
  /**
   * Get a mysqli database connection and set the character set
   *
   * @static
   *
   * @param string $host Host machine for database connection
   * @param string $user Database username
   * @param string $passwd Password for database user
   * @param string $database Initial schema to use
   * @param string $dbclass Optional class to use, e.g. debugging extension to mysqli
   *
   * @return object
   */
  static function get_mysqli_link($host, $user, $passwd, $database, $charset, $notice, $dbclass = 'mysqli', $port = 3306) {

    @$mysqli = new $dbclass($host, $user, $passwd, $database, $port);
    if ($mysqli->connect_error == '') {
      $mysqli->set_charset($charset);
    } else {
      $notice->display_notice('Database Error', "Unable to connect to database using $dbclass.", '/artwork/db_no_connect.png', '#C00000');
      exit;
    }

    return $mysqli;
  }
  
  /**
   * Execute database update command 
   * @param string $table The table being updated
   * @param string $table_idx The index of the table to use to update
   * @param string $params The columns to update and the values to use
   * @param string $id The value of the table index to use
   * @param mysqli $db db connection
   * @return bool true on success false otherwise
   */
  static function exec_db_update($table, $table_idx, $params, $id, $db) {
    $command = 'UPDATE ' . $table . ' SET ';
    $filter = ' WHERE ' . $table_idx . ' = ?';
    // Generate list of selected data to update.
    $selection = '';
    $properties = array_keys($params);
    foreach ($properties as $prop) {
        $selection .= $prop . ' = ?, ';
    }
    $selection = rtrim($selection, ', ');
    $values = array_values($params);
    // Get bind types and values
    $bind_types = array();
    $bind_values = array();
    foreach ($values as $idx => $val) {
        $bind_types[] = $val[0];
        $bind_values[] = $val[1];
    }
    $bind_types = implode('', $bind_types);
    $bind_types .= 'i';
    $bind_values[] = $id;
    $bind_values_ref = array();
    foreach ($bind_values as $key => $value)  {
        $bind_values_ref[$key] = &$bind_values[$key]; 
    }
    // Run generated query.
    $result = $db->prepare($command . $selection . $filter);
    call_user_func_array(array($result, "bind_param"), array_merge(array($bind_types), $bind_values_ref));
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
        return false;
    }
    return true;
  }
}

?>