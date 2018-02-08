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
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->set('questiontype', 'mcq');
    $this->set('otherselected', false);
    $this->set('abstainselected', false);
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
    if (is_null($useranswerid) and $screen_pre_submitted) {
      $this->set('unanswered', true);
    }
    // Set to vertical to simpify template logic.
    if ($this->get('displaymethod') === 'vertical_other') {
      $this->set('displaymethod', 'vertical');
    }
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
    if ($option['tmppartid'] === $useranswerid) {
      $option['selected'] = true;
    } else {
      $option['selected'] = false;
    }
    $option['optiontextdisplay'] = false;
    if ($option['optiontext'] != '') {
      $option['optiontextdisplay'] = true;
    }
    $option['displayoptionmedia'] = false;
    if ($option['omedia'] != '') {
      $option['displayoptionmedia'] = true;
    }
    if ($this->get('displaymethod') === 'vertical') {
      if (substr($user_dismissid, $option['tmppartid']-1, 1) == '1') {
        $option['inact'] = true;
      } else {
        $option['inact'] = false;
      }
    }
    $this->set_opt($part_id, $option);
    if ($option['tmppartid'] == $option['correct']) {
      $this->set('marks', $option['markscorrect']);
    }
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_additional_option($part_id, $useranswerid, $user_dismissid) {
    $option = $this->get_opt($part_id);
    if ($option['marksincorrect'] < 0) {
      $this->set('negativemarking', true);
      if ($useranswerid === 'a') {
        $this->set('abstainselected', true);
      }
    }
    if ($this->get('displaymethod') === 'vertical') {
      if($this->get('papertype') === 3) {
        $this->set('displaymethod', 'other');
        if (substr($useranswerid,0,5) === 'other') {
          $this->set('otherselected', true);
          $this->set('other', substr($useranswerid,6));
        }
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