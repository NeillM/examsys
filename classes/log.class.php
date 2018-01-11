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
* Log package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2018 onwards The University of Nottingham
*/

/**
 * Log helper class.
 */
class log {

  /**
   *  DB connection
   * @var mysqli
   */
  private $db;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  /**
   * Constructor
   */
  public function __construct() {
    $configObj = Config::get_instance();
    $this->db = $configObj->db;
  }

  /**
   * Get previous answers for a paper/user in log - used to load exam script
   * @param string $original_paper_type paper type identifier
   * @param string $paper_type paper type identifier
   * @param integer $metadataID unique identifier of paper/user entry in log
   * @param boolean $do_restart does this paper type allow multiple attempts
   * @param integer $current_screen what screen is the user on
   * @return array
   */
  public function get_previous_answers($original_paper_type, $paper_type, $metadataID, $do_restart, $current_screen) {
    $previous_duration = 0;
    $screen_pre_submitted = 0;
    $user_answers = array();
    $user_dismiss = array();
    $user_order = array();
    if ($paper_type == '_late') {
      // If we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below.
      $log_data = $this->db->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log_$original_paper_type WHERE metadataID = ?");
      $log_data->bind_param('i', $metadataID);
      $log_data->execute();
      $log_data->store_result();
      $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
      $used_question = $log_q_id;
      while ($log_data->fetch()) {
        $user_answers[$log_screen][$log_q_id] = $log_user_answer;
        $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
        $user_order[$log_screen][$log_q_id] = $option_order;
        // Bump up the current screen if restarting
        if ($do_restart and $log_screen > $current_screen) {
          $current_screen = $log_screen;
        }
        if ($log_screen == $current_screen) {
          $previous_duration = $log_duration;
          $screen_pre_submitted = 1;
        }
      }
      $log_data->close();
    }
    // Get user answers from whichever log is pointed to by log$paper_type
    $log_data = $this->db->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log$paper_type WHERE metadataID = ? ORDER BY id");
    $log_data->bind_param('i', $metadataID);
    $log_data->execute();
    $log_data->store_result();
    $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
    $used_question = $log_q_id;
    while ($log_data->fetch()) {
      $user_answers[$log_screen][$log_q_id] = $log_user_answer;
      $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
      $user_order[$log_screen][$log_q_id] = $option_order;
      // Bump up the current screen if restarting
      if ($do_restart and $log_screen > $current_screen) {
        $current_screen = $log_screen;
      }
      if ($log_screen == $current_screen) {
        $previous_duration = $log_duration;
        $screen_pre_submitted = 1;
      }
    }
    $log_data->close();
    return array('used_question' => $used_question,
        'user_answers' => $user_answers,
        'user_dismiss' => $user_dismiss,
        'user_order' => $user_order,
        'previous_duration' => $previous_duration,
        'screen_pre_submitted' => $screen_pre_submitted,
        'current_screen' => $current_screen);
  }
}