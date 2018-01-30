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
 * Class for MCQ rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class mcqrender extends questionrender {

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->set('questiontype', 'mcq');
  }

  /**
   * Disable/Enable display of question header sections
   */
  public function set_question_head() {
    if ($this->get('scenario') != '') {
      $this->set('displayscenario', true);
    }
    if ($this->get('q_media') != '') {
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
   * @param boolean $useranswered is the user answer a non answer
   */
  public function set_question($screen_pre_submitted, $useranswered) {
    if ($useranswered and $screen_pre_submitted) {
      $this->set('unanswered', true);
    }
    // Set to vertical to simpify template logic.
    if ($this->get('displaymethod') === 'vertical_other') {
      $this->set('displaymethod', 'vertical');
    }
  }

  /**
   * Option level settings for template rendering
   */
  public function set_option() {
    if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
      $questiondata['option'][$part_id]['selected'] = true;
    } else {
      $questiondata['option'][$part_id]['selected'] = false;
    }
    $questiondata['option'][$part_id]['optiontextdisplay'] = false;
    if ($display_option['option_text'] != '') {
      $questiondata['option'][$part_id]['optiontextdisplay'] = true;
    }
    if ($display_option['o_media'] != '') {
      $questiondata['option'][$part_id]['displayoptionmedia'] = true;
    }
    if ($this->get('displaymethod') === 'vertical') {
      if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
        $questiondata['option'][$part_id]['inact'] = true;
      } else {
        $questiondata['option'][$part_id]['inact'] = false;
      }
    }
    if ($tmp_part_id == $display_option['correct']) $marks = $display_option['marks_correct'];
  }
}