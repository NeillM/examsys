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
* Repository for the log_lab_end_time table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once 'paperutils.class.php';
require_once 'propertyobject.class.php';

class LogLabEndTime {

  private $lab_id;
  private $invigilator_id;
  private $msg;

  /*
   * @var PropertyObject $property_object
  */

  private $property_object;

  /*
   * @var mysqli $db
   */

  private $db;

  public function __construct(                $lab_id
                             ,                $invigilator_id
                             , PropertyObject $property_object
                             , mysqli         $db ) {

    $this->lab_id          = $lab_id;
    $this->invigilator_id  = $invigilator_id;
    $this->property_object = $property_object;
    $this->db              = $db;
  }

  public function get_end_time() {

    $this->msg = '';

    $start_datetime = $this->get_paper_start_datetime();

    $start_timestamp = $start_datetime->getTimestamp();

    $query = 'SELECT
                MAX( end_time )
              FROM
                log_lab_end_time
              WHERE
                labID   = ?
              AND
                paperID = ?
              AND
                end_time > ?';

    $stmt  = $this->db->prepare( $query );

    $stmt->bind_param( 'iii'
                     , $this->lab_id
                     , $this->get_paper_id()
                     , $start_timestamp );

    $stmt->execute();
    $stmt->store_result();

    $bindResult = $stmt->bind_result( $end_time );

    $stmt->fetch();
    $stmt->close();

    // No result
    if( $end_time === null ){
      return $this->calculate_default_end_time();
    }

    $end_datetime = new DateTime();

    $end_datetime->setTimestamp( $end_time );

    return $end_datetime;

  }

  public function save() {

    $this->msg = '';

    $query    = 'INSERT INTO
                    log_lab_end_time

                            ( labID
                            , invigilatorID
                            , paperID
                            , end_time )
                   VALUES

                      ( ?
                      , ?
                      , ?
                      , ? )';

    $stmt       = $this->db->prepare( $query );

    $start_time = new DateTime();

    $end_datetime   = $this->calculate_end_time( $start_time );

    $end_time       = $end_datetime->getTimestamp();

    $stmt->bind_param( 'iiii'
                     , $this->lab_id
                     , $this->invigilator_id
                     , $this->get_paper_id()
                     , $end_time );

    $stmt->execute();
    $stmt->close();

    return $end_datetime;

  }


  public function delete() {

    $this->msg = '';

    $query    = 'DELETE FROM
                   log_lab_end_time
                 WHERE
                   labID   = ?
                 AND
                   paperID = ?';

    $stmt     = $this->db->prepare( $query );

    $stmt->bind_param( 'ii'
                     , $this->labID
                     , $this->get_paper_id() );
    $stmt->execute();
    $stmt->close();

  }


  public function get_message(){
    return $this->msg;
  }


  /*
   * Takes the current time and adds the exam duration to it to get the end time
   */
  private function calculate_end_time( DateTime $start_datetime){

    $exam_duration_mins = $this->get_paper_exam_duration();
    $exam_duration_secs = $exam_duration_mins * 60;

    $date_interval      = new DateInterval( 'PT'. $exam_duration_secs . 'S' );

    $start_datetime->add( $date_interval );

    $exam_end_time_stamp       = $start_datetime->getTimestamp();
    $paper_end_datetime        = $this->get_paper_end_datetime();
    $paper_end_timestamp       = $paper_end_datetime->getTimestamp();

    if( $exam_end_time_stamp > $paper_end_timestamp ){
      $this->msg = 'The extended exam end time exceeds the paper\'s end time.';
      return $paper_end_datetime;
    }

    return $start_datetime;

  }

  /*
   * This will return then start time + exam_duration
  */

  private function calculate_default_end_time(){
    $start_datetime = $this->get_paper_start_datetime();
    return $this->calculate_end_time( $start_datetime );
  }

  private function get_paper_id(){
    return $this->property_object->get_property_id();
  }

  private function get_paper_exam_duration(){
    return $this->property_object->get_exam_duration();
  }

  private function get_paper_start_datetime(){
    $start_date    = $this->property_object->get_start_date();
    return new DateTime( $start_date );
  }

  private function get_paper_end_datetime(){
    $end_date = $this->property_object->get_end_date();
    return new DateTime( $end_date );
  }


}





























