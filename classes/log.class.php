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
abstract class log {

  /**
   *  DB connection
   * @var mysqli
   */
  protected $db;

  /**
   * Unique identifier of paper/user entry in log
   * @var integer 
   */
  protected $metadataid;

  /**
   * Does this paper type allow multiple attempts
   * @var boolean 
   */
  protected $dorestart;

  /**
   * What screen is the user on
   * @var integer 
   */
  protected $currentscreen;

  /**
   * Paper type
   * @var string
   */
  protected $papertype;

  /**
   * Screen duration
   * @var integer
   */
  protected $previousduration;

  /**
   * Screen previously submitted
   * @var boolean
   */
  protected $screenpresubmitted;

  /**
   * Flag to indicate if paper type uses log late table
   * @var boolean
   */
  protected $late;

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
    $this->late = false;
  }

  /**
   * Get previous answers for a paper/user in log - used to load exam script
   * @param string $papertype paper type identifier
   * @param integer $metadataID unique identifier of paper/user entry in log
   * @param boolean $do_restart does this paper type allow multiple attempts
   * @param integer $current_screen what screen is the user on
   * @param boolean so we need to check the log late table
   * @return array
   */
  public function get_previous_answers($papertype, $metadataID, $do_restart, $current_screen, $check_log_late = false) {
    $this->previousduration = 0;
    $this->screenpresubmitted = 0;
    $this->dorestart = $do_restart;
    $this->currentscreen = $current_screen;
    $this->metadataid = $metadataID;
    if ($check_log_late and $this->late) {
      // If we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below.
      return array_merge($this->get_log(), $this->get_log_late());
    } else {
      // Get user answers from whichever log is pointed to by log$paper_type
     return $this->get_log();
    }
  }

  /**
   * Get entries from the log late table
   * @return array
   */
  public function get_log_late() {
    $user_answers = array();
    $user_dismiss = array();
    $user_order = array();
    $used_questions = array();
    $log_data = $this->db->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log_late WHERE metadataID = ? ORDER BY id");
    $log_data->bind_param('i', $this->metadataid);
    $log_data->execute();
    $log_data->store_result();
    $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
    while ($log_data->fetch()) {
      $user_answers[$log_screen][$log_q_id] = $log_user_answer;
      $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
      $user_order[$log_screen][$log_q_id] = $option_order;
      $used_questions[$log_q_id] = $log_q_id;
      // Bump up the current screen if restarting
      if ($this->dorestart and $log_screen > $this->currentscreen) {
        $this->currentscreen = $log_screen;
      }
      if ($log_screen == $this->currentscreen) {
        $this->previousduration = $log_duration;
        $this->screenpresubmitted = 1;
      }
    }
    $log_data->close();
    return array('used_questions' => $used_questions,
      'user_answers' => $user_answers,
      'user_dismiss' => $user_dismiss,
      'user_order' => $user_order,
      'previous_duration' => $this->previousduration,
      'screen_pre_submitted' => $this->screenpresubmitted,
      'current_screen' => $this->currentscreen);
  }

  /**
   * Update screen variables to keep track of user journey
   * @param integer $log_screen screen identifier
   * @param integer $log_duration time in seconds spent on screen
   */
  public function process_screen_variables($log_screen, $log_duration) {
    // Bump up the current screen if restarting
    if ($this->dorestart and $log_screen > $this->currentscreen) {
      $this->currentscreen = $log_screen;
    }
    if ($log_screen == $this->currentscreen) {
      $this->previousduration = $log_duration;
      $this->screenpresubmitted = 1;
    }
  }

  /**
   * Get paper logs
   */
  abstract public function get_log();

  /**
   * Get paper log class
   * @param string $papertype paper type
   * @return class
   */
  public static function get_paperlog($papertype) {
    switch ($papertype) {
      case '0':
        $papertype = 'formative';
        break;
      case '1':
        $papertype = 'progressive';
        break;
      case '2':
        $papertype = 'summative';
        break;
      case '3':
        $papertype = 'survey';
        break;
      case '4':
        $papertype = 'osce';
        break;
      case '5':
        $papertype = 'offline';
        break;
      case '6':
        $papertype = 'peer_review';
        break;
      default:
        throw new \Exception("Unsupported paper type.");
    }
    $paperpluginns = 'plugins\\papers\\' . $papertype . '\\log';
    return new $paperpluginns();
  }
}