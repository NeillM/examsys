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

namespace plugins\questions\rank;

/**
 *
 * Class for Rank rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class render extends \questionrender {

  /**
   * Question options dismissed
   * @var string
   */
  public $dismiss;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'rank';
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    if ($this->get('scenario') != '') {
      $this->displayscenario = true;
    }
    if ($this->get('qmedia') != '') {
      $this->displaymedia = true;
    }
    $this->displaydefault = true;
    if ($this->get('notes') != ''){
      $this->displaynotes = true;
    }
    $this->displayleadin = true;
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid id or name of user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $allowed_responses = 1) {
    // Nothing to do
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if (!is_null($useranswerid)) {
      $rank_answers = explode(',', $useranswerid);
    } else {
      $rank_answers = '';
    }
    $total_rank_no = 0;
    $require_na = false;
    $question = $this->get('question');
    for ($i=0; $i<$this->get('optionnumber'); $i++) {
      if ($question['options'][$i]['correct'] != 0 or $this->get('papertype') == '3') {
        $total_rank_no++;
      }
      if ($question['options'][$i]['correct'] == 0) $require_na = true;
    }
    $tmp_user_answers = 0;

    if ($rank_answers != '') {
      for ($i=0; $i<count($rank_answers); $i++) {
        if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0 and $rank_answers[$i] != 'u') {
          $tmp_user_answers++;
        }
      }
    }

    if ($this->get('scoremethod') == 'Mark per Option') {
      $answers_needed = $this->get('optionnumber');
    } else {
      $answers_needed = $total_rank_no;
    }
    $option['unans'] = false;
    if (isset($rank_answers[$option['tmppartid'] - 1]) and $rank_answers[$option['tmppartid'] - 1] == 'u' and $screen_pre_submitted == 1 and $tmp_user_answers < $answers_needed) {
      $option['unans'] = true;
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
    $option['na'] = false;
    if ($require_na) {
      $option['na'] = true;
      if (isset($rank_answers[$option['tmppartid'] - 1]) and $rank_answers[$option['tmppartid'] - 1] == '0') {
        $option['selected'] = true;
      } else {
        $option['selected'] = false;
      }
    }
    $option['totalrank'] = $total_rank_no;
    for ($i=1; $i<=$total_rank_no; $i++) {
      if (isset($rank_answers[$option['tmppartid'] - 1]) and $i == $rank_answers[$option['tmppartid'] - 1]) {
        $option['selected'] = true;
      } else {
        $option['selected'] = false;
      }
    }
    if (substr($user_dismissid, $option['tmppartid']-1, 1) == '1') {
      $option['inact'] = true;
    } else {
      $option['inact'] = false;
    }
    $marks = $this->get('marks');
    if ($option['correct'] != 0) {
      $marks += $option['markscorrect'];
    } elseif ($this->get('scoremethod') == 'Mark per Option') {
      $marks += $option['markscorrect'];
    }
    $this->marks = $marks;
    $this->set_opt($part_id, $option);
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_additional_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if ($option['marksincorrect'] < 0) {
      $this->negativemarking = true;
    }
    // Write out the hidden field for the dismiss facility.
    if ($user_dismissid != '') {
       $this->dismiss = $user_dismissid;
    } else {
       $this->dismiss = str_repeat('0', $this->get('optionnumber'));
    }
  }
}