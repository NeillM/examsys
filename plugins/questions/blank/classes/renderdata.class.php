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

namespace plugins\questions\blank;

/**
 *
 * Class for fill in the blank rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {

  /**
   * Blank options
   * @var array
   */
  public $blankoptions;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'blank';
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->displaydefault = true;
    if ($this->notes != '') {
      $this->displaynotes = true;
    }
    if ($this->scenario != '') {
      $this->displayscenario = true;
    }
    $this->displayleadin = true;
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswerid user answer
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
    $ans = '';
    $blank_mark = array();
    $option['optiontext'] = str_replace('&nbsp;',' ',$option['optiontext']);
    $blank_details = preg_split("/\[blank|\[\/blank\]/", $option['optiontext']);
    if (!is_null($useranswerid)) {
      $useranswerid = str_replace('&nbsp;', ' ', $useranswerid);
      $blank_user_answers = json_decode($useranswerid);
    } else {
      $blank_user_answers = array();
    }

    $count = 0;
    $itemcount = 1;
    $blankoption = array();
    for ($blank_count = 0; $blank_count < count($blank_details); $blank_count++) {
      if ($blank_details[$blank_count] === '') {
        continue;
      } else {
        $count++;
      }
      if (substr($blank_details[$blank_count], 0, 1) === ']') {
        $blankoption[$count]['itemtype'] = 'blank';
        $blankoption[$count]['itemcount'] = $itemcount;
        if ($this->displaymethod === 'textboxes') {
          $sizeresults = array();
          $not_used = preg_match("|size=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$sizeresults);
          if (isset($sizeresults[1]) and $sizeresults[1] != '') {
            $blank_size[$blank_count] = $sizeresults[1];
          } else {
            $blank_size[$blank_count] = 15;
          }
          $blankoption[$count]['size'] = $blank_size[$blank_count];
          if ((isset($blank_user_answers[$itemcount - 1]) and $blank_user_answers[$itemcount - 1] == 'u') and (isset($screen_pre_submitted) and $screen_pre_submitted == 1)) {
            $this->unanswered = true;
            $blankoption[$count]['unans'] = true;
          } else {
            $blankoption[$count]['unans'] = false;
            if (isset($blank_user_answers[$itemcount - 1])) {
              $ans = $blank_user_answers[$itemcount - 1];
            }
            $encoded_ans = htmlentities($ans, ENT_COMPAT | ENT_HTML5, \Config::get_instance()->get('cfg_page_charset'), false);
            $blankoption[$count]['encoded_ans'] = $encoded_ans;
          }
        } else {
          $answer_list = explode(',', ltrim($blank_details[$blank_count], ']'));
          // Ensure that the correct answer is filtered in the same way as the user's answer.
          $answer_list = \param::clean_array($answer_list, \param::TEXT);
          shuffle($answer_list);            // Shuffle the answers up.
          for ($i=0; $i<count($answer_list); $i++) {
            if (isset($answer_list[$i]) and isset($blank_user_answers[$itemcount - 1]) and html_entity_decode(trim($answer_list[$i])) == html_entity_decode(trim($blank_user_answers[$itemcount - 1]))) {
              $blankoption[$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => true);
            } else {
              $blankoption[$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => false);
            }
          }
          if (isset($blank_user_answers[$itemcount- 1]) and $blank_user_answers[$itemcount- 1] == 'u' and $screen_pre_submitted == 1) {
            $blankoption[$count]['unans'] = true;
            $this->unanswered = true;
          } else {
            $blankoption[$count]['unans'] = false;
          }
        }
        $results=array();
        $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
        if (isset($results[1]) and $results[1] != '') {
          $blank_mark[$blank_count] = $results[1];
        } else {
          $blank_mark[$blank_count] = $option['markscorrect'];
        }
        $itemcount++;
      } else {
        $blankoption[$count]['itemtype'] = 'blurb';
        $blankoption[$count]['itemvalue'] = $blank_details[$blank_count];
      }
    }
    $this->blankoptions = $blankoption;
    if ($this->scoremethod == 'Mark per Option') {
      if (count($blank_mark) > 0) {
        $marks = $this->marks;
        foreach ($blank_mark as $individual_mark) {
          $marks += $individual_mark;
        }
      }
    } else {
      $marks = $option['markscorrect'];
    }
    $this->marks = $marks;
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function process_options($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted) {
    // Nothing to do.
  }
}