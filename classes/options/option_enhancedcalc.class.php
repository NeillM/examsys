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
 * Class for Extended Calculation question options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

Class OptionENHANCEDCALC extends OptionEdit {

  // Option level pseudo-properties for Calculation
  private $variable = '';
  protected $min = '';
  protected $max = '';
  protected $decimals = '';
  protected $increment = '';
  protected $formula = '';
  protected $units = '';

  protected $_fields_editable = array('min', 'max', 'decimals', 'increment', 'formula', 'units');

  /**
   * This option is not directly persisted
   * @return integer
   */
  public function save($option_number = 0) {
    $logger = new Logger($this->_mysqli);
    $this->save_changes($logger, $this->_number);
    return true;
  }

  /**
   * Is this option blank?
   * @return boolean
   */
  public function is_blank() {
    $this->get_text();
    return ($this->min == '' and $this->max == '' and $this->formula =='' and $this->units = '');
  }

  /**
   * Check that the minimum set of fields exist in the given data to create a new option
   * @param array $data
   * @param array $files expects PHP FILES array
   * @param integer $index option number
   * @return boolean
   */
  public function minimum_fields_exist($data, $files, $index) {
    return ((isset($data["option_min$index"]) and $data["option_min$index"] != '') or $data["option_formula$index"] != '');
  }

  // ACCESSORS

  /**
   * Get the variable name for the option
   * @return integer
   */
  public function get_variable() {
    return $this->variable;
  }

  /**
   * Set the variable for the option
   * @param string $value
   */
  public function set_variable($value) {
    $this->variable = $value;
  }

  /**
   * Get the minimum value for the option
   * @return integer
   */
  public function get_min() {
    return $this->min;
  }

  /**
   * Set the minimum value for the option
   * @param integer $value
   */
  public function set_min($value) {
    if ($value != $this->min) {
      $this->set_modified_field('min', $this->min);
      $this->min = $value;
    }
  }

  /**
   * Get the maximum value for the option
   * @return integer
   */
  public function get_max() {
    return $this->max;
  }

  /**
   * Set the maximum value for the option
   * @param integer $value
   */
  public function set_max($value) {
    if ($value != $this->max) {
      $this->set_modified_field('max', $this->max);
      $this->max = $value;
    }
  }

  /**
   * Get the number of decimal places for the option
   * @return integer
   */
  public function get_decimals() {
    return $this->decimals;
  }

  /**
   * Set the number of decimal places for the option
   * @param integer $value
   */
  public function set_decimals($value) {
    if ($value != $this->decimals) {
      $this->set_modified_field('decimals', $this->decimals);
      $this->decimals = $value;
    }
  }

  /**
   * Get the increment for the option
   * @return integer
   */
  public function get_increment() {
    return $this->increment;
  }

  /**
   * Set the increment for the option
   * @param integer $value
   */
  public function set_increment($value) {
    if ($value != $this->increment) {
      $this->set_modified_field('increment', $this->increment);
      $this->increment = $value;
    }
  }

  /**
   * Get the formula for the option
   * @return string
   */
  public function get_formula() {
    return $this->formula;
  }

  /**
   * Set the formula for the option
   * @param string $value
   */
  public function set_formula($value) {
    if ($value != $this->formula) {
      $this->set_modified_field('formula', $this->formula);
      $this->formula = $value;
    }
  }

  /**
   * Get the units for the option
   * @return string
   */
  public function get_units() {
    return $this->units;
  }

  /**
   * Set the units for the option
   * @param string $value
   */
  public function set_units($value) {
    if ($value != $this->units) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
    }
  }

  // PRIVATE / PROTECTED METHODS


  /**
   * Track the addition of a new option.
   * @param Logger $option_number
   * @param integer $option_number
   */
  protected function track_new($logger, $option_number) {
    $logger->track_change('New Variable', $this->question_id, $this->_user_id, '', $this->min . ',' . $this->max, 'Variable $' . chr(64 + $option_number));
  }

  /**
   * Track the change of an option.  The message may be different in other question types so allow this method to be overridden
   * @param Logger $option_number
   * @param integer $option_number
   * @param mixed $old
   * @param mixed $new
   * @param string $field
   */
  protected function track_change($logger, $option_number, $old, $new, $field) {
    $logger->track_change('Edit ' . ucwords($field), $this->question_id, $this->_user_id, $old, $new, 'Variable $' . chr(64 + $option_number));
  }

  /**
   * Track the deletion of an option
   * @param Logger $option_number
   * @param integer $option_number
   */
  protected function track_delete($logger, $option_number) {
    $logger->track_change('Deleted Variable', $this->question_id, $this->_user_id, '', '', 'Variable $' . chr(64 + $option_number));
  }
}

