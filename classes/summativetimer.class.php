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
* Class containing the timer logic for summative exams
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class SummativeTimer {

  /*
   * @var LogExtraTime $log_extra_time
  */

  private $log_extra_time;
  private $exam_duration;
  private $start_time;

  /*
   * @param LogExtraTime $log_extra_time
   */

  public function __construct( LogExtraTime $log_extra_time ) {
    $this->log_extra_time = $log_extra_time;
  }

  /*
   * This takes the end time for the student's exam session and calculates the time remaining by
   * subtracting the current time stamp from the session end time stamp. It then adds any
   * special needs allowance
   *
   * @return int
   */
  public function calculate_remaining_time_secs() {

    $session_end_datetime = false;

    //has the student been given extra time?
    $session_end_timestamp = $this->get_extra_end_date_timestamp();

    if( $session_end_timestamp === false){
      //has the lab got an end time set?
      $session_end_datetime = $this->get_session_end_datetime();
      var_dump('ONE',$session_end_datetime);
    }

    if( $session_end_timestamp === false AND $session_end_datetime === false ){
      //if we are here student has no extra time set and the lad has no end time set 
      //use the paper start time and duration to caculate end time
      $paper_start_time = $this->get_paper_exam_start_time();
      $paper_duration_sec = $this->get_paper_exam_duration() * 60;
      var_dump('TWO',$paper_start_time,$paper_duration_sec);
      $paper_start_time->add(new DateInterval('PT' . $paper_duration_sec . 'S'));
      var_dump('THREE',$paper_start_time);
      $session_end_datetime = $paper_start_time;
    }

    if($session_end_datetime !== false) {
      $session_end_timestamp = $session_end_datetime->getTimestamp();
      var_dump('FOUR',$paper_start_time);
    }

    $now_timestamp = time();

    $remaining_time_secs = $session_end_timestamp - $now_timestamp;

    $special_needs_secs = $this->calculate_special_needs_secs();
    $remaining_time_secs = $remaining_time_secs + $special_needs_secs;

    if( $remaining_time_secs < 1 ){
      $remaining_time_secs = 0;
    }

    return $remaining_time_secs;
  }

  /*
   * @return int
   */
  private function get_paper_exam_start_time(){
    return $this->log_extra_time->get_paper_exam_start_time();
  }

  /*
   * @return int
   */
  private function get_paper_exam_duration(){
    return $this->log_extra_time->get_paper_exam_duration();
  }

  /*
   * @return int
   */
  private function get_extra_end_date_timestamp(){
    $end_date_datetime = $this->log_extra_time->get_end_date_datetime();

    if( $end_date_datetime === false ){
      return false;
    }
    return $end_date_datetime->getTimestamp();
  }

  /*
   * @return DateTime
   */
  private function get_end_date_datetime(){
    return $this->get_end_date_datetime();
  }

  /*
   * @return int
   */
  private function calculate_special_needs_secs(){
    $students_special_needs_percentage = $this->log_extra_time->get_students_special_needs_percentage();
    $exam_duration                     = $this->get_paper_exam_duration();

    return ( $exam_duration * 60  ) * ( $students_special_needs_percentage / 100 );
  }

  /*
   * return int
   */
  private function get_session_end_datetime(){
    return $this->log_extra_time->get_session_end_datetime();
  }

  /*
   * return int
   */
  private function get_default_session_end_datetime(){
    return $this->log_extra_time->get_default_session_end_datetime();
  }


}
