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
 * Class for Correction behaviour with no action
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class ENHANCEDCALCCorrector {
  private $_mysqli;
  private $_lang_strings;
  private $_question;

  function __construct($mysqli, $lang_strings, $question) {
    $this->_mysqli = $mysqli;
    $this->_lang_strings = $lang_strings;
    $this->_question = $question;
  }

  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param mixed $new_correct Array of new values for fields that can be corrected
   * @param integer $paper_id
   * @param boolean $changes True if changes have been made by a previous corrector
   * @param integer $paper_type Integer index for type of paper
   * @return array[$string] Any errors encountered in the correction process
   */
  public function execute($new_correct, $paper_id, &$changes, $paper_type) {
    $errors = array();

    $marks_correct = $this->_question->get_marks_correct();
    $marks_incorrect = $this->_question->get_marks_incorrect();
    $marks_partial = $this->_question->get_marks_partial();

    $marks_unit = $this->_question->get_marks_unit();
    if ($marks_unit != $new_correct['marks_unit']) {
      $this->_question->set_marks_unit($new_correct['marks_unit']);
      $changes = true;

      $this->_question->add_unified_field_modification('marks_unit', 'marks_unit', $marks_unit, $new_correct['marks_unit'], $this->_lang_strings['postexamchange']);
    }

    $tolerance_full = $this->_question->get_tolerance_full();
    if ($tolerance_full != $new_correct['tolerance_full']) {
      $this->_question->set_tolerance_full($new_correct['tolerance_full']);
      $changes = true;

      $this->_question->add_unified_field_modification('tolerance_full', 'tolerance_full', $tolerance_full, $new_correct['tolerance_full'], $this->_lang_strings['postexamchange']);
    }

    $tolerance_partial = $this->_question->get_tolerance_partial();
    if ($tolerance_partial != $new_correct['tolerance_partial']) {
      $this->_question->set_tolerance_partial($new_correct['tolerance_partial']);
      $changes = true;

      $this->_question->add_unified_field_modification('tolerance_partial', 'tolerance_partial', $tolerance_partial, $new_correct['tolerance_partial'], $this->_lang_strings['postexamchange']);
    }

    $answer_precision = $this->_question->get_answer_precision();
    if ($answer_precision != $new_correct['answer_precision']) {
      $this->_question->set_answer_precision($new_correct['answer_precision']);
      $changes = true;

      $this->_question->add_unified_field_modification('answer_precision', 'answer_precision', $answer_precision, $new_correct['answer_precision'], $this->_lang_strings['postexamchange']);
    }

    $strict_display = $this->_question->get_strict_display();
    $new_strict_display = (isset($new_correct['strict_display'])) ? true : false;
    if ($strict_display != $new_strict_display) {
      $this->_question->set_strict_display($new_strict_display);
      $changes = true;

      $this->_question->add_unified_field_modification('strict_display', 'strict_display', $strict_display, $new_strict_display, $this->_lang_strings['postexamchange']);
    }

    $strict_zeros = $this->_question->get_strict_zeros();
    $new_strict_zeros = (isset($new_correct['strict_zeros'])) ? true : false;
    if ($strict_zeros != $new_strict_zeros) {
      $this->_question->set_strict_zeros($new_strict_zeros);
      $changes = true;

      $this->_question->add_unified_field_modification('strict_zeros', 'strict_zeros', $strict_zeros, $new_strict_zeros, $this->_lang_strings['postexamchange']);
    }


    // TODO: parse answers




    return $errors;
  }


}
