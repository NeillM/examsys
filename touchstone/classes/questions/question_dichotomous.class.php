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
 * Class for Dichotomous questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionDICHOTOMOUS extends Question {
  
  public $max_options = 15;
  protected $_answer_positive = 't';
  protected $_answer_negative = 'f';
  protected $display_method = 'TF_Positive';
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);
    
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
    $this->_display_methods = array('TF_NegativeAbstain' => $this->_lang_strings['tfnegativeabstain'], 'TF_Positive' => $this->_lang_strings['tfpositive'], 'YN_NegativeAbstain' => $this->_lang_strings['ynnegativeabstain'], 'YN_Positive' => $this->_lang_strings['ynpositive']);
    
    // 'correct' is not a unified field for Dichotomous questions
    $this->_fields_editable[] = 'correct';
  }

  /**
   * Get the labels for true/false options. These change depending on the score method
   */
  public function get_tf_labels() {
    if (substr($this->get_display_method(), 0, 2) == 'YN') {
      $labels = array('true' => $this->_lang_strings['abbryes'], 'false' => $this->_lang_strings['abbrno']);
    } else {
      $labels = array('true' => $this->_lang_strings['abbrtrue'], 'false' => $this->_lang_strings['abbrfalse']);
    }
    
    return $labels;
  }

  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct array of new correct answers
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    $errors = array();
    $changes = false;
    
    $i = 0;
    foreach ($this->options as $option) {
      if ($new_correct[$i] != $option->get_correct()) {
        $option->set_correct($new_correct[$i]);
        $changes = true;
        
        $opt_no = $i + 1;
        $this->add_unified_field_modification('correct', "Correct Option $opt_no", $old_correct, $new_correct, $this->_lang_strings['postexamchange']);
      }
      $i++;
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = 'Error saving data. Please try again';
    	  } else {
          // Remark the student's answers in 'log2'.
          $score_method = $this->get_score_method();
          switch ($score_method) {
            case 'TF_NegativeAbstain':
            case 'YN_NegativeAbstain':
              $negative = 1;
              break;
            case 'TF_NegativeAbstainHalf':
              $negative = 0.5;
              break;
            default:
              $negative = 0;
              break;
          }
        
    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->id, $paper_id);
          $result->execute();  
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $user_answers = str_split($user_answer);
            $mark = 0;
            
            for ($i=0; $i < count($new_correct); $i++) {
              if ($new_correct[$i] == $user_answers[$i]) {
                $mark++;
              } elseif ($user_answers[$i] == $this->get_answer_positive() or $user_answers[$i] == $this->get_answer_negative()) {
                // Don't subtract for unanswered/abstain
                $mark -= $negative;
              }
            }
            
            $updateLog = $this->_mysqli->prepare("UPDATE log2 SET mark=? WHERE user_answer=? AND q_id=? AND q_paper=?");
            $updateLog->bind_param('dsii', $mark, $user_answer, $this->id, $paper_id);
            $updateLog->execute();  
            $updateLog->close();
          }
          $result->free_result();
          $result->close();
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }
    
    return $errors;
  }
}

