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

  public $alluseranswers;

  public function __construct($configObj) {
    $this->configObj = $configObj;
  }

  //splits number off front of numb/unit or just number
  function splitnumbunit($input) {
    $pattern = '/-?(?:0|[1-9]\d*)(?:\.\d*)?(?:[eE][+\-]?\d+)?/';
    $out = preg_match($pattern, $input, $matches);
    if(is_array($matches) and isset($matches[0])) {
      $sz = strlen($matches[0]);
      $units = trim(substr($input, $sz));
      $numb = $matches[0];
      return array($numb, $units);
    } else {
      return array($input, $this->useranswer['uansunit']);
    }
  }

  //arange the possible fromula by units
  function build_formula_by_units() {
    $formula_by_units = array();
    foreach ($this->settings['answers'] as $key => $value) {
      $units = explode(',', $value['units']);
      foreach ($units as $value1) {
        $value1 = trim($value1);
        $formula_by_units[$value1] = $value['formula'];
      }
    }
    return $formula_by_units;
  }

  function are_units_correct($unit) {
    // create array of units and functions
    $this->settings['answersexp'] = $this->build_formula_by_units(); 
    if(isset($this->settings['answersexp'][$unit])) {
      return true;
    } else {
      return false;
    }
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
    
    $return = $this->splitnumbunit($this->useranswer['uans']);
    $this->useranswer['uansunit'] = $return[1];
    $this->useranswer['uansnumb'] = $return[0];
  
    if(isset($this->useranswer['uansunit'])) {
       $this->useranswer['ans']['guessedunits'] = $this->useranswer['uansunit'];
    } 
    
    //are the units correct?
    $this->useranswer['status']['units'] = $this->are_units_correct($this->useranswer['uansunit']);

    if($this->useranswer['status']['units'] === false) {
      //we cant mach the units so this question must be wrong! however we need to have a formula and a unit to caculate the feedback
      // so just use the fitst one!
      foreach($this->settings['answersexp'] as $unit => $formula) {
        $this->useranswer['ans']['formula_used'] = $formula;
        $this->useranswer['ans']['units_used'] = $unit;
        break;
      }
    } else {
      // setup the fomula and units for the caculation 
      $this->useranswer['ans']['formula_used'] = $this->settings['answersexp'][$this->useranswer['uansunit']];
      $this->useranswer['ans']['units_used'] = $this->useranswer['uansunit'];
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
    
    // run calculate through the external interface if errors catch exception and indicate its still unmarked.
    try {
      
      /*
       * 
       *  CACULATE REQURED NUMERIC VALUES
       * 
       */
      $this->useranswer['cans'] = $enhancedcalcObj->caculate_correct_ans($this->useranswer['vars'], $this->useranswer['ans']['formula_used']);     
      
      if (isset($this->settings['tolerance_full'])) {
        switch ($this->settings['fulltoltyp']) {
          case "%":
            $res = $enhancedcalcObj->caculate_tolerance_percent($this->useranswer['cans'], $this->settings['tolerance_full']);
          break;
          case "#":
            $res = $enhancedcalcObj->caculate_tolerance_absolute($this->useranswer['cans'], $this->settings['tolerance_full']);
          break;      
          case "sf":
            $res = $enhancedcalcObj->caculate_tolerance_sf($this->useranswer['cans'], $this->settings['tolerance_full']);
           break;
        }
        $this->useranswer['ans']['tolerance_full'] = $res['tolerance'];
        $this->useranswer['ans']['tolerance_fullans'] = $res['tolerance_ans'];
        $this->useranswer['ans']['tolerance_fullansneg'] = $res['tolerance_ansneg'];
      }
      
      if (isset($this->settings['tolerance_partial'])) {
        switch ($this->settings['parttoltyp']) {
          case "%":
            $res = $enhancedcalcObj->caculate_tolerance_percent($this->useranswer['cans'], $this->settings['tolerance_partial']);
          break;
          case "#":
            $res = $enhancedcalcObj->caculate_tolerance_absolute($this->useranswer['cans'], $this->settings['tolerance_partial']);
          break;      
          case "sf":
            $res = $enhancedcalcObj->caculate_tolerance_sf($this->useranswer['cans'], $this->settings['tolerance_partial']);
           break;
        }
        $this->useranswer['ans']['tolerance_partial'] = $res['tolerance'];
        $this->useranswer['ans']['tolerance_partialans'] = $res['tolerance_ans'];
        $this->useranswer['ans']['tolerance_partialansneg'] = $res['tolerance_ansneg'];
      }
      
      /*
       * 
       * FORMAT CACULATED ANS
       * 
       */
      if($this->settings['strictdisplay'] == 'on') {
        
        if(isset($this->settings['dp'])) {
          $function = 'format_number_dp';
          $arg = $this->settings['dp'];
          if($this->settings['strictzeros'] == 'on') {
            $function = 'format_number_dp_strict_zeros';
          }
        }
        if(isset($this->settings['sf'])) {
          $function = 'format_number_sf';
          $arg = $this->settings['sf'];
        }
        
        $this->useranswer['cans'] = $enhancedcalcObj->$function($this->useranswer['cans'], $arg);
                
        $this->useranswer['ans']['tolerance_full'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_full'], $arg);
        $this->useranswer['ans']['tolerance_fullans'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_fullans'], $arg);
        $this->useranswer['ans']['tolerance_fullansneg'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_fullansneg'], $arg);
   
        $this->useranswer['ans']['tolerance_partial'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_partial'], $arg);
        $this->useranswer['ans']['tolerance_partialans'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_partialans'], $arg);
        $this->useranswer['ans']['tolerance_partialansneg'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_partialansneg'], $arg);
      }
      
      /*
       * 
       *  
       * MARKING
       * 
       */
      if($this->useranswer['status']['units'] === false) {
        //we can't mach the units so this question must be wrong!
        $this->qmark = $this->settings['marks_incorrect'];
        $this->useranswer['status']['exact'] = false;
        $returnstatus = Q_MARKING_WRONG;
        $this->useranswer['status']['overall'] = $returnstatus;
        return $returnstatus;
      }
      
      $this->useranswer['status']['exact'] = $enhancedcalcObj->is_useranswer_correct($this->useranswer['uansnumb'], $this->useranswer['cans']);
      
      if($this->useranswer['status']['exact'] === false) {
        $this->useranswer['status']['tolerance_full']     = $enhancedcalcObj->is_useranswer_within_tolerance(
                                                                                                              $this->useranswer['uansnumb'], 
                                                                                                              $this->useranswer['ans']['tolerance_fullansneg'], 
                                                                                                              $this->useranswer['ans']['tolerance_fullans']
                                                                                                            );
        
        if($this->useranswer['status']['tolerance_full'] === false) {
          $this->useranswer['status']['tolerance_partial']  = $enhancedcalcObj->is_useranswer_within_tolerance(
                                                                                                                $this->useranswer['uansnumb'], 
                                                                                                                $this->useranswer['ans']['tolerance_partialansneg'], 
                                                                                                                $this->useranswer['ans']['tolerance_partialans']
                                                                                                              );
        }
      }
      //strict dp marking
      if ( $this->is_strict_dp_enabled() ) {
        
        if( $this->is_strict_dp_strictzeros_enabled() ) {
          $this->useranswer['status']['strictdp'] = $enhancedcalcObj->is_useranswer_correct_decimal_places_strictzeros($this->useranswer['uansnumb'], $this->settings['dp']);
        } else {
          $this->useranswer['status']['strictdp'] = $enhancedcalcObj->is_useranswer_correct_decimal_places($this->useranswer['uansnumb'], $this->settings['dp']);
        }
        if($this->useranswer['status']['strictdp'] === false) {
          $this->qmark = $this->settings['marks_incorrect'];
          $returnstatus = Q_MARKING_WRONG;
          $this->useranswer['status']['overall'] = $returnstatus;
          return $returnstatus;
        }
      }
      
      //check for strict sf
      if ((isset($this->settings['strictdisplay']) and $this->settings['strictdisplay'] === true) and isset($this->settings['sf']) ) {
        
        $this->useranswer['status']['strictsf'] = $enhancedcalcObj->is_useranswer_within_tolerance_significant_figures($this->useranswer['uansnumb'], $this->settings['sf']);
        if($this->useranswer['status']['strictsf'] === false) {
          $this->qmark = $this->settings['marks_incorrect'];
          $returnstatus = Q_MARKING_WRONG;
          $this->useranswer['status']['overall'] = $returnstatus;
          return $returnstatus;
        }
      }
 
      
      //assume its wrong wrong !!
      $returnstatus = Q_MARKING_WRONG;
      $this->qmark = $this->settings['marks_incorrect'];
      
      //part tolerance range
      if ($this->is_user_ans_within_partial_tolerance()) {
        $this->qmark = $this->settings['marks_partial'];
        $returnstatus = Q_MARKING_PART_TOL;
      }

      //full tolerance range
      if ($this->is_user_ans_within_fullmark_tolerance()) {
        $this->qmark = $this->settings['marks_correct'];
        $returnstatus = Q_MARKING_FULL_TOL;
      }

      //exact answer
      if ($this->is_user_ans_correct()) {
        $this->qmark = $this->settings['marks_correct'];
        $returnstatus = Q_MARKING_EXACT;
      }
      
      //remove marks for incorrect unit
      if ((isset($this->settings['unit_marks']) and !($this->settings['unit_marks'] == 0 or $this->settings['unit_marks'] == 'invalidate')) and $this->useranswer['status']['units'] !== true) {
        $this->qmark = $this->qmark - $this->settings['unit_marks'];
        $returnstatus = Q_MARKING_PART_UNITS_WRONG;
      }

      $this->useranswer['status']['overall'] = $returnstatus;
       
    } catch (Exception $e) {
      $returnstatus = Q_MARKING_ERROR;
      $this->useranswer['status']['error'] = true;
      $this->useranswer['ans']['error'] = $enhancedcalcObj->error_msg;
      $this->useranswer['status']['overall'] = $returnstatus;
      return $returnstatus;
    }
       
    return $returnstatus;
    
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
    return $this->settings['marks_correct'];
  }

  /*
   * caculate the Random Mark for this question 
   *  This Must handle exclusions
   */
  public function caculateRandomMark() {
    return 0;
  }

  //returns true if the question has been excluded
  function is_excluded() {
    return (isset($this->excluded{0}) and $this->excluded{0} == 1);
  }
  
  function is_user_ans_correct() {
    return (isset($this->useranswer['status']['exact']) and $this->useranswer['status']['exact'] === true);
  }
  
  function is_user_ans_within_fullmark_tolerance() {
    return (isset($this->useranswer['status']['tolerance_full']) and $this->useranswer['status']['tolerance_full'] === true);
  }
  
  function is_user_ans_within_partial_tolerance() {
    return (isset($this->useranswer['status']['tolerance_partial']) and $this->useranswer['status']['tolerance_partial'] === true);
  }
  
  function is_user_ans_units_correct() {
    return $this->useranswer['status']['units'];
  }
  
  function is_strict_dp_enabled() {
    return (isset($this->settings['strictdisplay']) and $this->settings['strictdisplay'] === 'on' and isset($this->settings['dp']));
  }
  
  function is_strict_dp_strictzeros_enabled() {
    return (isset($this->settings['strictzeros']) and $this->settings['strictzeros'] == 'on');
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
    global $string, $tmp_fback;
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
    /*
        if (!isset($this->settings['units'])) {
          $this->settings['units'] = $this->useranswer['ans']['units'];
        }
        */
    if ($this->useranswer['uans'] == '') {
      echo "<td>" . display_response($extra['tmp_display_students_response'], 'blank') . "<input type=\"text\" style=\"color:#808080; text-align:right\" name=\"q'" . $extra['question'] . "'\" size=\"10\" value=\"" . $string['unanswered'] . "\" />";

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
      if ((isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_UNMARKED) or !isset($this->useranswer['status']['overall'])) {
        echo '<input type="text" style="text-align:right" name="q' . $extra['question'] . '" size="10" value="UNMARKED">';
      } else {
        echo '<input type="text" style="text-align:right" name="q' . $extra['question'] . '" size="10" value="' . $this->useranswer['uansnumb'] . ' ' . $this->useranswer['uansunit'] . '" />'; //. $this->settings['units'];
      }
    }
    if ($extra['tmp_display_correct_answer'] == '1') {
      if (!isset($this->useranswer['status'])) {
        echo ' <strong>(<span style="color:#C00000">NOT MARKED YET!</span>)</strong>';
      } elseif (!isset($this->useranswer['cans'])) {
        echo ' <strong>(<span style="color:#C00000">error!</span>)</strong>';
      } else {
        echo ' <strong>(' . $this->useranswer['cans'] . ' ';
        if ($this->useranswer['ans']['units_used'] != '') echo ' ' . $this->useranswer['ans']['units_used'];
        echo ')</strong>';
      }
    } else {
      echo ' ';
    }

    if (isset($this->useranswer['cans'])) {
      if (isset($this->useranswer['status']['overall']) and ($this->useranswer['status']['overall'] == Q_MARKING_FULL_TOL)) {
        echo ' ' . $string['withatoleranceof'] . ' ' . $this->settings['tolerance_full'] . $this->settings['fulltoltyp'];
        if ($this->settings['fulltoltyp'] == '%') echo " (" . $this->useranswer['ans']['tolerance_fullnegans'] . " - " . $this->useranswer['ans']['tolerance_fullans'] . ")";
      }
      if (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_PART_TOL) {
        echo ' ' . $string['withatoleranceof'] . ' ' . $this->settings['tolerance_partial'] . $this->settings['parttoltyp'];
        if ($this->settings['parttoltyp'] == '%') echo " (" . $this->useranswer['ans']['tolerance_partialnegans'] . " - " . $this->useranswer['ans']['tolerance_partialans'] . ")";
      }
    }

    if ($extra['tmp_exclude'] == '1') echo '</span>';
    echo "</td></tr>\n</table>\n";
    if ($tmp_fback != '' and $extra['tmp_display_feedback'] == '1') echo "<div class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $tmp_fback . "</div>\n";
  }


  function load_all_user_answers(&$all_user_answers) {
    $this->alluseranswers = $all_user_answers;
  }

  function variable_substitution($inputVal, $user_answers) {
    if (substr($inputVal, 0, 3) == 'ans') {
      //its a question reference get previous user answer
      $find_qid = intval(substr($inputVal, 3));
      $pre_user_answers = '';
      foreach ($user_answers as $screen => $answers) {
        foreach ($answers as $pre_qid => $ans) {
          if ($pre_qid == $find_qid) {
            try {
              $uansarray = json_decode($ans, true);
            } catch (exception $e) {
              return 'ERROR';
            }
            break 2;
          }
        }
      }
      if (!isset($uansarray['uans'])) return 'ERROR';
      $return = $this->splitnumbunit($uansarray['uans']);
      $inputVal = $return[0];
    } elseif (substr($inputVal, 0, 3) == 'var') {
      //its a var refrance from a previous question
      $find_var = substr($inputVal, 3, 1);
      $find_qid = intval(substr($inputVal, 4));
      $pre_var_val = '';
      foreach ($user_answers as $screen => $answers) {
        foreach ($answers as $pre_qid => $ans) {
          if ($pre_qid == $find_qid) {
            try {
              $variables = json_decode($ans, true);
            } catch (exception $e) {
              return 'ERROR';
            }
            break 2;
          }
        }
      }
      if (!isset($variables['vars']['$' . $find_var])) return 'ERROR';
      $inputVal = $variables['vars']['$' . $find_var]; //str_replace('var' . substr($inputVal, 3, 1) . $find_qid, $pre_var_val, $inputVal);
    }

    //eval("\$inputVal = $inputVal;");

    return $inputVal;
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

    $calculatevars = false;
    //check to see if variables have been previously generated if not generate them
    if (!isset($this->useranswer['vars'])) {
      $calculatevars = true;
    } else {
      foreach ($this->useranswer['vars'] as $value) {
        if ($value == 'ERROR') {
          $calculatevars = true;
        }
      }
    }

    if ($calculatevars === true) {
      //need to generate variables
      //TODO handle the link variables
      foreach ($this->settings['vars'] as $key => $value) {
        $min = $this->variable_substitution($value['min'], $this->alluseranswers);
        $max = $this->variable_substitution($value['max'], $this->alluseranswers);
        $inc = $this->variable_substitution($value['inc'], $this->alluseranswers);
        $dec = $this->variable_substitution($value['dec'], $this->alluseranswers);
        $this->useranswer['vars'][$key] = MathsUtils::gen_random_no($min, $max, $inc, $dec);
      }
      $_SESSION['qid'][$this->id]['vars'] = $this->useranswer['vars'];
    }
    //

    $varname = array_keys($this->useranswer['vars']);
    $varvalue = array_values($this->useranswer['vars']);


    $leadin = str_ireplace($varname, $varvalue, $this->leadin);

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
        echo "<div><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" class=\"ecalc-answer\" />" . $dispunits . "</div>\n";
      } else {
        if ((isset($this->useranswer['uans']) and $this->useranswer['uans'] != '')) { //or $screen_pre_submitted == 0
          $ans = $this->useranswer['uans'];


          echo "<div><input type=\"text\" style=\"text-align:right\" id=\"qid[" . $this->id . "][uans]\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"" . $ans . "\" class=\"ecalc-answer\" />" . $this->settings['units'] . "</div>\n";
        } else {
          echo "<div><input type=\"text\" style=\"text-align:right\" class=\"ecalc-answer\" id=\"qid[" . $this->id . "][uans]\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" />" . $dispunits . "</div>\n";
          $unanswered = true;
        }
      }
    }

    $marks = $this->settings['marks_correct'];
  }

}

?>
