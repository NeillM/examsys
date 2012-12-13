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
* Class for the timer logic
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

class Timer {

  /*
   * @var LogStartTime $log_start_time
   */

  private $log_start_time;
  private $exam_duration;
  private $start_time;

  public function __construct( LogStartTime $log_start_time
                                          , $exam_duration ) {
    $this->log_start_time = $log_start_time;
    $this->exam_duration  = $exam_duration;
  }

  public function get_start_time(){

    if( $this->start_time == null ){
      $this->start_time = $this->log_start_time->get_start_time();
    }

    return $this->start_time;
  }

  public function start(){
    return $this->log_start_time->insert();
  }

  public function is_started(){
    return ( $this->get_start_time() !== null );
  }

  public function reset(){
    return $this->log_start_time->delete();
  }

  public function calculate_remaining_time() {

    $exam_duration  = $this->exam_duration;
    $exam_duration  = $exam_duration * 60;

    // get existing start time or create a new one
    $start_time      = $this->get_start_time();

    if( $start_time === false){
      return $exam_duration;
    }

    $start_time      = strtotime( $start_time );
    $now             = time();
    $time_elapsed    = $now - $start_time;
    $remaining_time  = $exam_duration - $time_elapsed;

    if( $remaining_time < 1 ){
      $remaining_time = 0;
    }

    return $remaining_time;

  }



}

?>