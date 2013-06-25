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
 * A variable within a calculation question
 *
 * @author Rob Ingram
 */
class CalculationVar {
  protected $label;
  protected $min;
  protected $max;
  protected $decimals;
  protected $increment;

  function __construct($label, $min, $max, $decimals, $increment) {
    $this->label = $label;
    $this->min = $min;
    $this->max = $max;
    $this->decimals = $decimals;
    $this->increment = $increment;
  }

  /**
   * Get the label for the variable
   */
  public function get_label() {
    return $this->label;
  }

  /**
   * Set the label for the variable
   * @param string $value The minimum value that the variable may contain
   */
  public function set_label($value) {
    $this->label = $value;
  }

  /**
   * Get the minimum value for the variable
   */
  public function get_min() {
    return $this->min;
  }

  /**
   * Set the minimum value for the variable
   * @param double $value The minimum value that the variable may contain
   */
  public function set_min($value) {
    $this->min = $value;
  }

  /**
   * Get the maximum value for the variable
   */
  public function get_max() {
    return $this->max;
  }

  /**
   * Set the maximum value for the variable
   * @param double $value The maximum value that the variable may contain
   */
  public function set_max($value) {
    $this->max = $value;
  }

  /**
   * Get the number of decimal places for the variable
   */
  public function get_decimals() {
    return $this->decimals;
  }

  /**
   * Set the number of decimal places for the variable
   * @param integer $value The number of decimal places to which the variable should be calculated
   */
  public function set_decimals($value) {
    $this->decimals = $value;
  }

  /**
   * Get the increment for the variable
   */
  public function get_increment() {
    return $this->increment;
  }

  /**
   * Set the increment for the variable
   * @param float $value The increment between potential values that may be generated for the variable
   */
  public function set_increment($value) {
    $this->increment = $value;
  }

  /**
   * Return a JSON representation of the variable
   * @return string JSON version of the variable data
   */
  public function to_JSON() {
    $array_rep = array("${$this->label}" => array('min' => $this->min, 'max' => $this->max, 'inc' => $this->increment, 'dec' => $this->decimals));
    return json_encode($array_rep);
  }
}