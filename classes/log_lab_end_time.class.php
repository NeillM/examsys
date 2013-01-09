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
 * Repository class for the log_lab_end_time table
 *
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class LogLabEndTime {

  /*
   * @var LabObject $lab_object
  */

  private $lab_object;

  private $msg;

  /*
   * @var PropertyObject $property_object
  */

  private $property_object;

  /*
   * @var mysqli $db
   */

  private $db;

  /*
   * @param LabObject $lab_object
   * @param PropertyObject $property_object
   * @param mysqli $db
   */
  public function __construct(LabObject $lab_object    , PropertyObject $property_object    , mysqli $db) {

    $this->lab_object = $lab_object;
    $this->property_object = $property_object;
    $this->db = $db;
  }

  /* Gets the exam session's current end time stored when the invigilator clicked 'Start'
   * @return DateTime
   */
  public function get_session_end_date_datetime() {

    $this->msg = '';

    $start_datetime = $this->get_paper_start_datetime();

    $start_timestamp = $start_datetime->getTimestamp();

    //$query = 'SELECT MAX( end_time ) as end_timestamp FROM log_lab_end_time WHERE labID   = ? AND paperID = ? AND end_time > ?';
    $query = 'SELECT end_time as end_timestamp FROM log_lab_end_time WHERE labID   = ? AND paperID = ? AND end_time > ? ORDER BY id DESC LIMIT 1';

    $stmt = $this->db->prepare($query);

    $lab_id = $this->get_lab_id();
    $paper_id = $this->get_paper_id();

    $stmt->bind_param('iii', $lab_id, $paper_id, $start_timestamp);

    $stmt->execute();
    $stmt->store_result();

    $bindResult = $stmt->bind_result($end_timestamp);

    $stmt->fetch();
    $stmt->close();

    // No result
    if ($end_timestamp === NULL) {
      return FALSE;
    }

    $end_datetime = new DateTime();

    $end_datetime->setTimestamp($end_timestamp);

    return $end_datetime;

  }

  /*
   * Called when the invigilator clicks the 'Start' button and extends the exam session's end time
   * @return DateTime
  */

  function listrecordswithextratime() {

    $data=array();

    $query = 'SELECT userID FROM log_extra_time WHERE labID   = ? AND paperID = ?';

    $stmt = $this->db->prepare($query);

    $lab_id = $this->get_lab_id();
    $paper_id = $this->get_paper_id();

    $stmt->bind_param('ii', $lab_id, $paper_id);

    $stmt->execute();
    $stmt->store_result();

    $bindResult = $stmt->bind_result($uid);

    $num_results = $stmt->num_rows;

    while($stmt->fetch()) {
      $data[]=$uid;
    }
    $stmt->close();
    return $data;
  }

  public function save($invigilator_id,$time = NULL) {

    $this->msg = '';

    $query = 'INSERT INTO log_lab_end_time ( labID, invigilatorID, paperID, end_time ) VALUES ( ?, ?, ?, ? )';

    $stmt = $this->db->prepare($query);
if(is_null($time)) {
    $start_time_datetime = new DateTime();

    $end_datetime = $this->calculate_end_datetime($start_time_datetime);
} else {
  $end_datetime = new DateTime();
  $end_datetime->setTime(0,0,0);
  $dateinterval = new DateInterval($time);
  $end_datetime->add($dateinterval);
}
    $end_time = $end_datetime->getTimestamp();
$tz=$this->property_object->get_time_zone();

    print $tz . '##' . $time . '::'. $end_time . '@@' . var_export($end_datetime,TRUE);

    $lab_id = $this->get_lab_id();
    $paper_id = $this->get_paper_id();

    $stmt->bind_param('iiii', $lab_id, $invigilator_id, $paper_id, $end_time);

    $stmt->execute();
    $stmt->close();

    $listofrecordstoupodate=$this->listrecordswithextratime();
    $log_lab_end_time = new LogLabEndTime($this->lab_object, $this->property_object, $this->db);
    foreach($listofrecordstoupodate as $uid) {
      $stuobj['user_ID']=$uid;


      $ext_timeobj=new LogExtraTime($this,$stuobj,$this->db);
      $ext_time=(int)$ext_timeobj->get_extra_time_secs()/60; // give time in minutes that it needs next
      $ext_timeobj->save($invigilator_id,$ext_time);
      unset($ext_timeobj);
    }

    return $end_datetime;

  }


  public function delete() {

    $this->msg = '';

    $query = 'DELETE FROM log_lab_end_time WHERE labID   = ? AND paperID = ?';

    $stmt = $this->db->prepare($query);

    $paper_id = $this->get_paper_id();

    $stmt->bind_param('ii', $this->labID, $paper_id);

    $stmt->execute();
    $stmt->close();

  }


  public function get_message() {
    return $this->msg;
  }


  /*
   * Takes current time and adds the exam duration to it to get the end time for the current session
   * @param DateTime $start_datetime
   */
  private function calculate_end_datetime(DateTime $start_datetime) {

    $exam_duration_mins = $this->get_paper_exam_duration();
    $exam_duration_secs = $exam_duration_mins * 60;

    // Add extra time

    $date_interval = new DateInterval('PT' . $exam_duration_secs . 'S');

    $start_datetime->add($date_interval);

    $paper_end_datetime = $this->get_paper_end_datetime();

    if ($start_datetime > $paper_end_datetime) {
      $this->msg = 'The extended exam end time exceeds the paper\'s end time.';

      return $paper_end_datetime;
    }

    return $start_datetime;

  }

  /*
   * This is called if there is no record in log_lab_end_time
   * It then defaults to using paper's start time and then adds the exam duration to get the end time
   * @return DateTime
  */
  public function calculate_default_session_end_datetime() {
    $start_datetime = $this->get_paper_start_datetime();

    return $this->calculate_end_datetime($start_datetime);
  }

  /*
   * @return int
   */
  public function get_paper_id() {
    return $this->property_object->get_property_id();
  }

  /*
   * @return int
   */
  public function get_lab_id() {
    return $this->lab_object->get_id();
  }

  /*
   * @return int
   */
  public function get_paper_exam_duration() {
    return $this->property_object->get_exam_duration();
  }

  /*
   * @return DateTime
  */
  public function get_paper_start_datetime() {
    $start_date = $this->property_object->get_start_date();

    return new DateTime($start_date);
  }

  /*
   * @return DateTime
  */
  public function get_paper_end_datetime() {
    $end_date = $this->property_object->get_end_date();

    return new DateTime($end_date);
  }

  /*
   * return int
   */
  public function get_paper_end_timestamp() {
    $paper_end_datetime = $this->get_paper_end_datetime();
    if ($paper_end_datetime === FALSE) {
      return FALSE;
    }

    return $paper_end_datetime->getTimestamp();
  }

}





























