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
 * Class for Multiple Response options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

Class OptionAREA extends Option {
  protected $correct_full;
  protected $error_full;
  protected $correct_partial;
  protected $error_partial;

  protected $_fields_editable = array('correct_full', 'error_full', 'correct_partial', 'error_partial');

  /**
   * Is this option blank?
   * @return boolean
   */
  public function is_blank() {
    return ($this->correct == '');
  }
  
  /**
   * Check that the minimum set of fields exist in the given data to create a new option 
   * @param array $data
   * @param array $files expects PHP FILES array
   * @param integer $index option number
   * @return boolean
   */
  public function minimum_fields_exist($data, $files, $index) {
    return true;
  }

  /**
   * @param string $value
   */
  public function set_correct($value) {
    if (strpos($value, ';') !== false) {
      $tmp = explode(';', $value);
      $value = $tmp[1];
    }
    $value = rtrim($value, ', ');
    parent::set_correct($value);
  }

  /**
   * @param $correct_full
   */
  public function set_correct_full($value) {
    if ($value != $this->get_correct_full()) {
      $this->set_modified_field('correct_full', $this->correct_full);
      $this->correct_full = $value;
      $this->set_text('dummy');
    }
  }

  /**
   * @return mixed
   */
  public function get_correct_full() {
    $this->get_text();
    return $this->correct_full;
  }

  /**
   * @param $correct_partial
   */
  public function set_correct_partial($value) {
    if ($value != $this->get_correct_partial()) {
      $this->set_modified_field('correct_partial', $this->correct_partial);
      $this->correct_partial = $value;
      $this->set_text('dummy');
    }
  }

  /**
   * @return mixed
   */
  public function get_correct_partial() {
    $this->get_text();
    return $this->correct_partial;
  }

  /**
   * @param $error_full
   */
  public function set_error_full($value) {
    if ($value != $this->get_error_full()) {
      $this->set_modified_field('error_full', $this->error_full);
      $this->error_full = $value;
      $this->set_text('dummy');
    }
  }

  /**
   * @return mixed
   */
  public function get_error_full() {
    $this->get_text();
    return $this->error_full;
  }

  /**
   * @param $error_partial
   */
  public function set_error_partial($value) {
    if ($value != $this->get_error_partial()) {
      $this->set_modified_field('error_partial', $this->error_partial);
      $this->error_partial = $value;
      $this->set_text('dummy');
    }
  }

  /**
   * @return mixed
   */
  public function get_error_partial() {
    $this->get_text();
    return $this->error_partial;
  }

  /**
   * Set the option text
   * @param string $value
   */
  public function set_text($value) {
    $this->text = $this->correct_full . ',' . $this->error_full . ',' . $this->correct_partial . ',' . $this->error_partial;
  }

  /**
   * Extract the option text into pseudo-properties
   * @return string
   */
  public function get_text() {
    if ($this->text != '') {
      $parts = explode(',', $this->text);
      $this->correct_full = $parts[0];
      $this->error_full = $parts[1];
      $this->correct_partial = $parts[2];
      $this->error_partial = $parts[3];
    }
    return $this->text;
  }
}

