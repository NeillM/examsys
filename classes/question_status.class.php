<?php
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
 * Class for question statuses
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

Class QuestionStatus {

  public $id = -1;
  protected $name = '';
  protected $exclude_marking = false;
  protected $exclude_search = false;
  protected $is_default = false;

  private $_db;
  private $_lang_strings;

  function __construct($db, $lang_strings, $data) {
    $this->_db = $db;
    $this->_lang_strings = $lang_strings;

    // Check the type of $data
    if (is_array($data)) {
      // If it is an array, assume an associative array of fields for creating a new object (but not
      // saving it to the database)
      foreach($data as $field => $val) {
        $this->$field = $val;
      }
    } elseif (ctype_digit($data)) {
      // If it is an int use it as an ID for the database lookup
      $this->id = $data;
      if (!$this->get_question_status()) {
        throw new DatabaseException("Error loading question status");
      }
    } elseif ($data !== null) {
      throw new DataTypeException("Invalid question status data type");
    }
  }

  /**
   * Persist the object to the database
   * @return boolean True if object was saved
   */
  public function save() {
    $success = false;

    $sql = "INSERT INTO question_statuses(name, exclude_marking, exclude_search, is_default) VALUES(?, ?, ?, ?)";
    $result = $this->_db->prepare($sql);
    $result->bind_param('siii', $this->name, $this->exclude_marking, $this->exclude_search, $this->is_default);
    if ($result->execute()) {
      $success = true;
      $this->id = $this->_db->insert_id;
    }
    $result->close();

    return $success;
  }

  /**
   * Load question status data from the database
   * @return boolean True if status was loaded
   */
  private function get_question_status() {
    $success = false;

    $sql = "SELECT name, exclude_marking, exclude_search, is_default FROM question_statuses WHERE id = ?";

    $result = $this->_db->prepare($sql);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    $result->bind_result($this->name, $this->exclude_marking, $this->exclude_search, $this->is_default);
    if ($result->fetch()) {
      $success = true;
    }
    $result->close();

    return $success;
  }


  /**
   * @param string $name
   */
  public function set_name($name) {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function get_name() {
    return $this->name;
  }

  /**
   * @param boolean $exclude_marking
   */
  public function set_exclude_marking($exclude_marking) {
    $this->exclude_marking = $exclude_marking;
  }

  /**
   * @return boolean
   */
  public function get_exclude_marking() {
    return $this->exclude_marking;
  }

  /**
   * @param boolean $exclude_search
   */
  public function set_exclude_search($exclude_search) {
    $this->exclude_search = $exclude_search;
  }

  /**
   * @return boolean
   */
  public function get_exclude_search() {
    return $this->exclude_search;
  }

  /**
   * @param boolean $is_default
   */
  public function set_is_default($is_default) {
    $this->is_default = $is_default;
  }

  /**
   * @return boolean
   */
  public function get_is_default() {
    return $this->is_default;
  }

  /**
   * Get an array containing all existing statuses
   * @param  mysqli $db             Database link
   * @param  array $lang_strings    Language Strings
   * @return array[QuestionStatus]  Existing statuses
   */
  public static function get_all_statuses($db, $lang_strings) {
    $statuses = array();

    $sql = "SELECT id, name, exclude_marking, exclude_search, is_default FROM question_statuses ORDER BY display_order";

    $result = $db->prepare($sql);
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $name, $exclude_marking, $exclude_search, $is_default);
    while ($result->fetch()) {
      $data = array('id' => $id, 'name' => $name, 'exclude_marking' => $exclude_marking, 'exclude_search' => $exclude_search, 'is_default' => $is_default);
      $statuses[] = new QuestionStatus($db, $lang_strings, $data);
    }
    $result->close();

    return $statuses;
  }
}

?>
