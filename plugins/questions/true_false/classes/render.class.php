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

namespace plugins\questions\true_false;

/**
 *
 * Class for TF rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class render extends \questionrender {

  /**
   * True selection state
   * @var boolean
   */
  public $trueselected;

  /**
   * False selection state
   * @var boolean
   */
  public $falseselected;

  /**
   * Abstain selection state
   * @var boolean
   */
  public $abstainselected;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'true_false';
    $this->abstainselected = false;
    $this->falseselected = false;
    $this->trueselected = false;
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->displaydefault = true;
    if ($this->get('notes') != '') {
      $this->displaynotes = true;
    }
    if ($this->get('scenario') != '') {
      $this->displayscenario = true;
    }
    $this->displayleadin = true;
    if ($this->get('qmedia') != '') {
      $this->displaymedia = true;
    }
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid id or name of user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $allowed_responses = 1) {
    // Noting to do.
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
    if ($useranswerid == 'u' and $screen_pre_submitted == 1) {
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
    if ($this->get('displaymethod') == 'dropdown') {
      if ($useranswerid == 't') {
        $this->trueselected = true;
      }
      if ($useranswerid == 'f') {
        $this->falseselected = true;
      }
    } else {
      if ($useranswerid == 't') {
        $this->trueselected = true;
      }
      if ($useranswerid == 'f') {
        $this->falseselected = false;
      }
      if ($this->get('negativemarking')) {
        if ($useranswerid == 'a') {
          $this->abstainselected = true;
        }
      }
    }
    $this->marks = $option['markscorrect'];
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_additional_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    // Nothing to do.
  }
}