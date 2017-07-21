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
 * @author Naseem Sarwar
 * @version 1.0
 * @copyright Copyright (c) 2017 The University of Nottingham
 * @package
 */

class access_denied_logs{

  private $db;
  private $logs;

  /**
   * @param object $db    - Link to mysqli
   */
  public function __construct($db) {
    $this->db = $db;
    $this->logs = array();
  }

  /**
   * Get all the logs
   */
  public function get_access_denied_logs() {
    $this->logs = array();
    $result = $this->db->prepare("SELECT denied_log.id, UNIX_TIMESTAMP(tried), ipaddress, page, msg, users.id, users.title, initials, surname FROM denied_log, users WHERE denied_log.userID = users.id ORDER BY tried DESC LIMIT 10000");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $tried, $ipaddress, $page, $msg, $userID, $title, $initials, $surname);
    while ($result->fetch()) {
      $tried_date = new DateTime();
      $tried_date->setTimestamp($tried);
      $this->logs[] = array('id' =>$id, 'tried' => $tried, 'ipaddress' => $ipaddress, 'page' => $page, 'msg' =>$msg, 'userID' => $userID, 'title' => $title, 'initials' => $initials, 'surname' => $surname);
    }

    return $this->logs;
  }

  /**
   * Clear All the logs from table
   */
  public function delete_access_denied_logs(){
    $result = $this->db->prepare("delete from denied_log ");
    $result->execute();
  }

  /**
   * Delete a log from the table
   * @param $log_id
   */

  public function delete_a_access_denied_log($log_id) {
    $result = $this->db->prepare("delete from denied_log where id = ?");
    $result->bind_param('i', $log_id);
    $result->execute();
  }



}