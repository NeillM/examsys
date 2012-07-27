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
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

Class QuestionAREA extends Question {
  
  protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');

  protected $points1 = '';
  protected $_requires_media = true;
  protected $_requires_correction_intermediate = true;
  protected $_requires_flash = true;
  protected $_allow_partial_marks = true;
  protected $_allow_negative_marks = true;
  protected $score_method = 'Allow partial Marks';
  public $max_options = 1;

  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);

    // Convert the max number of options into a list of variables
    $this->option_order = 'display_order';
    $this->_fields_editable[] = 'correct';
    $this->_fields_change = array('option_correct1', 'option_marks_correct', 'option_marks_partial', 'option_marks_incorrect');
    $this->_fields_unified = array('correct' => $this->_lang_strings['correctanswer'], 'marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);
    $this->_score_methods = array($this->_lang_strings['markperoption'], $this->_lang_strings['allowpartial']);
  }

  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   * @throws ValidationException
   */
  public function save($clear_checkout = true) {
    // Make sure 'correct' value is set for option
    if ((!isset($this->correct) or $this->correct = '') and $this->points1 != '' and count($this->options) > 0) {
      $this->set_points1($this->points1);
    }
    return parent::save($clear_checkout);
  }

  
  // ACCESSORS
  /**
   * Set the question leadin, stripping any carriage returns
   * @param string $value
   */
  public function set_leadin($value) {
    $value = str_replace("\r\n", ' ', $value);
    if ($value != $this->leadin) {
      $this->set_modified_field('leadin', $this->leadin);
      $this->leadin = $value;
    }
  }
  
}

