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
* Paper Render package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2018 onwards The University of Nottingham
*/

/**
 * Paper rendering helper class.
 */
class paperrender {

  public static function display_question($screen_pre_submitted, $q_displayed, $string, &$question, $pid, $calculator, $current_screen, $old_q_type, &$question_no, $user_answers, &$unanswered) {
    global $labelcolor, $old_likert_label, $old_likert_cols, $screen_pre_submitted, $A, $B, $C, $D, $E, $F, $G, $H, $I, $J, $li_set, $bgcolor, $li_set, $used_questions, $user_dismiss, $user_order, $string, $language, $unanswered_color;
 
    $configObject = Config::get_instance();
    $db = $configObject->db;
    $propertyObj = PaperProperties::get_paper_properties_by_id($pid, $db, $string, true);
    $paper_type = $propertyObj->get_paper_type();
    
    
    $render = new render($configObject);

    if ($screen_pre_submitted == 1 and $q_displayed == 0) {
      $questiondata['unanswered'] = true;
    } else {
      $questiondata['unanswered'] = false;
    }

    // Attempt to display paper prolog
    if ($q_displayed == 0 and $current_screen == 1 and $propertyObj->get_paper_prologue() != '') {
      $questiondata['prologue'] = $propertyObj->get_paper_prologue();
      $questiondata['displayprologue'] = true;
    } else {
      $questiondata['displayprologue'] = false;
    }

    if ($q_displayed == 0 and $question['theme'] == '') {
      $questiondata['notheme'] = true;
    } else {
      $questiondata['notheme'] = false;
    }

    // Get the media directory object.
    $mediadirectory = rogo_directory::get_directory('media');

    $q_id = $question['q_id'];
    $option_no = count($question['options']);

    // Determine if negative marking is used.
    $neg_marking = false;
    if (isset($question['object']) and method_exists($question['object'], 'is_negative_marked')) {
      $neg_marking = $question['object']->is_negative_marked();
    } else {
      foreach ($question['options'] as $tmp_option) {
        if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
      }
    }

    // Process the order
    $question['option_order'] = array();
    if (isset($question['q_option_order']) and ($question['q_option_order'] == 'random' or $question['q_option_order'] == 'alphabetic')) {
      if (!isset($user_order[$current_screen][$q_id]) or $user_order[$current_screen][$q_id] == '') {
        if ($question['q_option_order'] == 'random') {
          for ($i=0; $i<$option_no; $i++) {
            $question['option_order'][$i] = $i;
          }
          shuffle($question['option_order']);
        } elseif ($question['q_option_order'] == 'alphabetic') {
          $tmp_order_array = array();
          for ($i=0; $i<$option_no; $i++) {
            $tmp_order_array[$i] = strtolower($question['options'][$i]['option_text']);
          }
          asort($tmp_order_array);
          foreach ($tmp_order_array as $key => $value) {
            $question['option_order'][]= $key;
          }
        } else {
          // Make up the order array in the existing order
          for ($i=0; $i<$option_no; $i++) {
            $question['option_order'][$i] = $i;
          }
        }
      } else {
        // Set the order array to what is stored in the users log record
        $question['option_order'] = explode(',', $user_order[$current_screen][$q_id]);
      }

      // Re-arrange the options array
      $new_options = array();
      for ($i=0; $i<$option_no; $i++) {
        $new_options[$i] = $question['options'][$question['option_order'][$i]];
      }
      $question['options'] = $new_options;
    } else {
      // Make up the order array in the existing order
      for ($i=0; $i<$option_no; $i++) {
        $question['option_order'][$i] = $i;
      }
    }

    $question_no++;
    $textboxes_seen = array();

    $li_set = 0;
    $questiondata['likerprev'] = false;
    $questiondata['likercur'] = false;
    if ($old_q_type == 'likert' and $question['q_type'] != 'likert') {
      $questiondata['likerprev'] = true;
    } elseif ($old_q_type != 'likert' and $question['q_type'] == 'likert') {
      $questiondata['likercur'] = true;
    }

    if ($question['theme'] != '') {
      $questiondata['theme'] = $question['theme'];
      $questiondata['displaytheme'] = true;
    } else {
      $questiondata['displaytheme'] = false;
    }

    $questiondata['option_no'] = $option_no;
    $questiondata['displaymethod'] = '';
    $questiondata['negativemarking'] = false;
    $questiondata['papertype'] = $paper_type;
    $questiondata['displaycalc'] = false;
    $questiondata['displayassigned'] = false;
    $questiondata['displaymedia'] = false;
    $questiondata['displaynotes'] = false;
    $questiondata['displayscenario'] = false;
    $questiondata['displayleadin'] = false;
    $questiondata['assigned_number'] = $question['assigned_number'];
    $questiondata['scenario'] = $question['scenario'];
    $questiondata['notes'] = $question['notes'];
    $questiondata['labelcolour'] = $labelcolor;
    $questiondata['leadin'] = $question['leadin'];
    $questiondata['langauge'] = $language;
    $mediadata = self::get_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');
    $questiondata = array_merge($questiondata, $mediadata);
    if ($question['q_type'] != 'info' and $question['q_type'] != 'sct') {
      if ($question['scenario'] != '' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'matrix' and $question['q_type'] != 'likert' and $question['q_type'] != 'enhancedcalc') {
        $questiondata['displayassigned'] = true;
        if ($calculator == 1)  {
          $questiondata['displaycalc'] = true;
        }
        if ($question['notes'] != '') {
          $questiondata['displaynotes'] = true;
        }
        if ($question['scenario'] != '') {
          $questiondata['displayscenario'] = true;
        }
        $li_set = 1;
      }
      if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'area' and $question['q_type'] != 'enhancedcalc') {
        if ($li_set == 0) {
          $questiondata['displayassigned'] = true;
          if ($calculator == 1)  {
            $questiondata['displaycalc'] = true;
          }
        }
        $questiondata['displaymedia'] = true;
        $li_set = 1;
      }
      if ($question['q_type'] != 'likert') {
        if ($li_set == 0) {
          $questiondata['displayassigned'] = true;
          if ($calculator == 1)  {
            $questiondata['displaycalc'] = true;
          }
        }
        $li_set = 1;
        if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and in_array($question['q_type'], array('extmatch', 'matrix', 'enhancedcalc')))) {
          $questiondata['displaynotes'] = true;
        }
        if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'enhancedcalc') {
          $questiondata['displayleadin'] = true;
        }
      }
    }

    $questiondata['displayinfo'] = false;
    if ($question['q_type'] == 'info') {
      // Special processing of Information Blocks.
      if ($li_set == 0) {
        $questiondata['displayinfo'] = true;
      }
      if ($question['q_media'] != '') {
        $questiondata['displaymedia'] = true;
      }
      $questiondata['displayleadin'] = true;
      $li_set = 1;
      $question_no--;
    }

    $part_id = 0;
    $marks = 0;
    if ($question['q_type'] != 'likert') $old_likert_label = '';

    $render->render($questiondata, $string, 'paper/question_header.html');

    // Pre-question processing
    $questiondata['questiontype'] = $question['q_type'];
    $questiondata[$question['q_type']] = false;
    $questiondata['question_no'] = $question_no;
    switch ($question['q_type']) {
      case 'enhancedcalc':
        $questiondata['enhancedcalc'] = true;
        if (isset($user_answers[$current_screen][$q_id])) {
          $d = array();
          $d['useranswer'] = $user_answers[$current_screen][$q_id];
          $question['object']->load($d);
        }
        $question['object']->load_all_user_answers($user_answers);
        break;
      case 'dichotomous':
        $questiondata['dichotomous'] = true;
        $questiondata['dichotomous_type'] = 'YN';
        $questiondata['dichotomous_display_method'] = 0;
        if (substr($question['display_method'],0,2) == 'TF') {
          $questiondata['dichotomous_type'] = 'TF';
        }
        if (strpos($question['display_method'],'Abstain') !== false) {
          $questiondata['dichotomous_display_method'] = 1;
        }
        break;
      case 'mcq':
        $questiondata['mcq'] = true;
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0' and $screen_pre_submitted == 1) {
          $questiondata['unanswered'] = true;
          $unanswered = true;
        } else {
          $questiondata['unanswered'] = false;
        }
        if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
          $questiondata['displaymethod'] = 'vertical';
        } elseif ($question['display_method'] == 'dropdown') {
          $questiondata['displaymethod'] = 'dropdown';
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0') {
            $questiondata['unanswered'] = true;
            $unanswered = true;
          } else {
            $questiondata['unanswered'] = false;
          }
        }
        break;
      case 'mrq':
        $questiondata['mrq'] = true;
        $mrq_correct = 0;
        if ($question['score_method'] == 'Mark per Question') {
          $mrq_correct = $option_no;
        } else {
          for ($i=0; $i<$option_no; $i++) {
            if ($question['options'][$i]['correct'] == 'y') $mrq_correct++;
          }
        }
        if (isset($user_answers[$current_screen][$q_id])) {
          $answer_parts = explode(':', $user_answers[$current_screen][$q_id]);
          $len_answer = strlen($answer_parts[0]);
        } else {
          $len_answer = 0;
        }
        if (isset($answer_parts) and $answer_parts[0] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
          $questiondata['unanswered'] = true;
          $unanswered = true;
        } else {
          $questiondata['unanswered'] = false;
        }
        break;
      case 'rank':
        $questiondata['rank'] = true;
        break;
      case 'likert':
        $questiondata['likert'] = true;
        $questiondata['likertstart'] = false;
        $questiondata['likertna'] = false;
        $questiondata['likertscenario'] = false;
        $questiondata['displaylikertnotes'] = false;
        $questiondata['displaylikertscenario'] = false;
        $na = false;
        $likert_display = explode('|',$question['display_method']);
        $likert_col_no = substr_count($question['display_method'],'|');
        if (strtolower($old_likert_label) != strtolower($question['display_method']) or $question['theme'] != '') {
          if ($likert_col_no != $old_likert_cols) {
            $questiondata['likertstart'] = true;
          }
          if ($likert_display[$likert_col_no] == 'true') {
            $likert_col_no++;
            $na = true;
          }
          if ($question['notes'] != '') {
            $questiondata['displaylikertnotes'] = true;
            $questiondata['likertnotescolspan'] = $likert_col_no + 1;
          }
          if ($question['scenario'] != '') {
            $questiondata['displaylikertscenario'] = true;
            $questiondata['likertscenariocolspan'] = $likert_col_no + 2;
          }
          if ($na == true) {
            $questiondata['likertna'] = true;
          }
          $questiondata['likertdisplay'][0] = $likert_display[0];
          $temp_end = substr_count($question['display_method'],'|') - 1;
          for ($i=1; $i<=$temp_end; $i++) {
            $questiondata['likertdisplay'][$i] = $likert_display[$i];
          }
        } else {
          $questiondata['likertscenario'] = true;
          $questiondata['displaylikertscenario'] = true;
          $questiondata['likertscenariocolspan'] = $likert_col_no + 2;
        }
        $old_likert_label = $question['display_method'];
        $old_likert_cols = $likert_col_no;
        break;
      case 'extmatch':
      case 'matrix':
        $questiondata[$question['q_type']] = true;
        $matching_scenarios = explode('|', $question['scenario']);
        $matching_media = explode('|', $question['q_media']);
        $matching_media_width = explode('|', $question['q_media_width']);
        $matching_media_height = explode('|', $question['q_media_height']);
        $matching_options = array();
        if (isset($user_answers[$current_screen][$q_id])) {
          $matching_users_answers = explode('|', $user_answers[$current_screen][$q_id]);
        } else {
          $matching_users_answers = array();
        }
        $matching_answers = explode('|', $question['options'][0]['correct']);
        break;
      case 'sct':
        $questiondata['sct'] = true;
        $questiondata['displaysctcalc'] = false;
        $questiondata['displaysctmedia'] = false;
        $questiondata['displaysctscenario'] = false;
        $questiondata['displaysctnotes'] = false;
        $questiondata['sctheaderset'] = false;
        if ($question['scenario'] != '') {
          $questiondata['displaysctscenario'] = true;
          if ($calculator == 1) {
            $questiondata['displaysctcalc'] = true;
          }
          if ($question['notes'] != '') {
            $questiondata['displaysctnotes'] = true;
          }
          $li_set = 1;
          $questiondata['sctheaderset'] = true;
        }
        if ($question['q_media'] != '') {
          if ($li_set == 0) {
            if ($calculator == 1) {
              $questiondata['displaysctcalc'] = true;
            }
          }
          $questiondata['displaysctmedia'] = true;
          $li_set = 1;
        }

        $sct_parts = explode('~',$question['leadin']);
        $sct_titles = array(1=>$string['hypothesis'], 2=>$string['investigation'], 3=>$string['prescription'], 4=>$string['intervention'], 5=>$string['treatment']);
        $questiondata['scttitle'] = $sct_titles[$question['display_method']];
        $questiondata['sctpart0'] = $sct_parts[0];
        $questiondata['sctpart1'] = $sct_parts[1];
        $questiondata['scttitlelower'] = $string['thenthis'] . " " . mb_strtolower($sct_titles[$question['display_method']], 'UTF-8') . " " . $string['is'] . ":";

        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0' and $screen_pre_submitted == 1) {
          $questiondata['unanswered'] = true;
          $unanswered = true;
        } else {
          $questiondata['unanswered'] = false;
        }
        $marks = 1;
        break;
    }

    $questiondata['displayoptionmedia'] = false;
    // Processing for each stem.
    foreach ($question['options'] as $display_option) {
      $part_id++;
      $questiondata['part_id'] = $part_id;
      $questiondata['option'][$part_id]['optionmedia'] = self::get_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
      $questiondata['option'][$part_id]['optiontext'] = $display_option['option_text'];
      $tmp_part_id = $question['option_order'][$part_id-1] + 1;
      $questiondata['option'][$part_id]['option_no'] = 'q' . $question_no . '_' . $tmp_part_id;
      $questiondata['option'][$part_id]['tmp_part_id'] = $tmp_part_id;
      switch ($question['q_type']) {
        case 'area':
          $questiondata['area'] = true;
          $default_ans  = '100,0,0,0,0,0';

          if (isset($user_answers[$current_screen][$q_id])) {
            $tmp_user_answer = $user_answers[$current_screen][$q_id];
          } else {
            $tmp_user_answer = $default_ans;
          }

          $answer_parts = explode(';', $tmp_user_answer);
          if (isset($answer_parts[1])) {
            $tmp_user_answer = substr($answer_parts[1], 0, -2);
            $full_user_ans = $user_answers[$current_screen][$q_id];
          } else {
            $tmp_user_answer = '';
            $full_user_ans = $default_ans;
          }

          if (isset($user_answers[$current_screen][$q_id]) and $tmp_user_answer == $default_ans and  $screen_pre_submitted == 1) {
            $questiondata['unanswered'] = true;
            $unanswered = true;
            $tmp_bgcolor = '#FFC0C0';
          } else {
            $questiondata['unanswered'] = false;
            $tmp_bgcolor = $bgcolor;
          }

          $questiondata['mediawidth'] += 2;
          $questiondata['mediaheight'] += 27;
          $questiondata['areadisplay'] = $display_option['correct'];
          $questiondata['areauseranswer'] = $tmp_user_answer;
          $questiondata['areafulluseranswer'] = $full_user_ans;
          $marks += $display_option['marks_correct'];
          break;
        case 'dichotomous':
          if (isset($user_answers[$current_screen][$q_id])) {
            $tmp_user_answer = substr($user_answers[$current_screen][$q_id], $question['option_order'][$part_id-1], 1);
          } else {
            $tmp_user_answer = '';
          }
          if (($question['display_method'] == 'TF_NegativeAbstain') or ($question['display_method'] == 'TF_NegativeAbstainHalf') or ($question['display_method'] == 'TF_PostiveAbstain') or ($question['display_method'] == "YN_NegativeAbstain")) {
            $abstain = true;
          } else {
            $abstain = false;
          }

          $class_mod = '';
          $questiondata['option'][$part_id]['unanswered'] = false;
          if ($tmp_user_answer == 'u' and $screen_pre_submitted == 1) {
            $class_mod = ' class="unans"';
            $unanswered = true;
            $questiondata['option'][$part_id]['unanswered'] = true;
          }
          $questiondata['option'][$part_id]['dichotomouspartid'] = $part_id;
          $questiondata['option'][$part_id]['dichotomoususeranswer'] = $tmp_user_answer;
          $questiondata['option'][$part_id]['dichotomousabstain'] = $abstain;
          if ($display_option['o_media'] != '') {
            $questiondata['option'][$part_id]['displayoptionmedia'] = true;
          }
          $marks += $display_option['marks_correct'];
          break;
        case 'enhancedcalc':
          // no options for enhanced calc now stored in settings

          $extra = array(
            'num_on_screen' => $question_no,
            'current_question' => $question,
          );

          $question['object']->render_paper($extra);
          $question['object']->load_all_user_answers($user_answers);

          $marks += $question['object']->calculate_question_mark();

          break;
        case 'likert':
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
            $unanswered = true;
            $questiondata['unanswered'] = true;
          } else {
            $questiondata['unanswered'] = false;
          }
          $scale_size = substr_count($question['display_method'],'|');
          $questiondata['likertscaledisplay'] = false;
          $questiondata['likertoptionid'] = $question_no . "_" . $part_id;
          if ($likert_display[$scale_size] == 'true') {
            $questiondata['likertscaledisplay'] = true;
            $questiondata['likertscalena'] = false;
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'n/a') {
              $questiondata['likertscalena'] = true;
            }
          }
          for ($i=1; $i<=$scale_size; $i++) {
            $optionID = 'q' . $question_no . '_' . $part_id;
            if (isset($user_answers[$current_screen][$q_id]) and $i == $user_answers[$current_screen][$q_id]) {
              $questiondata['likertscale'][$i] = true;
            } else {
              $questiondata['likertscale'][$i] = false;
            }
          }
          break;
        case 'mcq':
          $questiondata['mcqpartid'] = $tmp_part_id;
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            $questiondata['option'][$part_id]['selected'] = true;
          } else {
            $questiondata['option'][$part_id]['selected'] = false;
          }
          $questiondata['option'][$part_id]['optiontextdisplay'] = false;
          if ($display_option['option_text'] != '') {
            $questiondata['option'][$part_id]['optiontextdisplay'] = true;
          }
          if ($display_option['o_media'] != '') {
            $questiondata['option'][$part_id]['displayoptionmedia'] = true;
          }
          if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
            $questiondata['displaymethod'] = 'vertical';
            if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
              $class_type = 'inact';
              $questiondata['option'][$part_id]['inact'] = true;
            } else {
              $questiondata['option'][$part_id]['inact'] = false;
              $class_type = 'act';
            }
          } elseif ($question['display_method'] == 'horizontal') {
            $questiondata['displaymethod'] = 'horizontal';
          }
          if ($tmp_part_id == $display_option['correct']) $marks = $display_option['marks_correct'];
          break;
        case 'mrq':
          $questiondata['option'][$part_id]['mrq_correct'] = $mrq_correct;
          $questiondata['option'][$part_id]['optiontextdisplay'] = false;
          if ($display_option['option_text'] != '') {
            $questiondata['option'][$part_id]['optiontextdisplay'] = true;
          }
          if ($display_option['o_media'] != '') {
            $questiondata['option'][$part_id]['displayoptionmedia'] = true;
          }
          if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1) == 'y') {
            $questiondata['option'][$part_id]['selected'] = true;
          } else {
            $questiondata['option'][$part_id]['selected'] = false;
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$part_id-1,1) == '1') {
            $class_type = 'inact';
            $questiondata['option'][$part_id]['inact'] = true;
          } else {
            $questiondata['option'][$part_id]['inact'] = false;
            $class_type = 'act';
          }
          if ($question['score_method'] == 'Mark per Option') {
            if ($display_option['correct'] == 'y') $marks += $display_option['marks_correct'];  // Mark for correct options only
          } elseif ($question['score_method'] == 'Mark per Question') {
            if ($part_id == 1) $marks += $display_option['marks_correct'];
          } else {
            $marks += $display_option['marks_correct'];  // Mark for each and every item
          }
          break;
        case 'extmatch':
        case 'matrix':
          $matching_options[] = $display_option['option_text'];
            break;
        case 'rank':
          if (isset($user_answers[$current_screen][$q_id])) {
            $rank_answers = explode(',', $user_answers[$current_screen][$q_id]);
          } else {
            $rank_answers = '';
          }
          $total_rank_no = 0;
          $require_na = false;
          for ($i=0; $i<$option_no; $i++) {
            if ($question['options'][$i]['correct'] != 0 or $paper_type == '3') {
              $total_rank_no++;
            }
            if ($question['options'][$i]['correct'] == 0) $require_na = true;
          }
          $tmp_user_answers = 0;

          if ($rank_answers != '') {
            for ($i=0; $i<count($rank_answers); $i++) {
              if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0 and $rank_answers[$i] != 'u') {
                $tmp_user_answers++;
              }
            }
          }

          if ($question['score_method'] == 'Mark per Option') {
            $answers_needed = $option_no;
          } else {
            $answers_needed = $total_rank_no;
          }
          $questiondata['option'][$part_id]['unans'] = false;
          if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == 'u' and $screen_pre_submitted == 1 and $tmp_user_answers < $answers_needed) {
            $questiondata['option'][$part_id]['unans'] = true;
            $unanswered = true;
          }
          $questiondata['option'][$part_id]['na'] = false;
          if ($require_na) {
            $questiondata['option'][$part_id]['na'] = true;
            if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == '0') {
              $questiondata['option'][$part_id]['selected'] = true;
            } else {
              $questiondata['option'][$part_id]['selected'] = false;
            }
          }
          $questiondata['option'][$part_id]['totalrank'] = $total_rank_no;
          for ($i=1; $i<=$total_rank_no; $i++) {
            if (isset($rank_answers[$tmp_part_id - 1]) and $i == $rank_answers[$tmp_part_id - 1]) {
              $questiondata['option'][$part_id]['selected'] = true;
            } else {
              $questiondata['option'][$part_id]['selected'] = false;
            }
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
            $questiondata['option'][$part_id]['inact'] = true;
          } else {
            $questiondata['option'][$part_id]['inact'] = false;
          }
          if ($display_option['correct'] != 0) {
            $marks += $display_option['marks_correct'];
          } elseif ($question['score_method'] == 'Mark per Option') {
            $marks += $display_option['marks_correct'];
          }
          break;
        case 'sct':
          $tmp_part_id = $question['option_order'][$part_id-1] + 1;
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            $questiondata['option'][$part_id]['selected'] = true;
          } else {
            $questiondata['option'][$part_id]['selected'] = false;
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
            $questiondata['option'][$part_id]['inact'] = true;
          } else {
            $questiondata['option'][$part_id]['inact'] = false;
          }
          $questiondata['option'][$part_id]['optiontextdisplay'] = false;
          if ($display_option['option_text'] != '') {
            $questiondata['option'][$part_id]['optiontextdisplay'] = true;
          }
          break;
        case 'true_false':
          $questiondata['true_false'] = true;
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
            $questiondata['unanswered'] = true;
            $unanswered = true;
          } else {
            $questiondata['unanswered'] = false;
          }

          $questiondata['trueselected'] = false;
          $questiondata['falseselected'] = false;
          $questiondata['abstainselected'] = false;
          if ($question['display_method'] == 'dropdown') {
            $questiondata['displaymethod'] = 'dropdown';
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 't') {
              $questiondata['trueselected'] = true;
            }
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'f') {
              $questiondata['falseselected'] = true;
            }
          } else {
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 't') {
              $questiondata['trueselected'] = true;
            }
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'f') {
              $questiondata['falseselected'] = false;
            }

            if ($question['display_method'] == 'horizontal') {
              $questiondata['displaymethod'] = 'horizontal';
            } elseif ($question['display_method'] == 'vertical') {
              $questiondata['displaymethod'] = 'vertical';
            }
            $questiondata['neg_marking'] = $neg_marking;
            if ($neg_marking) {
              if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
                $questiondata['abstainselected'] = true;
              }
            }
          }
          $marks = $display_option['marks_correct'];

          break;
        case 'blank':
          $questiondata['blank'] = true;
          $ans = '';
          $blank_mark = array();
          $display_option['option_text'] = str_replace('&nbsp;',' ',$display_option['option_text']);
          $blank_details = preg_split("/\[blank|\[\/blank\]/", $display_option['option_text']);
          if (isset($user_answers[$current_screen][$q_id])) {
            $user_answers[$current_screen][$q_id] = str_replace('&nbsp;',' ',$user_answers[$current_screen][$q_id]);
          }

          if (isset($user_answers[$current_screen][$q_id])) {
            $blank_user_answers = json_decode($user_answers[$current_screen][$q_id]);
          } else {
            $blank_user_answers = array();
          }

          if ($question['display_method'] == 'textboxes') {
            $questiondata['displaymethod'] = 'textboxes';
          }
          $count = 0;
          $itemcount = 1;
          for ($blank_count = 0; $blank_count < count($blank_details); $blank_count++) {
            if ($blank_details[$blank_count] === '') {
              continue;
            } else {
              $count++;
            }
            if (substr($blank_details[$blank_count], 0, 1) === ']') {
              $questiondata['option'][$count]['itemtype'] = 'blank';
              $questiondata['option'][$count]['itemcount'] = $itemcount;
              $itemcount++;
              if ($question['display_method'] == 'textboxes') {
                $sizeresults = array();
                $not_used = preg_match("|size=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$sizeresults);
                if (isset($sizeresults[1]) and $sizeresults[1] != '') {
                  $blank_size[$blank_count] = $sizeresults[1];
                } else {
                  $blank_size[$blank_count] = 15;
                }
                $questiondata['option'][$count]['size'] = $blank_size[$blank_count];
                if ( (isset($blank_user_answers[$blank_count - 1]) and $blank_user_answers[$blank_count - 1] == 'u') and (isset($screen_pre_submitted) and $screen_pre_submitted == 1) ) {
                  $unanswered = true;
                  $questiondata['option'][$count]['unans'] = true;
                } else {
                  $questiondata['option'][$count]['unans'] = false;
                  if (isset($blank_user_answers[$blank_count - 1])) {
                    $ans = $blank_user_answers[$blank_count - 1];
                  }
                  $encoded_ans = htmlentities($ans, ENT_COMPAT | ENT_HTML5, Config::get_instance()->get('cfg_page_charset'), false);
                  $questiondata['option'][$count]['encoded_ans'] = $encoded_ans;
                }
              } else {
                $answer_list = explode(',', ltrim($blank_details[$blank_count], ']'));
                // Ensure that the correct answer is filtered in the same way as the user's answer.
                $answer_list = param::clean_array($answer_list, param::TEXT);
                shuffle($answer_list);            // Shuffle the answers up.
                for ($i=0; $i<count($answer_list); $i++) {
                  if (isset($answer_list[$i]) and isset($blank_user_answers[$blank_count - 1]) and html_entity_decode(trim($answer_list[$i])) == html_entity_decode(trim($blank_user_answers[$blank_count - 1]))) {
                    $questiondata['option'][$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => true);
                  } else {
                    $questiondata['option'][$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' =>  false);
                  }
                }
                if (isset($blank_user_answers[$part_id - 1]) and $blank_user_answers[$part_id - 1] == 'u' and $screen_pre_submitted == 1) {
                  $questiondata['option'][$count]['unans'] = true;
                  $unanswered = true;
                } else {
                  $questiondata['option'][$count]['unans'] = false;
                }
                $part_id++;
              }
              $results=array();
              $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
              if (isset($results[1]) and $results[1] != '') {
                $blank_mark[$blank_count] = $results[1];
              } else {
                $blank_mark[$blank_count] = $display_option['marks_correct'];
              }
            } else {
              $questiondata['option'][$count]['itemtype'] = 'blurb';
              $questiondata['option'][$count]['itemvalue'] = $blank_details[$blank_count];
            }
          }
          if ($question['score_method'] == 'Mark per Option') {
            if (count($blank_mark) > 0) {
              foreach ($blank_mark as $individual_mark) $marks += $individual_mark;
            }
          } else {
            $marks = $display_option['marks_correct'];
          }
          break;
        case 'textbox':
          $questiondata['textbox'] = true;
          $questiondata['unanswered'] = false;
          if (!in_array($question_no, $textboxes_seen)) {
            $textboxes_seen[] = $question_no;
            $settings = json_decode($question['settings'], true);
            $questiondata['editorcolumns'] = $settings['columns'];
            $questiondata['editorrows'] = $settings['rows'];
            if (!isset($settings['editor']) or $settings['editor'] == 'plain' or $settings['editor'] == 'mathjax') {
              $questiondata['editor'] = 'plain';
              if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
                $questiondata['unanswered'] = true;
                $questiondata['useranswer'] = $user_answers[$current_screen][$q_id];
                $unanswered = true;
              } else {
                $ans = '';
                if (isset($user_answers[$current_screen][$q_id])) {
                  $ans = $user_answers[$current_screen][$q_id];
                }
                $questiondata['useranswer'] = $ans;
                if ($settings['editor'] == 'mathjax') {
                  $questiondata['editormathjax'] = true;
                  $questiondata['id'] = 'q' . $question_no;
                }
              }
            } else {
              include_once('wysiwyg_editor.inc');
              $ans = '';
              if (isset($user_answers[$current_screen][$q_id])) {
                $ans = $user_answers[$current_screen][$q_id];
              }

              $textbox_width  = ( 40 + ( $settings['columns'] * 8 ) );
              $textbox_height = ( $settings['rows'] * 28 );

              $background_colour = '';

              if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
                $background_colour = 'background-color:red;';
              }
              ?>
              <textarea class="mceEditor" id="q<?php echo $question_no ?>" name="q<?php echo $question_no ?>" style="<?php echo $background_colour; ?>width:<?php echo $textbox_width ?>px; height:<?php echo $textbox_height ?>px"><?php echo $ans ?></textarea><?php echo "\n"; ?>
              <?php
            }
            $marks += $display_option['marks_correct'];
          }
          break;
        case 'hotspot':
          $questiondata['hotspot'] = true;
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and  $screen_pre_submitted == 1) {
            $unanswered = true;
          }
          $hotspot_no = substr_count($question['options'][0]['correct'],'|') + 1;
          $tmp_height = $question['q_media_height'] + 30;
          if ($tmp_height < (($hotspot_no * 36) + 25)) $tmp_height = (($hotspot_no * 36) + 25);

          $tmp_correct = str_replace("'", "\'", trim($question['options'][0]['correct']));
          $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
          $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);

          $questiondata['tmpcorrect'] = $tmp_correct;
          $questiondata['mediawidth'] += 300;
          $questiondata['mediaheight'] = $tmp_height-29;

          if (isset($user_answers[$current_screen][$q_id])) {
            $questiondata['useranswer'] = trim($user_answers[$current_screen][$q_id]);
            $questiondata['screensubmitted'] = $screen_pre_submitted;
          }

          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] != '') {
            $questiondata['unanswered'] = false;
          } else {
            $questiondata['unanswered'] = true;
          }
          if ($question['score_method'] == 'Mark per Question') {
            $marks = $display_option['marks_correct'];
          } else {
            $marks = (substr_count($question['options'][0]['correct'],'|') + 1) * $display_option['marks_correct'];
          }
          break;
        case 'labelling':
          $questiondata['labelling'] = true;
          $tmp_labels = 0;
          $max_col1 = 0;
          $max_col2 = 0;
          $tmp_first_split = explode(';', $question['options'][0]['correct']);
          $tmp_second_split = explode('|', $tmp_first_split[11]);
          $label_width = $tmp_first_split[5];
          $label_height = $tmp_first_split[6];
          $hyphen = false;
          foreach ($tmp_second_split as $ind_label) {
            $label_parts = explode('$', $ind_label);
            if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
              if (mb_strstr($label_parts[4], '-') !== false) $hyphen = true;
              $tmp_labels++;
              if ($label_parts[2] > 219) $marks += $display_option['marks_correct'];
              if ($label_parts[0] < 10) {
                $max_col1 = $label_parts[0];
              } else {
                $max_col2 = $label_parts[0];
              }
            }
          }
          $max_col2-=10;
          $max_label = max($max_col1, $max_col2);

          if ($question['score_method'] == 'Mark per Question') {
            $marks = $display_option['marks_correct'];
          }
          if (($label_width < 80 and $hyphen) or ($label_width < 104 and !$hyphen)) {    // Two columns
            $computed_height = round(($label_height + 6) * ceil($tmp_labels / 2)) + 10;
            $tmp_height = max($question['q_media_height'], $computed_height);
          } else {                    // Single column
            $computed_height = round(($label_height + 6) * $tmp_labels) + 10;
            $tmp_height = max($question['q_media_height'], $computed_height);
          }

          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0$' . $marks . ';' and  $screen_pre_submitted == 1) {
            $unanswered = true;
          }
          $tmp_correct = trim($question['options'][0]['correct']);
          $tmp_correct = str_replace("'", "&#039;", $tmp_correct);

          $questiondata['mediawidth'] += 220;
          $questiondata['mediaheight'] = $tmp_height;
          $questiondata['tmpcorrect'] = $tmp_correct;
          $questiondata['marks'] = $marks;

          if (isset($user_answers[$current_screen][$q_id])) {
            $questiondata['useranswer'] = trim($user_answers[$current_screen][$q_id]);
            $questiondata['marks_correct'] = $display_option['marks_correct'];
            $questiondata['marks_incorrect'] = $display_option['marks_incorrect'];
            $questiondata['score_method'] = $question['score_method'];
          }

          if (!isset($user_answers[$current_screen][$q_id]) or $user_answers[$current_screen][$q_id] == '') {
            $questiondata['unanswered'] = true;
          } else {
            $questiondata['unanswered'] = false;
          }
          break;
        case 'flash':
          // Question type is deprecated. Rogo only supports pre-existing flash questions.
          $questiondata['flash'] = true;
          if ($question['scenario'] != '') {
            $questiondata['displayscenario'] = true;
          } else {
            $questiondata['displayscenario'] = false;
          }
        $marks += $display_option['marks_correct'];
        break;
      }                  // End switch
    }                    // End foreach loop

    if ($question['q_type'] == 'mcq') {
      if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
        $questiondata['displayother'] = false;
        if ($question['display_method'] == 'vertical_other' and $paper_type == '3') {
          $questiondata['displayother'] = true;
          if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],0,5) == 'other') {
            $questiondata['otherselected'] = true;
            $questiondata['other'] = substr($user_answers[$current_screen][$q_id],6);
          } else {
            $questiondata['otherselected'] = false;
          }
        }
        if ($display_option['marks_incorrect'] < 0) {
          $questiondata['negativemarking'] = true;
            // Include an abstain option if negative marking is used.
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
              $questiondata['abstainselected'] = true;
            } else {
              $questiondata['abstainselected'] = false;
            }
        }
      } elseif ($question['display_method'] == 'horizontal') {
        if ($display_option['marks_incorrect'] < 0) {
          $questiondata['negativemarking'] = true;
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
            $questiondata['abstainselected'] = true;
          } else {
            $questiondata['abstainselected'] = false;
          }
        }
      }
    } elseif (in_array($question['q_type'], array('dichotomous', 'mrq', 'rank'))) {
      $answer = (isset($user_answers[$current_screen][$q_id])) ? $user_answers[$current_screen][$q_id] : '';
      $questiondata['displayother'] = false;
      if ($question['display_method'] == 'other') {
        $questiondata['displayother'] = true;
        $part_id++;
        $questiondata['part_id'] = $part_id;
        if ($answer != '' and substr($answer,($part_id - 1),1) == 'y') {
          $questiondata['otherselected'] = true;
        } else {
          $questiondata['otherselected'] = false;
        }
        $questiondata['other'] = substr($answer, $part_id);
      } elseif ($question['q_type'] == 'mrq' and $display_option['marks_incorrect'] < 0) {
        $questiondata['negativemarking'] = true;
        // Include an abstain option if negative marking is used.
        if ($answer != '' and $answer == 'a') {
          $questiondata['abstainselected'] = true;
        } else {
          $questiondata['abstainselected'] = false;
        }
      }
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      }
    } elseif ($question['q_type'] == 'extmatch') {
      $questiondata['extmatch'] = true;
      $matching_answers = explode('|', $question['options'][0]['correct']);
      $questiondata['displayextmatchmedia'] = false;
      if ($matching_media[0] != '') {
        $questiondata['displayextmatchmedia'] = true;
        $mediadata = self::get_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
        $questiondata = array_merge($questiondata, $mediadata);
      }

      array_unshift($matching_scenarios, '');
      $max_scenarios = max(count($matching_scenarios), count($matching_media));
      $scenario_no = 0;
      for ($part_id = 1; $part_id < $max_scenarios; $part_id++) {
        if ((isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id],'<img>')) != '')
          or (isset($matching_media[$part_id]) and $matching_media[$part_id] != '')) {
          $scenario_no++;
        }
      }

      $col1_no = ceil(count($matching_options) / 2);
      $questiondata['extmatchsplit'] = $col1_no-1;
      for ($i=0; $i<count($matching_options); $i++) {
        $questiondata['extmatchoptions'][$i] = $matching_options[$i];
      }
      $questiondata['extmathmatching_options'] = count($matching_options);
      for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
        if(isset($matching_answers[$part_id-1])) {
          $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
          $marks += (substr_count($matching_answers[$part_id-1],'$') + 1) * $display_option['marks_correct'];
        } else {
          $answer_no = 0;
        }
        $questiondata['extmatchstem'][$part_id-1]['answer_no'] = $answer_no;
        if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') {
          $questiondata['extmatchstem'][$part_id-1]['scenario'] = $matching_scenarios[$part_id];
        }
        $questiondata['extmatchstem'][$part_id-1]['display'] = false;
        if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
          $questiondata['extmatchstem'][$part_id-1]['display'] = true;
          $questiondata['extmatchstem'][$part_id-1]['media'] = self::get_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '');
        }
        if(isset($matching_answers[$part_id-1])) {
          $sub_answers = explode('$', $matching_answers[$part_id - 1]);
        } else {
          $sub_answers = array();
        }
        $list_size = 10;
        if (count($matching_options) < 10) $list_size = count($matching_options);
        if ($answer_no == 1) {
          if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == 'u' and $screen_pre_submitted == 1) {
            $questiondata['extmatchstem'][$part_id-1]['answered'] = false;
            $unanswered = true;
          } else {
            $questiondata['extmatchstem'][$part_id-1]['answered'] = true;
          }
        } else {
          $questiondata['extmatchstem'][$part_id-1]['listsize'] = $list_size;
          $questiondata['extmatchstem'][$part_id-1]['sub_answers'] = count($sub_answers);
          $questiondata['extmatchstem'][$part_id-1]['matching_options'] = count($matching_options);
          if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
            $questiondata['extmatchstem'][$part_id-1]['answered'] = false;
            $unanswered = true;
          } else {
            $questiondata['extmatchstem'][$part_id-1]['answered'] = true;
          }
        }

        $multi_answers = array();
        if (isset($matching_users_answers[$part_id - 1])) {
          $multi_answers = explode('$', $matching_users_answers[$part_id - 1]);
        }

        $tmp_option_no = 0;
        for ($option_no=0; $option_no<count($matching_options); $option_no++) {
          $tmp_answer_match = false;
          foreach ($multi_answers as $separate_tmp_answer) {
            if ($separate_tmp_answer == ($question['option_order'][$tmp_option_no]+1)) {
              $tmp_answer_match = true;
            }
          }
          if ($tmp_answer_match == true) {
            $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['selected'] = true;
          } else {
            $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['selected'] = false;
          }
          $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['value'] = $question['option_order'][$option_no]+1;
          $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['option'] = chr($option_no+65) . '. ' . $matching_options[$option_no];
          $tmp_option_no++;
        }
      }
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      }
    } elseif ($question['q_type'] == 'matrix') {
      $part_id = 1;
      echo '<table cellpadding="2" cellspacing="0" border="1" class="matrix">';
      echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
      foreach ($matching_options as $single_option) {
        echo '<td>' . $single_option . '</td>';
      }
      echo "</tr>\n";
      foreach ($matching_scenarios as $single_scenario) {
        if (trim($single_scenario) != '') {
          if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
            echo "<tr class=\"unans\">\n";
            $unanswered = true;
          } else {
            echo "<tr>\n";
          }
          echo '<td align="right">' . chr(64 + $part_id) . '.</td><td>' . $single_scenario . '</td>';
          $answer_no = 1;
          foreach ($matching_options as $single_option) {
            $tmp_part_id = $question['option_order'][$answer_no-1] + 1;
            if (isset($matching_users_answers[$part_id-1]) and $matching_users_answers[$part_id-1] == $tmp_part_id) {
              echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $tmp_part_id . '" checked="checked" /></div></td>';
            } else {
              echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $tmp_part_id . '" /></div></td>';
            }
            $answer_no++;
          }
          echo "</tr>\n";
          $part_id++;
        }
      }
      echo '</table>';
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      } else {
        $marks = $part_id - 1;
      }
    } elseif ($question['q_type'] == 'sct') {
      echo '</table>';
    }

    // Write out the hidden field for the dismiss facility.
    if (in_array($question['q_type'], array('mcq', 'mrq', 'rank'))) {
      if (isset($user_dismiss[$current_screen][$q_id]) and $user_dismiss[$current_screen][$q_id] != '') {
        echo "\n<div><input type=\"hidden\" name=\"dismiss$question_no\" id=\"dismiss$question_no\" value=\"" . $user_dismiss[$current_screen][$q_id] . "\" /></div>\n";
      } else {
        echo "\n<div><input type=\"hidden\" name=\"dismiss$question_no\" id=\"dismiss$question_no\" value=\"" . str_repeat('0', count($question['options'])) . "\" /></div>\n";
      }
    }

    // Display possible marks for question (if not Survey)
    if (!in_array($question['q_type'], array('hotspot', 'likert', 'info', 'enhancedcalc', 'matrix'))) {
      echo '</blockquote>';
    }
    if ($paper_type < 3) {
      if ($marks == 0) {
        echo "</td></tr>\n<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      } else {
        echo "<div id=\"q{$question_no}_mk\" class=\"mk\">($marks ";
        if ($marks == 1) {
          echo $string['mark'];
        } else {
          echo $string['marks'];
        }
        if ($question['score_method'] == 'Bonus Mark') {
          $plural = ($display_option['marks_correct'] == 1) ?  $string['mark'] : $string['marks'];
          echo ' ' . sprintf($string['bonusmark'], $display_option['marks_correct'], $plural);  // Used on ranking questions
        }
        if ($neg_marking) echo ', ' . $string['negmarking'];
        echo ")</div>\n<br /></td></tr>\n";
      }
    } else {
      echo "</td></tr>\n";
    }
    if ($question['q_type'] != 'info') echo "<input type=\"hidden\" name=\"order$question_no\" value=\"" . implode(',', $question['option_order']) . "\" />\n";
    $used_questions[$q_id] = $q_id;

    // Plugin question use there own templating.
    if ($question['q_type'] != 'enhancedcalc') {
      $render->render($questiondata, $string, 'paper/question.html');
    }
  }

  /**
   * Function takes a filename with the width and height and returns appropriate HTML to display the media type.
   *
   * @param mixed $filename
   * @param mixed $width
   * @param mixed $height
   * @param mixed $imageid
   *
   */
  public static function get_media($filename, $width, $height, $border_color, $imageid=-1, $locked=false) {

    $configObject = Config::get_instance();
    $render = new render($configObject);

    $mediadirectory = rogo_directory::get_directory('media');
    $mediadata = array();

    $fn_parts = pathinfo($filename);

    $mediadata['mediaid'] = $imageid;
    $mediadata['mediafile'] = $filename;
    $mediadata['mediawidth'] = $width;
    $mediadata['mediaheight'] = $height;
    $mediadata['mediaurl'] = $mediadirectory->url($filename);
    // Is the file an image or something else (e.g. RasMol)?
    if (!array_key_exists('extension', $fn_parts)) {
      $mediadata['mediatype'] = 1;
    } elseif (array_key_exists('extension', $fn_parts) and in_array(strtolower($fn_parts['extension']), array('gif', 'jpg', 'jpeg', 'png'))) {
      $mediadata['mediatype'] = 2;
      if ($border_color == '') {
        $mediadata['mediaborder'] = false;
      } else {
        $mediadata['mediaborder'] = true;
        $mediadata['mediabordercolour'] = $border_color;
      }
    } elseif (in_array($fn_parts['extension'], array('wav', 'wma', 'mid'))) {
      $mediadata['mediatype'] = 3;
    } elseif (in_array($fn_parts['extension'], array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf'))) {
      $mediadata['mediatype'] = 4;
    } elseif ($fn_parts['extension'] == 'flv') {
      $mediadata['mediatype'] = 5;
      if ($width == 0 or $height == 0) {
        $width = 320;
        $height = 260;
      }
      $mediadata['mediaurl'] = $mediadirectory->url($filename, false, false, true);
    } elseif ($fn_parts['extension'] == 'mp3') {     // Embed MP3 using HTML5 audio tag.
      $mediadata['mediatype'] = 6;
      $mediadata['mediaedit'] = false;
      if (strpos(Url::fromGlobals(),'/edit/') !== false or strpos(Url::fromGlobals(),'/add/') !== false) {  // Display filename if add or edit script
        $mediadata['mediaedit'] = true;
      }
    } elseif ($fn_parts['extension'] == 'avi' or $fn_parts['extension'] == 'wmv') {
      $mediadata['mediatype'] = 7;
    }
    if ($imageid > -1 and !$locked) {
      $mediadata['mediadelete'] = true;
    } else {
      $mediadata['mediadelete'] = false;
    }
    return $mediadata;
  }
}