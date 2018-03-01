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
   * @param integer $metadataID unique identifier of paper/user entry in log
   * @param boolean $do_restart does this paper type allow multiple attempts
   * @param integer $current_screen what screen is the user on
   * @return array
   */
  public function get_previous_answers($original_paper_type, $metadataID, $do_restart, $current_screen) {
    $this->previousduration = 0;
    $this->screenpresubmitted = 0;
    $this->dorestart = $do_restart;
    $this->currentscreen = $current_screen;
    $this->metadataid = $metadataID;
    if ($this->papertype == '_late') {
      // If we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below.
      $loglate = $this->get_log();
      $this->papertype = $original_paper_type;
      return array_merge($loglate, $this->get_log());
    } else {
      // Get user answers from whichever log is pointed to by log$paper_type
     return $this->get_log();
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
      case '_late':
        $papertype = 'late';
        break;
      default:
        break;
    }
    $paperpluginns = 'plugins\\papers\\' . $papertype . '\\log';
    return new $paperpluginns();
  }
}