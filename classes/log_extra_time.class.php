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
* Repository class for the log_extra_time table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class LogExtraTime {



  /*
   * @var LogLabEndTime $log_lab_end_time
  */

  private $log_lab_end_time;

  /*
   * @var UserObject $student_object
  */

  private $student_object;


  /*
   * @var mysqli $db
   */

  private $db;
  private $msg;

  public function __construct( LogLabEndTime $log_lab_end_time
                             , UserObject    $student_object
                             , mysqli        $db ) {

    $this->log_lab_end_time = $log_lab_end_time;
    $this->student_object   = $student_object;
    $this->db               = $db;

  }


  public function get_extra_time_secs() {

    $query = 'SELECT
                extra_time
              FROM
                log_extra_time
              WHERE
                labID   = ?
              AND
                userID  = ?
              AND
                paperID = ?';

    $stmt  = $this->db->prepare( $query );

    $lab_id     = $this->get_lab_id();
    $student_id = $this->get_student_id();
    $paper_id   = $this->get_paper_id();

    $stmt->bind_param( 'iii'
                     , $lab_id
                     , $student_id
                     , $paper_id );

    $stmt->execute();
    $stmt->store_result();

    if( $stmt->num_rows < 1 ){
      $stmt->close();

      return false;
    }

    $bindResult = $stmt->bind_result( $extra_time_secs );

    $stmt->fetch();
    $stmt->close();

    return $extra_time_secs;

  }

  /*
   * This gets the
   * @return DateTime
  */
  public function get_end_date_datetime() {

    $query = 'SELECT
                end_date
              FROM
                log_extra_time
              WHERE
                labID   = ?
              AND
                userID  = ?
              AND
                paperID = ?';

    $stmt  = $this->db->prepare( $query );

    $lab_id     = $this->get_lab_id();
    $student_id = $this->get_student_id();
    $paper_id   = $this->get_paper_id();

    $stmt->bind_param( 'iii'
                      , $lab_id
                      , $student_id
                      , $paper_id );

    $stmt->execute();
    $stmt->store_result();

    $bindResult = $stmt->bind_result( $end_date );

    $num_results = $stmt->num_rows;

    $stmt->fetch();
    $stmt->close();

    // If no record exists then fall back to the default

    if( $num_results < 1 or $end_date === null ){
      return false;
    }

    $end_datetime = new DateTime();

    $end_datetime->setTimestamp( $end_date );

    return $end_datetime;

  }

  /*
   * @param int $invigilator_id
   * @param int $extra_time_minutes
   */
  public function save( $invigilator_id, $extra_time_minutes ) {

    if( $extra_time_minutes === 0 ){
      return 0;
    }

    $query    = 'INSERT INTO
                    log_extra_time
                                   ( labID
                                   , paperID
                                   , invigilatorID
                                   , userID
                                   , extra_time
                                   , end_date )
                  VALUES
                      ( ?
                      , ?
                      , ?
                      , ?
                      , ?
                      , ? )

                  ON DUPLICATE KEY UPDATE
                      extra_time = ?
                    , end_date   = ?';

    $stmt               = $this->db->prepare( $query );

    $extended_end_date_timestamp = $this->calculate_end_date_timestamp( $extra_time_minutes );

    $lab_id             = $this->get_lab_id();
    $paper_id           = $this->get_paper_id();
    $student_id         = $this->get_student_id();

    $extra_time_seconds = $extra_time_minutes * 60;

    $stmt->bind_param( 'iiiiiiii'
                     , $lab_id
                     , $paper_id
                     , $invigilator_id
                     , $student_id
                     , $extra_time_seconds
                     , $extended_end_date_timestamp
                     , $extra_time_seconds
                     , $extended_end_date_timestamp );

    $stmt->execute();
    $stmt->close();

    return $extra_time_seconds;

  }

  /*
   * @param int
   * @return int
   */
  private function calculate_end_date_timestamp( $extra_time_minutes ){

    $end_datetime       = $this->get_session_end_datetime();

    if( $end_datetime == false ){
      $end_datetime = $this->get_default_session_end_datetime();
    }

    $extra_time_seconds = $extra_time_minutes * 60;

    $end_timestamp      = $end_datetime->getTimestamp();

    $extended_end_date_timestamp = $end_timestamp + $extra_time_seconds;

    $paper_end_timestamp = $this->get_paper_end_timestamp();

    if( $extended_end_date_timestamp > $paper_end_timestamp ){
      $extended_end_date_timestamp = $paper_end_timestamp;
    }

    return $extended_end_date_timestamp;
  }

  /*
   * @return int
   */
  public function get_paper_exam_duration(){
    return $this->log_lab_end_time->get_paper_exam_duration();
  }

  /*
   * @return int
   */
  private function get_paper_id(){
    return $this->log_lab_end_time->get_paper_id();
  }

  /*
   * @return int
  */
  private function get_lab_id(){
    return $this->log_lab_end_time->get_lab_id();
  }

  /*
   * @return int
  */
  private function get_student_id(){
    return $this->student_object->get_user_ID();
  }

  /*
   * @return int
  */
  private function get_user_id(){
    return $this->student_object->get_user_ID();
  }

  /*
   * @return int
  */
  public function get_students_special_needs_percentage(){
    return $this->student_object->get_special_needs_percentage();
  }

  /*
   * @return int
  */
  private function get_end_date_timestamp(){
    $end_date_datetime = $this->get_end_date_datetime();
    if( $end_date_datetime === false ){
      return false;
    }
    return $end_date_datetime->getTimestamp();
  }

  /*
   * @return int
  */
  private function get_paper_end_timestamp(){
    return $this->log_lab_end_time->get_paper_end_timestamp();
  }

  /*
   * @return int
  */
  public function get_session_end_datetime(){
    return $this->log_lab_end_time->get_session_end_date_datetime();
  }

  /*
   * @return int
  */
  public function get_default_session_end_datetime(){
    return $this->log_lab_end_time->calculate_default_session_end_datetime();
  }


}