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
 * Class for matrix rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class matrixrender extends questionrender {

  /**
   * Matching scenarios
   * @var array
   */
  protected $scenarios;

  /**
   * Matching user answers
   * @var array
   */
  protected $usersanswers;

  /**
   * Matching options
   * @var array 
   */
  protected $matchoptions;

  /**
   * Matching scenarios
   * @var integer 
   */
  protected $matchscenarios;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->set('questiontype', 'matrix');
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->set('displaydefault', true);
    $this->set('displaymedia', true);
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
    $this->set('scenarios', explode('|', $this->get('scenario')));
    if (!is_null($useranswerid)) {
      $this->set('usersanswers', explode('|', $useranswerid));
    } else {
      $this->set('usersanswers', array());
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
    $matching_options = $this->get('matchoptions');
    $matching_options[] = $option['optiontext'];
    $this->set('matchoptions', $matching_options);
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_additional_option($part_id, $useranswerid, $user_dismissid) {
    $matchoption = array();
    $matchscenario = array();
    $matching_options = $this->get('matchoptions');
    foreach ($matching_options as $single_option) {
      $matchoption[]['option'] = $single_option;
    }
    $matching_users_answers = $this->get('usersanswers');
    $option_order = explode(',', $this->get('optionorder'));
    $matching_scenarios = $this->get('scenarios');
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
          $matchscenario[$part_id-1]['unanswered'] = true;
          $this->set('unanswered', true);
        } else {
          $matchscenario[$part_id-1]['unanswered'] = false;
        }
        $matchscenario[$part_id-1]['id'] = chr(64 + $part_id);
        $matchscenario[$part_id-1]['value'] = $single_scenario;
        for ($i = 0; $i < count($matchoption); $i++) {
          $tmp_part_id = $option_order[$i] + 1;
          $matchoption[$i]['value'] = $tmp_part_id;
          if (isset($matching_users_answers[$part_id-1]) and $matching_users_answers[$part_id-1] == $tmp_part_id) {
            $matchoption[$part_id-1]['selected'] = true;
          } else {
            $matchoption[$part_id-1]['selected'] = false;
          }
        }
        $part_id++;
      }
    }
    $this->set('matchoptions', $matchoption);
    $this->set('matchscenarios', $matchscenario);
  }
}