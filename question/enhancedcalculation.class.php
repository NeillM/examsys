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


    if(!isset($this->useranswer['uansunit'])) {
      $pattern='/-?(?:0|[1-9]\d*)(?:\.\d*)?(?:[eE][+\-]?\d+)?/';
      $out=preg_match($pattern,$inp,$matches);
      $sz=strlen($matches[0]);
      $units=trim(substr($inp,$sz));

      $this->useranswer['uansunit'] = $units;
      $this->useranswer['uansnumb'] = $matches[0];
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

    $returnarray = $enhancedcalcObj->calculate($this->useranswer, $this->settings);

    $return = $returnarray[0];
    $this->useranswer = $returnarray[1];

    var_dump($this->useranswer);

    if($return!==true) {
      // not marked
      return Q_MARKING_UNMARKED;
    }

    if(!isset($this->settings['markruleset']) or (isset($this->settings['markruleset']) and $this->settings['markruleset'] = 0)) {
      //default rules for marking
    }


    return Q_MARKING_UNMARKED;
  }

  public function useranswer_to_string() {
    return json_encode($this->useranswer);
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

  public function render_feedback($extra = array()) {
    global $string;
    print "ENHANCED CALC QUESTION FEEDBACK";


    //make sure data is arrays not encoded
    if (!is_array($this->useranswer)) {
      $this->useranswer = json_decode($this->useranswer, true);
    }
    if (!is_array($this->settings)) {
      $this->settings = json_decode($this->settings, true);
    }
    //
    if (isset($this->useranswer['vars'])) {
      $varname = array_keys($this->useranswer['vars']);
      $varvalue = array_values($this->useranswer['vars']);
    } else {
      $varname = array('$A', '$B', '$C', '$D', '$E', '$F', '$G', '$H', '$I', '$J', '$K');
      $varvalue = array('ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR', 'ERROR');
    }

    $leadin = str_ireplace($varname, $varvalue, $this->leadin);

    //deal with the failed variables

    if ($this->scenario != '') echo "<p>" . $this->scenario . "</p>\n";
    if ($this->q_media != '') echo "<p align=\"center\">" . display_media($this->q_media, $this->q_media_width, $this->q_media_height, '') . "</p>\n";

    echo_content($leadin);

    if (!isset($this->useranswer['uans']) or $this->useranswer['uans'] == '') {
      reset_feedback($extra['hide_if_unanswered']);
    }

    $saved_response = $this->useranswer['uans'];
    $part_id = 1;


    $tmp_fback = $this->correct_fback;


    //echo_content($tmp_leadin);


    echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\"><tr>";
    if ($extra['tmp_display_correct_answer'] == '1') {
      echo '<td>';
      if (isset($this->std[0])) echo display_std($this->std[0]);
      echo '</td>';
    } else {
      echo '<td></td>';
    }

    $saved_response_clean = preg_replace('([^0-9\.\-])', '', $saved_response);

    if ($this->useranswer['uans'] == '') {
      echo "<td>" . display_response($extra['tmp_display_students_response'], 'blank') . "<input type=\"text\" style=\"color:#808080; text-align:right\" name=\"q'" . $extra['question'] . "'\" size=\"10\" value=\"" . $string['unanswered'] . "\" />" . $this->settings['units'];

    } else {
      echo '<td>';
      if ($extra['tmp_exclude'] == '1') echo '<span class="exclude">';


      if (isset($this->useranswer['status']) and ($this->useranswer['status'] == Q_MARKING_EXACT or $this->useranswer['status'] == Q_MARKING_FULL_TOL)) {
        echo display_response($extra['tmp_display_students_response'], 'tick');
      } elseif (isset($this->useranswer['status']) and $this->useranswer['status'] == Q_MARKING_PART_TOL) {
        echo display_response($extra['tmp_display_students_response'], 'half');
      } elseif (isset($this->useranswer['status']) and $this->useranswer['status'] == Q_MARKING_WRONG) {
        echo display_response($extra['tmp_display_students_response'], 'cross');
      } else {
        echo display_response($extra['tmp_display_students_response'], 'unmarked');
      }
      echo '<input type="text" style="text-align:right" name="q' . $extra['question'] . '" size="10" value="' . $this->useranswer['uans'] . '" />' . $this->settings['units'];
    }
    if ($extra['tmp_display_correct_answer'] == '1') {
      if (!isset($this->useranswer['status'])) {
        echo ' <strong>(<span style="color:#C00000">NOT MARKED YET!</span>)</strong>';
      } elseif (!isset($this->useranswer['cans'])) {
        echo ' <strong>(<span style="color:#C00000">error!</span>)</strong>';
      } else {
        echo ' <strong>(' . $this->useranswer['cans'] . ')';
        if ($this->settings['units'] != '') echo ' ' . $this->settings['units'];
        echo '</strong>';
      }
    } else {
      echo ' ';
    }

    if (isset($this->useranswer['cans'])) {
      if (isset($this->useranswer['status']) and ($this->useranswer['status'] == Q_MARKING_FULL_TOL)) {
        echo ' ' . $string['withatoleranceof'] . ' ' . $settings['tolerance_full'];
        if (StringUtils::ends_with($settings['tolerance_full'], '%')) echo " (" . (round($tmp_answer[1], $decimals) - $tolerance_full) . " - " . (round($tmp_answer[1], $decimals) + $tolerance_full) . ")";
      }
      if (isset($this->useranswer['status']) and $this->useranswer['status'] == Q_MARKING_PART_TOL) {
        echo ' ' . $string['withatoleranceof'] . ' ' . $settings['tolerance_partial'];
        if (StringUtils::ends_with($settings['tolerance_partial'], '%')) echo " (" . (round($tmp_answer[1], $decimals) - $tolerance_partial) . " - " . (round($tmp_answer[1], $decimals) + $tolerance_partial) . ")";
      }
    }

    if ($extra['tmp_exclude'] == '1') echo '</span>';
    echo "</td></tr>\n</table>\n";
    if ($tmp_fback != '' and $extra['tmp_display_feedback'] == '1') echo "<div class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $tmp_fback . "</div>\n";
  }


  public function render_paper($extra = array()) {

    global $string;
    // display question on paper
    $screen_pre_submitted = null;
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

    if ($this->scenario != '') echo "<p>" . $this->scenario . "</p>\n";
    if ($this->q_media != '') echo "<p align=\"center\">" . display_media($this->q_media, $this->q_media_width, $this->q_media_height, '') . "</p>\n";

    echo $leadin;
    if (in_array('ERROR', $varvalue, true)) {
      echo "<p><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" disabled />" . $this->settings['units'] . "</p>\n";
    } else {
      if (isset($this->useranswer['uans']) and $this->useranswer['uans'] == '') {
        echo "<div><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" class=\"calc-answer\" />" . $this->settings['units'] . "</div>\n";
      } else {
        if ((isset($this->useranswer['uans']) and $this->useranswer['uans'] != '')) { //or $screen_pre_submitted == 0
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
