<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Class for Multiple Response options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class OptionSCT extends Option {
  
  public function save($option_number = 0) {
    $this->set_marks_correct($this->correct / $this->_question->get_max_experts());
    
    return parent::save($option_number);
  }

  /**
   * Set the option correct answer
   * @param string $value
   */
  public function set_correct($value) {
    if($value != $this->correct and !in_array('correct', array_keys($this->_question->get_unified_fields()))) {
      $this->set_modified_field('correct', $this->correct, "Option #{$this->_number} Experts");
    }
    $this->correct = $value;
  }
  /**
   * Set the option marks for correct answers
   * @param string $value
   */
  public function set_marks_correct($value) {
    $this->marks_correct = $value;
  }
}

