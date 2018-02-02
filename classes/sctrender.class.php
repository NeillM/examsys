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
 * Class for SCT rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class sctrender extends questionrender {

  /**
   * SCT title
   * @var string
   */
  protected $scttitle;

  /**
   * SCT hypothesis
   * @var string
   */
  protected $scthyp;

  /**
   * SCT New information
   * @var string
   */
  protected $sctinfo;

  /**
   * SCT title
   * @var string
   */
  protected $scttitlelower;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->set('questiontype', 'sct');
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    // Nothing to do.
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid id or name of user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $allowed_responses = 1) {
    global $string;
    // SCT stalls vignette in scenario so must display. 
    $this->set('displayscenario', true);
    if ($this->get('notes') != '') {
      $this->set('displaynotes', true);
    }
    if ($this->get('qmedia') != '') {
      $this->set('displaymedia', true);
    }
    $sct_parts = explode('~',$this->get('leadin'));
    $sct_titles = array(1=>$string['hypothesis'], 2=>$string['investigation'], 3=>$string['prescription'], 4=>$string['intervention'], 5=>$string['treatment']);
    $this->set('scttitle', $sct_titles[$this->get('displaymethod')]);
    $this->set('scthyp', $sct_parts[0]);
    $this->set('sctinfo', $sct_parts[1]);
    $this->set('scttitlelower', $string['thenthis'] . " " . mb_strtolower($sct_titles[$this->get('displaymethod')], 'UTF-8') . " " . $string['is'] . ":");

    if ($useranswerid == '0' and $screen_pre_submitted == 1) {
      $this->set('unanswered', true);
    } else {
      $this->set('unanswered', false);
    }
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param integer $marks reference to marks available for question
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option($part_id, $useranswerid, $user_dismissid, &$marks, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if ($option['tmppartid'] == $useranswerid) {
      $option['selected'] = true;
    } else {
      $option['selected'] = false;
    }
    if (substr($user_dismissid, $option['tmppartid']-1, 1) == '1') {
      $option['inact'] = true;
    } else {
      $option['inact'] = false;
    }
    $option['optiontextdisplay'] = false;
    if ($option['optiontext'] != '') {
      $option['optiontextdisplay'] = true;
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
    if ($option['marksincorrect'] < 0) {
      $this->set('negativemarking', true);
    }
  }
}