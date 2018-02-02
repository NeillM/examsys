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
 * Class for MRQ rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class mrqrender extends questionrender {

  /**
   * Question 'other' option selected state
   * @var boolean
   */
  protected $otherselected;

  /**
   * Question 'abstain' option selected state
   * @var boolean
   */
  protected $abstainselected;

  /**
   * Question options dismissed
   * @var string
   */
  protected $dismiss;

  /**
   * Number of allowed responses to the question
   * @var integer 
   */
  protected $allowedresponses;

  /**
   * Default other selected state
   */
  const default_otherselected = false;

  /**
   * Default abstain selected state
   */
  const default_abstainselected = false;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->set('questiontype', 'mrq');
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    if ($this->get('scenario') != '') {
      $this->set('displayscenario', true);
    }
    if ($this->get('qmedia') != '') {
      $this->set('displaymedia', true);
    }
    $this->set('displaydefault', true);
    if ($this->get('notes') != ''){
      $this->set('displaynotes', true);
    }
    $this->set('displayleadin', true);
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid id or name of user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $allowed_responses = 1) {
    if (!is_null($useranswerid)) {
      $answer_parts = explode(':', $user_answers[$current_screen][$q_id]);
      $len_answer = strlen($answer_parts[0]);
    } else {
      $len_answer = 0;
    }
    if (isset($answer_parts) and $answer_parts[0] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
      $this->set('unanswered', true);
    } else {
      $this->set('unanswered', false);
    }
    $this->set('allowedresponses', $allowed_responses);
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option($part_id, $useranswerid, $user_dismissid, &$marks, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if (substr($useranswerid, $option['tmppartid']-1, 1) === 'y') {
      $option['selected'] = true;
    } else {
      $option['selected'] = false;
    }
    if (substr($user_dismissid,$part_id-1,1) === '1') {
      $option['inact'] = true;
    } else {
      $option['inact'] = false;
    }
    $option['optiontextdisplay'] = false;
    if ($option['optiontext'] != '') {
      $option['optiontextdisplay'] = true;
    }
    $option['displayoptionmedia'] = false;
    if ($option['omedia'] != '') {
      $option['displayoptionmedia'] = true;
    }
    if ($this->get('scoremethod') === 'Mark per Option') {
      if ($option['correct'] === 'y') $marks += $option['markscorrect'];  // Mark for correct options only
    } elseif ($this->get('scoremethod') === 'Mark per Question') {
      if ($part_id == 1) {
        $marks += $option['markscorrect'];
      }
    } else {
      $marks += $option['markscorrect'];  // Mark for each and every item
    }
    if ($this->get('displaymethod') === 'other') {
      $pid = $this->get('partid') + 1;
      $this->set('partid', $part_id) ;
      if (!is_null($useranswerid) and substr($useranswerid,($pid - 1),1) == 'y') {
        $this->set('otherselected', true);
      }
      $this->set('other', substr($useranswerid, $pid));
    }
    if ($option['marksincorrect'] < 0) {
      $this->set('negativemarking', true);
      if ($useranswerid === 'a') {
        $this->set('abstainselected', true);
      }
    }
    $this->set_opt($part_id, $option);
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_additional_option($part_id, $useranswerid, $user_dismissid) {
    $option = $this->get_opt($part_id);
    if ($this->get('displaymethod') === 'other') {
      $part_id = $this->get('partid') + 1;
      $this->set('partid', $part_id) ;
      if (!is_null($useranswerid) and substr($useranswerid,($part_id - 1),1) == 'y') {
        $this->set('otherselected', true);
      }
      $this->set('other', substr($useranswerid, $part_id));
    }
    if ($option['marksincorrect'] < 0) {
      $this->set('negativemarking', true);
      if ($useranswerid === 'a') {
        $this->set('abstainselected', true);
      }
    }
    // Write out the hidden field for the dismiss facility.
    if ($user_dismissid != '') {
       $this->set('dismiss', $user_dismissid);
    } else {
       $this->set('dismiss', str_repeat('0', $this->get('optionnumber')));
    }
  }
}