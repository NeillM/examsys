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

class save_fail_logs{

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
  public function get_save_fail_logs() {
    $this->logs = array();
    $result = $this->db->prepare("SELECT save_fail_log.id, surname, title, initials, userID, paperID, screen, ipaddress, FROM_UNIXTIME(failed, '%d/%m/%Y %H:%i:%s'), paper_title, status, request_url, response_data FROM save_fail_log, users, properties WHERE save_fail_log.userID = users.id AND save_fail_log.paperID = properties.property_id ORDER BY failed");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $surname, $title, $initials, $userID, $paperID, $screen, $ipaddress, $failed, $paper_title, $status, $request, $response);
    while ($result->fetch()) {
      $this->logs[] = array('id' => $id, 'surname' => $surname, 'title' => $title, 'initials' => $initials, 'userID' => $userID, 'paperID' => $paperID, 'screen' => $screen, 'ipaddress' => $ipaddress, 'page_tile' => $paper_title, 'failed' =>$failed, 'status' => $status, 'request' => $request, 'response' => $response );
    }

    return $this->logs;
  }

  /**
   * Clear All the logs from the table
   */
  public function delete_save_fail_logs(){
    $result = $this->db->prepare("delete from save_fail_log");
    if($result->execute()){
      return true;
    }else{
      return false;
    }
  }

  /**
   * Delete a log from the table
   * @param $log_id
   */

  public function delete_a_save_fail_log($log_id) {
    $result = $this->db->prepare("delete from save_fail_log where id = ?");
    $result->bind_param('i', $log_id);
    if($result->execute()){
      return true;
    }else{
      return false;
    }
  }
}