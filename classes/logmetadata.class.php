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
* Repository class for the log_meta_data table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class LogMetadata {

  private $id;
  private $paper_id;
  private $student_id;
  private $session_id; //started
  private $start_datetime; //date time object derived from started
  private $ipaddress;
  private $student_grade;
  private $year;
  private $attempt;
  private $completed;
  private $lab_name;

  /**
   * @var mysqli $db
   */
  private $db;

  /**
   * @var userObject $userObject
   */
  private $userObject;

  /**
   * Create new object to represent the Log Metadata table
   * @param userObject $userObject Object describing a user
   * @param integer    $paper_id   ID of the current paper
   * @param mysqli     $db         Database connection
   */
  public function __construct( userObject $userObject, $paper_id, mysqli $db ) {
    $this->userObject = $userObject;
    $this->id             = null;
    $this->session_id     = null;
    $this->start_datetime = null;
    $this->ipaddress      = null;
    $this->student_grade  = $this->userObject->get_grade();
    $this->year           = $this->userObject->get_user_ID();
    $this->attempt        = null;
    $this->completed      = null;
    $this->lab_name       = null;
    $this->student_id     = $this->userObject->get_user_ID();
    $this->paper_id       = $paper_id;
    $this->db             = $db;
  }

  /**
   * Gets last log metadata record for this userID on this paperID
   * @return DateTime
   */
  public function get_record() {

    $query = 'SELECT
                id, started, ipaddress, student_grade, year, attempt, completed, lab_name
              FROM
                log_metadata
              WHERE
                userID  = ?
              AND
                paperID = ?
              ORDER BY
                id DESC
              LIMIT 1';

    $stmt = $this->db->prepare($query);

    $stmt->bind_param( 'ii', $this->student_id, $this->paper_id );
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows < 1) {
      $stmt->close();
      return false;
    }

    $bindResult = $stmt->bind_result( $this->id,
                                      $this->session_id,
                                      $this->ipadress,
                                      $this->student_grade,
                                      $this->year,
                                      $this->attempt,
                                      $this->completed,
                                      $this->lab_name );

    $stmt->fetch();
    $stmt->close();
    $this->populate_start_date_time();

    return true;
  }

  /**
   * Create a new log_metadata record
   * @return bool
   */
  public function create_new_record($ipadress, $attempt, $lab_name) {
    $this->ipadress = $ipadress;
    $this->attempt = $attempt;
    $this->lab_name = $lab_name;
    $this->populate_start_date_time();
    $this->save();
    
    return true;
  }

  public function get_session_id() {
    return $this->session_id;
  }

  public function get_metadata_id() {
    return $this->id;
  }

  public function get_start_datetime() {
    return $this->start_datetime;
  }

  /**
   * Set time at which the paper was completed for the current user
   */
  public function set_completed_to_now() {
    $result = $this->db->prepare('UPDATE log_metadata SET completed = NOW() WHERE userID = ? AND paperID = ?');
    $result->bind_param('ii', $this->userObject->get_user_ID(), $this->paper_id);
    $result->execute();
    $result->close();
  }

  /**
   * Remove indication that the paper has been completed for the current user
   */
  public function set_completed_to_null() {
    $result = $this->db->prepare('UPDATE log_metadata SET completed = NULL WHERE userID = ? AND paperID = ?');
    $result->bind_param('ii', $this->userObject->get_user_ID(), $this->paper_id);
    $result->execute();
    $result->close();
  }

  /**
   * Indicate if the current user has completed the paper
   * @return boolean Has the current user completed the paper
   */
  public function  is_users_paper_completed() {
    if (is_null($this->completed)) {
      return false;
    } else {
      return true;
    }
  }

  /*
  * PRIVATE FUNCTIONS
  */

  /**
   * Insert or update the log_metadata record
   * @return bool
   */
  private function save() {

    // BP Using date() is more reliable when interacting
    // with the front end javascript timer than Mysql server's NOW()

    if ($this->id != null) {
      //update
      $query = 'UPDATE log_metadata (ipaddress, attempt, completed, lab_name) VALUES (?, ?, ?, ?)';
      $stmt = $this->db->prepare($query);
      $stmt->bind_param('siss', $this->ipadress, $this->attempt, $this->completed, $this->lab_name);
      $stmt->execute();
      $stmt->close();
    } else {
      //insert
      $query = 'INSERT INTO log_metadata (id, userID, paperID, started, ipaddress, attempt, completed, lab_name) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)';
      $stmt = $this->db->prepare($query);
      $stmt->bind_param('iississ', $this->student_id, $this->paper_id, $this->session_id, $this->ipadress, $this->attempt, $this->completed, $this->lab_name);
      $stmt->execute();
      $stmt->close();
      
      $this->id = $this->db->insert_id;
    }

    return true;
  }

  /**
   * sets up the start_datetime Date object from started
   * or sets it to now if started is not set
   */
  private function populate_start_date_time() {
    if ($this->session_id != NULL) {
      $this->start_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $this->session_id );
      $this->start_datetime->format('Y-m-d H:i:s');
    } else {
      $this->start_datetime = new DateTime;
      $this->session_id = $this->start_datetime->format('YmdHis');
      $this->start_datetime->format('Y-m-d H:i:s');
    }
  }

}
?>