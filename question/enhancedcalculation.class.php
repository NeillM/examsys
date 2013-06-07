<?php

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
 * The caculation question
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once '../classes/mathsutils.class.php';
require_once('../classes/question.class.php');
class EnhancedCalculation extends Question implements questionInterface {

  protected $configObj;
  protected $db;


  public function __construct($configObj) {
    $this->configObj = $configObj;
  }

  /*
   * Mark the users answer
   * 
   *  This Must handle exclusions
   */
  public function caculateUserMark() {

    if (is_null($this->useranswer)) {
      $this->error = 'No User Answer';

      return QUESTION_ERROR;
    }
    if (!is_array($this->useranswer)) {
      $this->useranswer = json_decode($this->useranswer, true);
    }
    if (!is_array($this->settings)) {
      $this->settings = json_decode($this->settings, true);
    }


    $enhancedcalcType = $this->configObj->get('enhancedcalc_type');
    if (!is_null($enhancedcalcType)) {
      require_once '../plugins/enhancedcalc/' . $enhancedcalcType . '.php';
      $name = 'enhancedcalc_' . $enhancedcalcType;
      $enhancedcalcObj = new $name($this->configObj);

    } else {
      require_once '../plugins/enhancedcalc/rserve.php';
      $enhancedcalcObj = new enhancedcalc_rserve($this->configObj);
    }

    $return = $enhancedcalcObj->calculate($this->useranswer, $this->settings);


    if ($return === Q_MARKING_WRONG) {
      $this->qmark = $this->settings['m_incorrect'];
      $this->markinfo = Q_MARKING_WRONG;
    } elseif ($return === Q_MARKING_EXACT or $return === Q_MARKING_FULL_TOL) {
      $this->qmark = $this->settings['m_correct'];
      $this->markinfo = Q_MARKING_EXACT;
    } elseif ($return === Q_MARKING_PART_TOL) {
      $this->qmark = $this->settings['m_partial'];
      $this->markinfo = Q_MARKING_PART_TOL;
    } else {
      $this->qmark = null;
      $this->markinfo = Q_MARKING_UNMARKED;
    }

  }

  static public function processUserAnswer(&$postdata, &$session) {

    $data = $session;
    foreach ($postdata as $key => $value) {
      $data[$key] = $value;
    }

    $return = json_encode($data);

    //$this->useranswer = $return;

    return $return;
  }


  /*
   * caulate how many marks is this question worth form its options 
   *    
   *   This Must handle exclusions
   */
  public function caculateQuestionMark() {

  }

  /*
   * caculate the Random Mark for this question 
   *  This Must handle exclusions
   */
  public function caculateRandomMark() {

  }

  /*
   * Display the question
   *
   * The Paper handles question numbering this function renders the inner part of the question 
   * we need at least 2 renders one for the exam script (start.php) one for formative feedback on (finish.php)
   */
  public function render() {

  }

  public function render_paper($extra = array()) {
    // display question on paper
    $screen_pre_submitted=null;
    if (isset($extra['screen_pre_submitted'])) {
      $screen_pre_submitted = $extra['screen_pre_submitted'];
    }

    //make sure data is arrays not encoded
    if (!is_array($this->useranswer)) {
      $this->useranswer = json_decode($this->useranswer, true);
    }
    if (!is_array($this->settings)) {
      $this->settings = json_decode($this->settings, true);
    }

    //check to see if variables have been previously generated if not generate them
    if (!isset($this->useranswer['vars'])) {
      //need to generate variables
      //TODO handle the link variables
      foreach ($this->settings['vars'] as $key => $value) {
        $min = $value['min'];
        $max = $value['max'];
        $inc = $value['inc'];
        $dec = $value['dec'];
        $this->useranswer['vars'][$key] = MathsUtils::gen_random_no($min, $max, $inc, $dec);
      }
      $_SESSION['qid'][$this->id]['vars'] = $this->useranswer['vars'];
    }
    //

    $varname = array_keys($this->useranswer['vars']);
    $varvalue = array_values($this->useranswer['vars']);


    $leadin = str_ireplace($varname, $varvalue, $this->leadin);

    //deal with the failed variables


    echo $leadin;
    if (in_array('ERROR', $varvalue, true)) {
      echo "<p><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" disabled />" . $this->settings['units'] . "</p>\n";
    } else {
      if (isset($this->useranswer['uans']) and $this->useranswer['uans'] == '') {
        echo "<div><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" class=\"calc-answer\" />" . $this->settings['units'] . "</div>\n";
      } else {
        if ((isset($this->useranswer['uans']) and $this->useranswer['uans'] != '') or $screen_pre_submitted == 0) {
          $ans = $this->useranswer['uans'];


          echo "<div><input type=\"text\" style=\"text-align:right\" id=\"qid[" . $this->id . "][uans]\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"" . $ans . "\" class=\"calc-answer\" />" . $this->settings['units'] . "</div>\n";
        } else {
          echo "<div><input type=\"text\" style=\"text-align:right\" class=\"unans calc-answer\" id=\"qid[" . $this->id . "][uans]\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" />" . $this->settings['units'] . "</div>\n";
          $unanswered = true;
        }
      }
    }

    $marks = $this->settings['m_correct'];


  }

}

?>
