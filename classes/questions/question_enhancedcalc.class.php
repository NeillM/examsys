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
 *
 * Class for Area questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once __DIR__ . '/../calculation_var.class.php';
require_once __DIR__ . '/../calculation_answer.class.php';

Class QuestionENHANCEDCALC extends QuestionEdit {

  protected $units = '';
  protected $dp = 0;
  protected $strictdp = false;
  protected $fulltol = 0;
  protected $fulltoltyp = '#';
  protected $parttol = 0;
  protected $parttoltyp = '#';
  protected $variables = array();
  protected $answers = array();
  protected $score_method = 'Allow partial Marks';
  protected $show_units = true;
  protected $marks_unit = 0;
  public $max_options = 10;
  public $max_answers = 5;
  protected $variable_labels = array();
  protected $_allow_partial_marks = true;
  protected $_allow_change_marking_method = false;

  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'units', 'answer_decimals', 'tolerance_full', 'tolerance_partial', 'bloom', 'status');
  protected $_fields_change = array('option_correct', 'option_marks_correct', 'option_marks_incorrect', 'option_marks_partial', 'answer_decimals', 'tolerance_full', 'tolerance_partial');
  protected $_fields_settings = array('units', 'answer_decimals', 'tolerance_full', 'tolerance_partial');

  private $_variable_map = array();

  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);
    $this->_score_methods = array($this->_lang_strings['allowpartial']);
    $this->_fields_unified = array('correct' => $this->_lang_strings['correctanswer'], 'marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);

    // Convert the max number of options into a list of variables
    $this->variable_labels = range('A', chr(64 + $this->max_options));
    $this->option_order = 'display order';
  }

  // ACCESSORS

  /**
   * Get the variables for the question
   * @return integer
   */
  public function get_variables() {
    return $this->variables;
  }

  /**
   * Get the answers for the question
   * @return integer
   */
  public function get_answers() {
    return $this->answers;
  }

  /**
   * Get the units for the question
   * @return integer
   */
  public function get_units() {
    return $this->units;
  }

  /**
   * Set the units for the question
   * @param unknown_type $value
   */
  public function set_units($value) {
    if ($value != $this->units) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
    }
  }

  /**
   * Get the number of decimal places for the question
   * @return integer
   */
  public function get_answer_decimals() {
    return $this->answer_decimals;
  }

  /**
   * Set the number of decimal places for the question
   * @param unknown_type $value
   */
  public function set_answer_decimals($value) {
    if ($value != $this->answer_decimals) {
      $this->set_modified_field('answer_decimals', $this->answer_decimals);
      $this->answer_decimals = $value;
    }
  }

  /**
   * Get the full marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_full() {
    return $this->tolerance_full;
  }

  /**
   * Set the full marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_full($value) {
    if ($value != $this->tolerance_full) {
      $this->set_modified_field('tolerance_full', $this->tolerance_full);
      $this->tolerance_full = $value;
    }
  }

  /**
   * Get the partial marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_partial() {
    return $this->tolerance_partial;
  }

  /**
   * Set the partial marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_partial($value) {
    if ($value != $this->tolerance_partial) {
      $this->set_modified_field('tolerance_partial', $this->tolerance_partial);
      $this->tolerance_partial = $value;
    }
  }

  /**
   * Get the possible labels for variables
   * @return arar List of variable labels
   */
  public function get_variable_labels() {
    return $this->variable_labels;
  }

  /**
   * Get whether to display units for the question
   * @return integer
   */
  public function get_show_units() {
    return $this->show_units;
  }

  /**
   * Set whether to display units for the question
   * @param boolean $value
   */
  public function set_show_units($value) {
    if ($value != $this->show_units) {
      $this->set_modified_field('show_units', $this->show_units);
      $this->show_units = $value;
    }
  }

  /**
   * Get the marks adjustment for units for the question
   * @return integer
   */
  public function get_marks_unit() {
    return $this->marks_unit;
  }

  /**
   * Set the marks adjustment for units for the question
   * @param mixed $value
   */
  public function set_marks_unit($value) {
    if ($value != $this->marks_unit) {
      $this->set_modified_field('marks_unit', $this->marks_unit);
      $this->marks_unit = $value;
    }
  }

  /**
   * Unpack JSON string containing extra data into local fields
   */
  protected function unserialize_settings() {
    $extra = json_decode($this->settings, true);

    if (is_array($extra)) {
      foreach ($extra as $field => $value) {
        if (is_array($value)) {
          $func = "unserialize_$field";
          $this->$func($value);
        } else {
          $this->$field = $value;
        }
      }
    }
  }

  /**
   * Parse the data for the answers
   * @param  array $data Data describing the answers in the form of a formula and associated units
   */
  private function unserialize_answers($data) {
    foreach ($data as $fields) {
      $answer = new CalculationAnswer($fields['formula'], $fields['units']);
      $this->answers[] = $answer;
    }
  }

  /**
   * Parse the data for the variables
   * @param  array $data Data describing the variables indexed by the variable label
   */
  private function unserialize_vars($data) {
    foreach ($data as $label => $fields) {
      $var = new CalculationVar($label, $fields['min'], $fields['max'], $fields['dec'], $fields['inc']);
      $this->variables[] = $var;
      $this->_variable_map[$label] = $var;
    }
  }
}