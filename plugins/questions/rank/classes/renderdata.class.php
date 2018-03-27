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

class renderdata extends \questiondata {
  use \mpqgetmarks;
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
    if ($this->scenario != '') {
      $this->displayscenario = true;
    }
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
    $this->displaydefault = true;
    if ($this->notes != ''){
      $this->displaynotes = true;
    }
    $this->displayleadin = true;
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid) {
    // Nothing to do
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswerid user answer
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option_answer($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if (!is_null($useranswerid)) {
      $rank_answers = explode(',', $useranswerid);
    } else {
      $rank_answers = array();
    }
    $total_rank_no = 0;
    $require_na = false;
    $question = $this->question;
    for ($i=0; $i<$this->optionnumber; $i++) {
      if ($question['options'][$i]['correct'] != 0 or $this->papertype == '3') {
        $total_rank_no++;
      }
      if ($question['options'][$i]['correct'] == 0) {
        $require_na = true;
      }
    }
    $tmp_user_answers = 0;

    for ($i=0; $i<count($rank_answers); $i++) {
      if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0) {
        $tmp_user_answers++;
      }
    }

    if ($this->scoremethod == 'Mark per Option') {
      $answers_needed = $this->optionnumber;
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
    if ($require_na) {
      if (isset($rank_answers[$option['tmppartid'] - 1]) and $rank_answers[$option['tmppartid'] - 1] == '0') {
        $option['selected'][0] = true;
      } else {
        $option['selected'][0] = false;
      }
    }
    $option['totalrank'] = $total_rank_no;
    for ($i=1; $i<=$total_rank_no; $i++) {
      if (isset($rank_answers[$option['tmppartid'] - 1]) and $i == $rank_answers[$option['tmppartid'] - 1]) {
        $option['selected'][$i]= true;
      } else {
        $option['selected'][$i] = false;
      }
    }
    if (substr($user_dismissid, $option['tmppartid']-1, 1) == '1') {
      $option['inact'] = true;
    } else {
      $option['inact'] = false;
    }
    $marks = $this->marks;
    if ($option['correct'] != 0) {
      $marks += $option['markscorrect'];
    } elseif ($this->scoremethod == 'Mark per Option') {
      $marks += $option['markscorrect'];
    }
    $this->marks = $marks;
    $this->set_opt($part_id, $option);
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswerid user answer
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function process_options($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if ($option['marksincorrect'] < 0) {
      $this->negativemarking = true;
    } else {
      $this->negativemarking = false;
    }
    // Write out the hidden field for the dismiss facility.
    if ($user_dismissid != '') {
       $this->dismiss = $user_dismissid;
    } else {
       $this->dismiss = str_repeat('0', $this->optionnumber);
    }
  }
}