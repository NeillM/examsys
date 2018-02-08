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
 * Class for labelling rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class labellingrender extends questionrender {

  /**
   * User response
   * @var string
   */
  protected $useranswer;

  /**
   * Temp correct answer
   * @var string
   */
  protected $tmpcorrect;

  /**
   * Marks correct
   * @var float
   */
  protected $markscorrect;

  /**
   * marks incorrect
   * @var flost
   */
  protected $marksincorrect;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->set('questiontype', 'labelling');
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->set('displaydefault', true);
    if ($this->get('notes') != '') {
      $this->set('displaynotes', true);
    }
    if ($this->get('scenario') != '') {
      $this->set('displayscenario', true);
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
    $marks = $this->get('marks');
    $tmp_labels = 0;
    $max_col1 = 0;
    $max_col2 = 0;
    $tmp_first_split = explode(';', $option['correct']);
    $tmp_second_split = explode('|', $tmp_first_split[11]);
    $label_width = $tmp_first_split[5];
    $label_height = $tmp_first_split[6];
    $hyphen = false;
    foreach ($tmp_second_split as $ind_label) {
      $label_parts = explode('$', $ind_label);
      if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
        if (mb_strstr($label_parts[4], '-') !== false) $hyphen = true;
        $tmp_labels++;
        if ($label_parts[2] > 219) $marks += $option['markscorrect'];
        if ($label_parts[0] < 10) {
          $max_col1 = $label_parts[0];
        } else {
          $max_col2 = $label_parts[0];
        }
      }
    }
    $max_col2-=10;
    $max_label = max($max_col1, $max_col2);

    if ($this->get('scoremethod') == 'Mark per Question') {
      $marks = $option['markscorrect'];
    }
    if (($label_width < 80 and $hyphen) or ($label_width < 104 and !$hyphen)) {    // Two columns
      $computed_height = round(($label_height + 6) * ceil($tmp_labels / 2)) + 10;
      $tmp_height = max($this->get('qmediaheight'), $computed_height);
    } else {                    // Single column
      $computed_height = round(($label_height + 6) * $tmp_labels) + 10;
      $tmp_height = max($this->get('qmediaheight'), $computed_height);
    }

    if ($useranswerid == '0$' . $marks . ';' and  $screen_pre_submitted == 1) {
      $this->set('unanswered', true);
    }
    $tmp_correct = trim($option['correct']);
    $tmp_correct = str_replace("'", "&#039;", $tmp_correct);

    $qmediawidth = $this->get('mediawidth') + 220;
    $this->set('mediawidth', $qmediawidth);
    $this->set('mediaheight', $tmp_height);
    $this->set('tmpcorrect', $tmp_correct);
    $this->set('marks', $marks);

    if (!is_null($useranswerid)) {
      $this->set('useranswer', trim($useranswerid));
      $this->set('markscorrect', $option['markscorrect']);
      $this->set('marksincorrect', $option['marksincorrect']);
      $this->set('unanswered', false);
    } else {
      $this->set('unanswered', true);
    }
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   */
  public function set_additional_option($part_id, $useranswerid, $user_dismissid) {
    // Nothing to do.
  }
}