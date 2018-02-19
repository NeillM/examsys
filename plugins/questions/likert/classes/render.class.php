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

namespace plugins\questions\likert;

/**
 *
 * Class for Likert rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class render extends \questionrender {

  /**
   * Na option state
   * @var integer 
   */
  public $displayna;

  /**
   * Note column span length
   * @var integer 
   */
  public $likertnotescolspan;

  /**
   * Scenario column span length
   * @var integer 
   */
  public $likertscenariocolspan;

  /**
   * List of scale labels.
   * @var array
   */
  public $scale;

  /**
   * List of scale options.
   * @var array
   */
  public $scaleopt;

  /**
   * Id of scale
   * @var integer
   */
  public $id;

  /**
   * Na state
   * @var boolean
   */
  public $na;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'likert';
    $this->displayna = false;
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    if ($this->get('qmedia') != '') {
      $this->displaymedia = true;
    }
    $this->displaydefault = true;
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid id or name of user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $allowed_responses = 1) {
    $na = false;
    $likert_display = explode('|',$this->get('displaymethod'));
    $likert_col_no = substr_count($this->get('displaymethod'),'|');
    if ($likert_display[$likert_col_no] == 'true') {
      $likert_col_no++;
      $na = true;
    }
    if ($this->get('notes') != '') {
      $this->displaynotes = true;
      $this->likertnotescolspan = $likert_col_no + 1;
    }
    if ($this->get('scenario') != '') {
      $this->displayscenario = true;
      $this->likertscenariocolspan = $likert_col_no + 2;
    }
    if ($na == true) {
      $this->displayna = true;
    } else {
      $this->displayna = false;
    }
    $disp[0] = $likert_display[0];
    $temp_end = substr_count($this->get('displaymethod'),'|') - 1;
    for ($i=1; $i<=$temp_end; $i++) {
      $disp[$i] = $likert_display[$i];
    }
    $this->scale = $disp;
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    if ($useranswerid == 'u' and $screen_pre_submitted == 1) {
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
    $scale_size = substr_count($this->get('displaymethod'),'|');
    $this->id = $this->get('questionno') . "_" . $part_id;
    $likert_display = explode('|',$this->get('displaymethod'));
    if ($likert_display[$scale_size] == 'true') {
      $this->na = false;
      if ($useranswerid == 'n/a') {
        $this->na = true;
      }
    } else {
      $this->na = false;
    }
    $scale = array();
    for ($i=1; $i<=$scale_size; $i++) {
      if ($i == $useranswerid) {
        $scale[$i] = true;
      } else {
        $scale[$i] = false;
      }
    }
    $this->scaleopt = $scale;
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