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

    $returnstatus = null;
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

    if ($this->useranswer['uans'] == '') {
      $this->qmark = 0;

      return Q_MARKING_NOTANS;
    }

    if ((isset($this->settings['show_units']) and $this->settings['show_units'] !== true) or (!isset($this->settings['show_units']))) {
      $pattern = '/-?(?:0|[1-9]\d*)(?:\.\d*)?(?:[eE][+\-]?\d+)?/';
      $out = preg_match($pattern, $this->useranswer['uans'], $matches);
      $sz = strlen($matches[0]);
      $units = trim(substr($this->useranswer['uans'], $sz));

      $this->useranswer['uansunit'] = $units;
      $this->useranswer['uansnumb'] = $matches[0];
    } else {
      $this->useranswer['uansnumb'] = $this->useranswer['uans'];
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

    // create array of units and functions
    if ((isset($this->settings['answersexp']) and !is_array($this->settings['answersexp'])) or (!isset($this->settings['answersexp']))) {
      foreach ($this->settings['answers'] as $key => $value) {
        $units = explode(',', $value['units']);
        foreach ($units as $value1) {
          $value1 = trim($value1);
          $this->settings['answersexp'][$value1] = $value['formula'];
        }
      }
    }

    $unitlist = array_keys($this->settings['answersexp']);
    $this->useranswer['ans']['guessedunits'] = $this->useranswer['uansunit'];
    if ($this->settings['show_units'] === true) {
      if (count($unitlist) > 1) {
        $this->useranswer['status']['units'] = true;
        $this->useranswer['ans']['units'] = $this->useranswer['uansunit'];
        $tmnp = $this->useranswer['uansunit'];
        $this->settings['formula'] = $this->settings['answersexp'][$tmnp];
      } else {
        $this->useranswer['status']['units'] = true;
        $this->useranswer['ans']['units'] = $unitlist[0];
        $this->settings['formula'] = $this->settings['answersexp'][$unitlist[0]];
      }
    } else {
      if (count($unitlist) > 1) {
        //
        $this->useranswer['status']['units'] = false;
        $this->settings['formula'] = $this->settings['answersexp'][$unitlist[0]];
        $this->useranswer['ans']['guessedunits'] = $this->useranswer['uansunit'];
        foreach ($unitlist as $unite => $form) {
          if (strcmp($this->useranswer['uansunit'], $unite) === true) {
            $this->settings['formula'] = $form;
            $this->useranswer['status']['units'] = true;
            $this->useranswer['ans']['units'] = $unite;
            break;
          }
        }
      } else {
        // only 1 unit
        $this->settings['formula'] = $this->settings['answersexp'][$unitlist[0]];
        $this->useranswer['ans']['units'] = $unitlist[0];
        $this->useranswer['ans']['guessedunits'] = $this->useranswer['uansunit'];
        if (strcmp($this->useranswer['uansunit'], $unitlist[0]) === true) {
          //units match
          $this->useranswer['status']['units'] = true;
        } else {
          //units dont match
          $this->useranswer['status']['units'] = false;
        }
      }

    }


    // run calculate through the external interface if errors catch exception and indicate its still unmarked.
    try {
      $returnarray = $enhancedcalcObj->calculate($this->useranswer, $this->settings);
      $return = $returnarray[0];
      $this->useranswer = $returnarray[1];
    } catch (Exception $e) {
      $return = false;
    }

    var_dump($this->settings);
    var_dump($this->useranswer);

    if ($return !== true) {
      // not marked
      $returnstatus = Q_MARKING_UNMARKED;
      $this->useranswer['status']['overall'] = $returnstatus;

      return $returnstatus;
    }


    if (!isset($this->settings['markruleset']) or (isset($this->settings['markruleset']) and $this->settings['markruleset'] = 0)) {
      //default rules for marking


      //if invalidate set for unit mark then if units not right question is wrong and dont bother with anything else
      if($this->settings['marks_unit'] == 'invalidate' and  $this->useranswer['status']['units'] === false ) {
        $this->qmark = $this->settings['marks_incorrect'];

        $returnstatus = Q_MARKING_WRONG;
        $this->useranswer['status']['overall'] = $returnstatus;

        return $returnstatus;
      }

      //check for strict first

      //check strict dp
      if ((isset($this->settings['strictdisplay']) and $this->settings['strictdisplay'] === true and isset($this->settings['dp']) and !($this->settings['strictzeros'] === true  and $this->useranswer['status']['strictdp'] === true and $this->useranswer['status']['strictdpsize'] === true))) {
        $this->qmark = $this->settings['marks_incorrect'];

        $returnstatus = Q_MARKING_WRONG;
        $this->useranswer['status']['overall'] = $returnstatus;

        return $returnstatus;
      }
      //check for strict sf
      if ((isset($this->settings['strictdisplay']) and $this->settings['strictdisplay'] === true and isset($this->settings['sf']) and isset($this->useranswer['status']['strictsf']) and $this->useranswer['status']['strictsf'] !== true)) {
        $this->qmark = $this->settings['marks_incorrect'];


        $returnstatus = Q_MARKING_WRONG;
        $this->useranswer['status']['overall'] = $returnstatus;

        return $returnstatus;
      }

      //check strict units
/*      if (isset($this->settings['strictunits']) and $this->settings['strictunits'] === true and $this->useranswer['status']['units'] !== true) {
        $this->qmark = $this->settings['marks_incorrect'];

        $returnstatus = Q_MARKING_WRONG;
        $this->useranswer['status']['overall'] = $returnstatus;

        return $returnstatus;
      }*/

      $returnstatus = Q_MARKING_WRONG;

      //part tolerance range
      if (isset($this->useranswer['status']['tolerance_partial']) and $this->useranswer['status']['tolerance_partial'] === true) {
        $this->qmark = $this->settings['marks_partial'];
        $returnstatus = Q_MARKING_PART_TOL;
      }

      //full tolerance range
      if (isset($this->useranswer['status']['tolerance_full']) and $this->useranswer['status']['tolerance_full'] === true) {
        $this->qmark = $this->settings['marks_correct'];
        $returnstatus = Q_MARKING_FULL_TOL;
      }

      //exact answer
      if (isset($this->useranswer['status']['exact']) and $this->useranswer['status']['exact'] === true) {
        $this->qmark = $this->settings['marks_correct'];
        $returnstatus = Q_MARKING_EXACT;
      }


      //remove marks for incorrect unit
      if ((isset($this->settings['unit_marks']) and !($this->settings['unit_marks'] == 0 or $this->settings['unit_marks']=='invalidate')) and $this->useranswer['status']['units'] !== true) {
        $this->qmark = $this->qmark - $this->settings['unit_marks'];
        $returnstatus = Q_MARKING_PART_TOL;
      }


      $this->useranswer['status']['overall'] = $returnstatus;

      return $returnstatus;
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

    if(!isset($this->settings['units'])) {
      $this->settings['units']='ERROR UNSET';
    }
    if ($this->useranswer['uans'] == '') {
      echo "<td>" . display_response($extra['tmp_display_students_response'], 'blank') . "<input type=\"text\" style=\"color:#808080; text-align:right\" name=\"q'" . $extra['question'] . "'\" size=\"10\" value=\"" . $string['unanswered'] . "\" />" . $this->settings['units'];

    } else {
      echo '<td>';
      if ($extra['tmp_exclude'] == '1') echo '<span class="exclude">';


      if (isset($this->useranswer['status']['overall']) and ($this->useranswer['status']['overall'] == Q_MARKING_EXACT or $this->useranswer['status']['overall'] == Q_MARKING_FULL_TOL)) {
        echo display_response($extra['tmp_display_students_response'], 'tick');
      } elseif (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_PART_TOL) {
        echo display_response($extra['tmp_display_students_response'], 'half');
      } elseif (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_WRONG) {
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
        echo ' <strong>(' . $this->useranswer['cans'] . ' ';
        if ($this->settings['units'] != '') echo ' ' . $this->settings['units'];
        echo ')</strong>';
      }
    } else {
      echo ' ';
    }

    if (isset($this->useranswer['cans'])) {
      if (isset($this->useranswer['status']['overall']) and ($this->useranswer['status']['overall'] == Q_MARKING_FULL_TOL)) {
        echo ' ' . $string['withatoleranceof'] . ' ' . $this->settings['tolerance_full'] . $this->settings['tolerance_fulltyp'];
        if ($this->settings['tolerance_fulltyp'] == '%') echo " (" . $this->useranswer['ans']['tolerance_fullnegans'] . " - " . $this->useranswer['ans']['tolerance_fullans'] . ")";
      }
      if (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_PART_TOL) {
        echo ' ' . $string['withatoleranceof'] . ' ' . $this->settings['tolerance_partial'] . $this->settings['tolerance_partialtyp'];
        if ($this->settings['tolerance_partialtyp'] == '%') echo " (" . $this->useranswer['ans']['tolerance_partialnegans'] . " - " . $this->useranswer['ans']['tolerance_partialans'] . ")";
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

    // create array of units and functions
    if ((isset($this->settings['answersexp']) and !is_array($this->settings['answersexp'])) or (!isset($this->settings['answersexp']))) {
      foreach ($this->settings['answers'] as $key => $value) {
        $units = explode(',', $value['units']);
        foreach ($units as $value1) {
          $value1 = trim($value1);
          $this->settings['answersexp'][$value1] = $value['formula'];
        }
      }
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

    $this->settings['show_units'] = true;

    $dispunits = '';
    if ($this->settings['show_units'] === true) {
      if (count($this->settings['answersexp']) > 1) {
        //make drop down of units
        $dispunits = "&nbsp;&nbsp;<select name='qid[" . $this->id . "][uansunit]'>";
        foreach ($this->settings['answersexp'] as $key => $value) {
          $dispunits = $dispunits . "<option value='$key'>$key</option>";
        }
        $dispunits = $dispunits . '</select>';
      } else {
        $dispunits = array_keys($this->settings['answersexp']);
        $dispunits = "&nbsp;&nbsp;" . $dispunits[0];
      }
    }



    //deal with the failed variables

    if ($this->scenario != '') echo "<p>" . $this->scenario . "</p>\n";
    if ($this->q_media != '') echo "<p align=\"center\">" . display_media($this->q_media, $this->q_media_width, $this->q_media_height, '') . "</p>\n";

    echo $leadin;
    if (in_array('ERROR', $varvalue, true)) {
      echo "<p><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" disabled />" . $dispunits . "</p>\n";
    } else {
      if (isset($this->useranswer['uans']) and $this->useranswer['uans'] == '') {
        echo "<div><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" class=\"calc-answer\" />" . $dispunits . "</div>\n";
      } else {
        if ((isset($this->useranswer['uans']) and $this->useranswer['uans'] != '')) { //or $screen_pre_submitted == 0
          $ans = $this->useranswer['uans'];


          echo "<div><input type=\"text\" style=\"text-align:right\" id=\"qid[" . $this->id . "][uans]\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"" . $ans . "\" class=\"calc-answer\" />" . $this->settings['units'] . "</div>\n";
        } else {
          echo "<div><input type=\"text\" style=\"text-align:right\" class=\"unans calc-answer\" id=\"qid[" . $this->id . "][uans]\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" />" . $dispunits . "</div>\n";
          $unanswered = true;
        }
      }
    }

    $marks = $this->settings['marks_correct'];


  }

}

?>
