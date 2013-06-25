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
class CalculationAnswer {
  protected $formula;
  protected $units;

  function __construct($formula, $units) {
    $this->formula = $formula;
    $this->units = $units;
  }

  /**
   * Get the formula for the answer
   */
  public function get_formula() {
    return $this->formula;
  }

  /**
   * Set the formula for the answer
   * @param string $value The formula of the answer
   */
  public function set_formula($value) {
    $this->label = $formula;
  }

  /**
   * Get the units for the answer
   */
  public function get_units() {
    return $this->units;
  }

  /**
   * Set the units for the answer
   * @param string $value The units for the answer
   */
  public function set_units($value) {
    $this->units = $value;
  }

  /**
   * Return a JSON representation of the variable
   * @return string JSON version of the variable data
   */
  public function to_JSON() {
    $array_rep = array('formula' => $this->formula, 'units' => $this->units);
    return json_encode($array_rep);
  }
}