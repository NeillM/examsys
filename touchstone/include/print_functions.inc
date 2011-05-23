<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

function gen_random_no($min, $max, $increment, $decimals) {
  if ($decimals > 0) {
    $min = $min * (10 * $decimals);
    $max = $max * (10 * $decimals);
    $increment = $increment * (10 * $decimals);
  }
  if ($increment == 1) {
    $gen_no = rand($min, $max);
  } else {
    $new_max = ($max - $min) / $increment;
    $gen_no = rand(0, $new_max);
    $gen_no *= $increment;
    $gen_no += $min;
  }
  if ($decimals > 0) $gen_no = number_format(($gen_no / (10 * $decimals)), $decimals);
  return $gen_no;
}

function checkVariables($number) {
  global $A, $B, $C, $D, $E, $F, $G, $H;
  eval("\$new_number = \"$number\";");
  return $new_number;
}

function display_options($options,$q_id, $current_theme) {
  global $marking, $question_no, $question_offset, $part_id, $labelcolor, $old_likert_label, $users_answers, $users_confidence, $screen_pre_submitted;

  $option_no = count($options);
  $part_id = 0;
  $marks = 0;
  if ($options[0]['q_type'] != 'likert') $old_likert_scale = '';

  if ($options[0]['q_type'] == 'dichotomous') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
    if ($marking < 2) {
      if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">True</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">False</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'TF_Negative') or ($options[0]['score_method'] == 'TF_Positive')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">True</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">False</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'YN_NegativeAbstain') or ($options[0]['score_method'] == 'YN_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">No</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">No</td></tr>\n";
      }
    } else {
      if ($options[0]['score_method'] == 'TF_Positive' or $options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain') {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">True</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">False</td><td width=\"80\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Confidence</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">No</td><td width=\"80\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Confidence</td></tr>\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'mcq') {
    if ($users_answers[$q_id] == 0 and $screen_pre_submitted == 1) {
      echo '<blockquote style="background-color: #FFC0C0">';
    } else {
      echo '<blockquote>';
    }
    if ($options[0]['score_method'] == 'vertical') {
      echo '<table cellpadding="2" cellspacing="0" border="0">';
    }
    if ($options[0]['score_method'] == 'dropdown') {
      echo '<div class="option"><select name="q' . $question_no . '" size="1">';
      echo '<option value=""></option>';
    }
  } elseif ($options[0]['q_type'] == 'mrq') {
    $len_answer = strlen($users_answers[$q_id]);
    if ($users_answers[$q_id] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
      echo '<blockquote style="background-color: #FFC0C0"><table cellpadding="2" cellspacing="0" border="0">';
    } else {
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
    }
  } elseif ($options[0]['q_type'] == 'rank') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0"><tr>';
    $rank_answers = explode(",", $users_answers[$q_id]);
    $total_rank_no = 0;
    for ($i=0; $i<$option_no; $i++) {
      if ($options[$i]['correct'] < 9990) {
        $total_rank_no++;
      } elseif ($display_option['score_method'] == 'StrictOrder' or $display_option['score_method'] == 'AllItemsCorrect') {
        $total_rank_no++;
      }
    }
    for ($i=1; $i<=$total_rank_no; $i++) {
      echo "<td class=\"option\" style=\"text-align: center\">" . ordinal_suffix($i) . "</td>";
    }
    echo "<td class=\"option\">&nbsp;</td></tr>";
  } elseif ($options[0]['q_type'] == 'calculation') {
    $A = $B = $C = $D = $E = $F = $G = $H = '';    // Reset all variables initially
  } elseif ($options[0]['q_type'] == 'likert') {
    $str = $options[0]['score_method'];
    $likert_display = explode('|',$str);
    $likert_col_no = substr_count($options[0]['score_method'],'|');
    if ($old_likert_label != $str or $current_theme != '') {
      echo '</table><table cellpadding="4" cellspacing="0" border="0">';
      if ($likert_display[$likert_col_no] == 'true') {
        $likert_col_no++;
        $na = true;
      }
      if ($current_notes != '') {
        echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"notes\"><img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"Note\" />&nbsp;<strong>NOTE:</strong>&nbsp;$current_notes</td></tr>\n";
      }
      if ($current_scenario != '') {
        echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">$current_scenario</td></tr>\n";
        echo '</table><br /><table cellpadding="4" cellspacing="0" border="0">';
      }
      echo '<tr><td></td>';
      if ($na == true) {
        echo "<td valign=\"bottom\" style=\"text-align: center; color: $labelcolor; font-size: 80%\">N/A</td>";
      }
      echo "<td valign=\"bottom\" style=\"text-align: center; color: $labelcolor; font-size: 80%\">$likert_display[0]</td>";
      $temp_end = substr_count($options[0]['score_method'],'|') - 1;
      for ($i=1; $i<=$temp_end; $i++) {
        echo "<td valign=\"bottom\" style=\"text-align: center; color: $labelcolor; font-size: 80%\">$likert_display[$i]</td>\n";
      }
    } else {
      if ($current_scenario != '') {
        echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">$current_scenario</td></tr>\n";
      }
    }
    $old_likert_label = $str;
  } elseif ($options[0]['q_type'] == 'extmatch') {
    $matching_scenarios = array();
    $matching_scenarios = explode('|', $options[0]['scenario']);
    $matching_media = array();
    $matching_media = explode('|', $options[0]['q_media']);
    $matching_media_width = array();
    $matching_media_width = explode('|', $options[0]['q_media_width']);
    $matching_media_height = array();
    $matching_media_height = explode('|', $options[0]['q_media_height']);
    $matching_options = array();
    $matching_users_answers = array();
    $matching_users_answers = explode('|', $users_answers[$q_id]);
  } elseif ($options[0]['q_type'] == 'sct') {
    $sct_parts = explode('~',$options[0]['leadin']);
    $sct_titles = array(1=>'Hypothesis',2=>'Investigation',3=>'Prescription',4=>'Intervention',5=>'Treatment');
    echo "<tr><td class=\"question_no\">$question_no.&nbsp;</td><td>\n<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";
    echo "<tr><td colspan=\"2\"><strong>Clinical Vignette</strong><br />" . $options[0]['scenario'] . "</td></tr>\n";
    echo "<tr><td style=\"width:50%\"><strong>" . $sct_titles[$options[0]['score_method']] . "</strong><br />" . $sct_parts[0] . "</td><td style=\"width:50%\"><strong>New Information</strong><br />" . $sct_parts[1] . "</td></tr>\n";
    
    echo '<tr><td colspan=\"2\"><strong>';
    if ($options[0]['score_method'] == 1) {
      echo 'Then this hypothesis becomes:';
    } elseif ($options[0]['score_method'] == 2) {
      echo 'Then this investigation becomes:';
    } elseif ($options[0]['score_method'] == 3) {
      echo 'Then this prescription becomes:';
    } elseif ($options[0]['score_method'] == 4) {
      echo 'Then this intervention becomes:';
    } elseif ($options[0]['score_method'] == 5) {
      echo 'Then this treatment becomes:';
    }
    echo '</strong></td></tr>';
  }
  $i = 0;
  foreach ($options as $display_option) {
    $part_id++;
    //echo $options[0]['q_type'];
    switch ($options[0]['q_type']) {
      case 'calculation':
        $vars = array(1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'F',7=>'H');
        $var = $vars[$part_id];
        $tmp_user_answers = array();
        $figures = explode(',',$display_option['option_text']);
        $$var = gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
      break;
      case 'dichotomous':
        if ($marking == 2) {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
            }
            echo "<td>" . $display_option['option_text'] . "</td></tr>\n";
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          }
          $marks += 3;
        } elseif (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain') or ($options[0]['score_method'] == "YN_NegativeAbstain") or ($options[0]['score_method'] == "YN_PostiveAbstain")) {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          }
          $marks++;
        } else {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /><td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "</td></tr>\n";
            } 
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if (substr($users_answers[$q_id],$part_id-1,1) == 't') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'f') {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } elseif (substr($users_answers[$q_id],$part_id-1,1) == 'u' and $screen_pre_submitted == 1) {
              echo "<tr style=\"background-color: #FFC0C0\"><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          }
          $marks++;
        }
        break;
      case 'likert':
        echo '<tr><td class="question_no">' . (($question_no + $question_offset) - 1) . '.&nbsp;</td>';
        $scale_size = substr_count($options[0]['score_method'],'|');
        if ($likert_display[$scale_size] == 'true') {
          echo "<td style=\"background-color: #FFC0C0; text-align: center; width: 40px; vertical-align: top\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"n/a\" /></td>";
        }
        for ($i=1; $i<=$scale_size; $i++) {
          if ($i == $users_answers[$q_id]) {
            echo "<td style=\"text-align: center; width: 40px; vertical-align: top\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
          } else {
            echo "<td style=\"text-align: center; width: 40px; vertical-align: top\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
          }
        }
        echo "<td>" . $display_option['leadin'] . "</td></tr>\n";
        $marks+=2;
        break;
      case 'mcq':
        if ($options[0]['score_method'] == 'horizontal') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . $display_option['option_text'] . "&nbsp;&nbsp;";
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "&nbsp;&nbsp;";
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "&nbsp;&nbsp;";
          }
        } elseif ($options[0]['score_method'] == 'vertical') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if ($part_id == $users_answers[$q_id]) {
              echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td></tr>\n";
            } else {
              echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td></tr>\n";
            }
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if ($part_id == $users_answers[$q_id]) {
              echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span><br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span><br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if ($part_id == $users_answers[$q_id]) {
              echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            } else {
              echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
            }
          }
        }
        if ($part_id == $display_option['correct']) {
          if ($marking == 2) {
            $marks = (3 * $display_option['marks']);
          } else {
            $marks = $display_option['marks'];
          }
        }
        break;
      case 'mrq':
        if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No image
          if (substr($users_answers[$q_id],$part_id-1,1) == 'y') {
            echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td></tr>\n";
          } else {
            echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td></tr>\n";
          }
        } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and image
          if (substr($users_answers[$q_id],$part_id-1,1) == 'y') {
            echo "<div><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" alt=\"image\" border=\"0\"/></div>\n";
          } else {
            echo "<div><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" alt=\"image\" border=\"0\"/></div>\n";
          }
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and image
          if (substr($users_answers[$q_id],$part_id-1,1) == 'y') {
            echo "<div><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" alt=\"image\" border=\"0\"/></div>\n";
          } else {
            echo "<div><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" alt=\"image\" border=\"0\"/></div>\n";
          }
        }
        if ($display_option['score_method'] == 'SelectedPositive') {
          if ($display_option['correct'] == 'y') $marks++;  // 1 mark for correct options only
        } elseif ($display_option['score_method'] == 'AllItemsCorrect') {
          if ($part_id == 1) $marks++;
        } else {
          $marks++;  // 1 mark for each and every item
        }
        break;
      case 'extmatch':
        $matching_options[] = $display_option['option_text'];
        break;
      case 'rank':
        $rank_answers = array();
        $rank_answers = explode(",", $users_answers[$q_id]);
        $total_rank_no = 0;
        for ($i=0; $i<$option_no; $i++) {
          if ($options[$i]['correct'] < 9990) {
            $total_rank_no++;
          } elseif ($display_option['score_method'] == 'StrictOrder' or $display_option['score_method'] == 'AllItemsCorrect') {
            $total_rank_no++;
          }
        }
           
        if ($part_id == 1 and $display_option['score_method'] == 'BonusMark') {
          $marks++;  // Add one mark at start in case all options are answered correctly
        }
        
        for ($i=1; $i<=$total_rank_no; $i++) {
          echo "<td class=\"option\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>";
        }
        if ($display_option['score_method'] == 'BonusMark') {
          echo "<td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td>\n</tr>\n";
        } else {
          echo "<td>" . $display_option['option_text'] . "</td>\n</tr>\n";
        }
        if ($display_option['score_method'] == 'AllItemsCorrect') {
          if ($part_id == 1) {
            $marks++;
          }
        } elseif ($display_option['correct'] < 9990) {
          $marks++;
        } elseif ($display_option['score_method'] == 'StrictOrder') {
          $marks++;
        }
        break;
      case 'sct':
        echo "<tr><td colspan=\"2\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . $display_option['option_text'] . "</td></tr>\n";
        break;
      case 'blank':
        if ($display_option['score_method'] == 'textboxes') {
          $blank_details = explode('<blank',$display_option['option_text']);
          $array_size = count($blank_details);
          $blank_count = 1;
          while ($blank_count < $array_size) {
            $blank_details[$blank_count] = '<blank' . $blank_details[$blank_count];
            $results = array();
            $not_used = ereg("size=\"([0-9]{1,3})\"",$blank_details[$blank_count],$results);
	 
            if ($results[1]) {
              $blank_size[$blank_count] = $results[1];
            } else {
              $blank_size[$blank_count] = 15;
            }
            $results=array();
            $not_used = preg_match("/mark=\"([0-9]{1,3})\"/",$blank_details[$blank_count],$results);
            if ($results[1]) {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = 1;
            }
            $screen_output[$blank_count] = preg_replace("/<blank.*>.*</blank>/","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
            $blank_count++;
          }
          $screen_output_all = '';
          foreach ($screen_output as $value) {
            $screen_output_all .= $value;
          }
          echo "<p class=\"stem\">" . $blank_details[0].$screen_output_all . "</p>\n";
          foreach ($blank_mark as $individual_mark) {
            $marks += $individual_mark;
          }	   
        } else {
            $blank_details = array();
            $blank_details = explode('<blank',$display_option['option_text']);
            $blank_user_answers = array();
            $blank_user_answers = explode('|', $users_answers[$q_id]);
            $no_blanks = count($blank_details);
            echo '<p>' . $blank_details[0];
            $blank_count = 1;
            while ($blank_count < $no_blanks) {
            $not_used = preg_match("/mark=\"([0-9]{1,3})\"/",$blank_details[$blank_count],$results);
            if ($results[1]) {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = 1;
            }
            $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],'>') + 1));
            $answer_options = array();
            $answer_options = explode("</blank>",$blank_details[$blank_count]);
            $answer_list = array();
            $answer_list = explode(",",$answer_options[0]);
            if ($display_option['score_method'] == 'dropdown') {
              if ($blank_user_answers[$part_id] == 'u' and $screen_pre_submitted == 1) {
                echo "<select name=\"q" . $question_no . "_" . $part_id . "\" style=\"background-color: #FFC0C0\">\n";
              } else {
                echo "<select name=\"q" . $question_no . "_" . $part_id . "\">\n";
              }
              echo "<option value=\"u\"></option>\n";
              $no_answers = count($answer_list);
          
              // Shuffle the answers up.
              for ($i=1; $i<26; $i++) {
                $pos1 = rand(0,$no_answers-1);
                $pos2 = rand(0,$no_answers-1);
                if ($pos1 != $pos2) {
                  $postemp = $answer_list[$pos2];
                  $answer_list[$pos2] = $answer_list[$pos1];
                  $answer_list[$pos1] = $postemp;
                }
              }
          
              for ($i=1; $i<=$no_answers; $i++) {
                if ($answer_list[$i-1] == $blank_user_answers[$blank_count]) {
                  echo "<option value=\"" . $answer_list[$i-1] . "\" selected>" . $answer_list[$i-1]. "</option>\n";
                } else {
                  echo "<option value=\"" . $answer_list[$i-1] . "\">" . $answer_list[$i-1]. "</option>\n";
                }
              }
              echo '</select>';
            } else {
              echo "<input type=\"text\" name=\"q" . $question_no . "_" . $part_id . "\" size=\"" . strlen($answer_list[0]) . "\" />";
            }
            echo $answer_options[1]; // Bit after the closing </blank> tag.
            $blank_count++;
            $part_id++;
          }
          echo '</p>';
          foreach ($blank_mark as $individual_mark) {
            $marks += $individual_mark;
          }	   
        }
        break;
      case 'textbox':
        $textbox_size = explode('x',$display_option['score_method']);
        if ($textbox_size[1] == 1) {
          echo "<blockquote><input type=\"text\" name=\"q" . $question_no . "\" size=\"" . $textbox_size[0] . "\" value=\"" . $users_answers[$q_id] . "\" /></blockquote>\n";
        } else {
          echo "<blockquote><textarea name=\"q" . $question_no . "\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\" />" . $users_answers[$q_id] . "</textarea></blockquote>\n";
        }
        break;
      case 'hotspot':
?>        
               
  <script language="JavaScript">
    var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
    function hotspot<?php echo $question_no; ?>_DoFSCommand(command, args) {
      var hotspot<?php echo $question_no; ?>Obj = isInternetExplorer ? document.all.hotspot<?php echo $question_no; ?> : document.hotspot<?php echo $question_no; ?>;
      document.questions.q<?php echo $question_no; ?>.value = args;
    }
    if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
      document.write('<script language=\"VBScript\"\>\n');
      document.write('On Error Resume Next\n');
      document.write('Sub hotspot<?php echo $question_no; ?>_FSCommand(ByVal command, ByVal args)\n');
      document.write('	Call hotspot<?php echo $question_no; ?>_DoFSCommand(command, args)\n');
      document.write('End Sub\n');
      document.write('</script\>\n');
    }
  </script>
        
  <div align="center">
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="hotspot<?php echo $question_no; ?>" width="<?php echo ($options[0]['q_media_width'] + 8); ?>" height="<?php echo ($options[0]['q_media_height'] + 8); ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="hotspot_question.swf" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <param name="FlashVars" value="flashWidth=<?php echo ($options[0]['q_media_width'] + 8); ?>&flashHeight=<?php echo ($options[0]['q_media_height'] + 8); ?>&imageName=<?php echo $options[0]['q_media']; ?>&type=<?php echo $options[0]['score_method']; ?>&config=<?php echo $options[0]['correct']; ?>&user_answer=<?php echo $users_answers[$q_id]; ?>" />
    <embed src="hotspot_question.swf" FlashVars="imageName=<?php echo $options[0]['q_media']; ?>&config=<?php echo $options[0]['correct']; ?>&user_answer=<?php echo $users_answers[$q_id]; ?>" quality="high" bgcolor="#ffffff" width="<?php echo ($options[0]['q_media_width'] + 8); ?>" height="<?php echo ($options[0]['q_media_height'] + 8); ?>" swLiveConnect=true id="hotspot<?php echo $question_no; ?>" name="hotspot<?php echo $question_no; ?>" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />
    </object>
    <br /><span style="color: #808080; font-size: 80%">(<img src="../artwork/hotspot_cursor.gif" width="15" height="15" alt="cusor" border="0" /> Single click with the mouse to record your answer. Single click again if you need to change that answer.)</span>
  </div>
        
<?php
        if ($users_answers[$q_id] == '') {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"u\" />\n";
        } else {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"" . $users_answers[$q_id] . "\" />\n";
        }
        break;
      case 'flash':
?>        
               
  <script language="JavaScript">
    var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
    function flash<?php echo $question_no; ?>_DoFSCommand(command, args) {
      var flash<?php echo $question_no; ?>Obj = isInternetExplorer ? document.all.flash<?php echo $question_no; ?> : document.flash<?php echo $question_no; ?>;
      document.questions.q<?php echo $question_no; ?>.value = args;
    }
    if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
      document.write('<script language=\"VBScript\"\>\n');
      document.write('On Error Resume Next\n');
      document.write('Sub flash<?php echo $question_no; ?>_FSCommand(ByVal command, ByVal args)\n');
      document.write('	Call flash<?php echo $question_no; ?>_DoFSCommand(command, args)\n');
      document.write('End Sub\n');
      document.write('</script\>\n');
    }
  </script>
        
  <div align="center">
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo $display_option['q_media_width']; ?>" height="<?php echo $display_option['q_media_height']; ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="./media/<?php echo $display_option['q_media']; ?>" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <?php
      if ($display_option['scenario'] != '') {
        echo '<param name="FlashVars" value="' . $display_option['scenario'] . '">';
      }
      echo '<embed src="./media/' . $display_option['q_media'] . '"';
      if ($display_option['scenario'] != '') {
        echo ' FlashVars="' . $display_option['scenario'] . '"';
      }
      echo ' quality="high" bgcolor="#ffffff" width="' . $display_option['q_media_width'] . '" height="' . $display_option['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />';
    ?>
    </object>
  </div>
      
<?php        
        break;
    }                  // End switch
    $i++;
  }                    // End foreach loop

  if ($options[0]['q_type'] == 'mcq') {
    if ($options[0]['score_method'] == 'vertical') {
      echo '</table>';
    } elseif ($options[0]['score_method'] == 'dropdown') {
      echo '</select></div>';
    }
  } elseif ($options[0]['q_type'] == 'calculation') {
    $score_array = explode(',',$options[0]['score_method']);
    
    echo '<tr><td class="question_no">' . (($question_no + $question_offset) - 1) . '.&nbsp;';
    echo '</td><td>';
    
    $new_leadin = "";
    $search = array('$A','$B','$C','$D','$E','$F','$G','$H');
    $replace = array($A,$B,$C,$D,$E,$F,$G,$H);
    $display_option['leadin'] = str_ireplace($search,$replace,$display_option['leadin']);
    echo "<p>" . $display_option['leadin'] . "</p>\n";
    if (in_array('ERROR',$replace,true)) {
      echo "<p><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" value=\"\" disabled /></p>\n";
    } else {
      if ($score_array[2] == 'Formula') {
        if (isset($user_answers[$current_screen][$q_id]) AND $user_answers[$current_screen][$q_id] == '') {
          echo "<p><input type=\"hidden\" style=\"text-align:right\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" size=\"10\" />";
          output_Math_Editor("q$question_no");
          echo "</p>\n";
        } else {
          if ((isset($tmp_user_answers[0]) and $tmp_user_answers[0] != '') or $screen_pre_submitted == 0) {
            echo "<p><input type=\"hidden\" style=\"text-align:right\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" size=\"10\" value=\"" . $tmp_user_answers[0] . "\" />";
            output_Math_Editor("q$question_no");
            echo "</p>\n";
          } else {
            echo "<p class=\"unanswered\"><input type=\"hidden\" style=\"text-align:right\"  id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" size=\"10\" value=\"\" />";
            output_Math_Editor("q$question_no");
            echo "</p>\n";
          }
        }
      } else {
        echo "<p><input type=\"text\" style=\"text-align:right\" class=\"unanswered\" name=\"q" . $question_no . "\" size=\"10\" value=\"\" />" . $score_array[2] .  "</p>\n";	  
      }
    }
    $answer = "echo \"" . $options[0]['correct'] . "\"";
    $marks = 1;
  } elseif ($options[0]['q_type'] == 'dichotomous' or $options[0]['q_type'] == 'mrq' or $options[0]['q_type'] == 'rank') {
    echo '</table>';
    if ($options[0]['q_type'] == 'mrq') {
      echo "<input type=\"hidden\" id=\"mrq" . $question_no . "_options\" name=\"mrq" . $question_no . "_options\" value=\"$part_id\" />\n";
      if ($options[0]['score_method'] == 'SelectedPositive') {
        echo "<input type=\"hidden\" id=\"mrq" . $question_no . "_correct_no\" name=\"mrq" . $question_no . "_correct_no\" value=\"$marks\" />\n";
      } else {
        echo "<input type=\"hidden\" id=\"mrq" . $question_no . "_correct_no\" name=\"mrq" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
      }
    } elseif ($options[0]['q_type'] == 'rank') {
      echo "<input type=\"hidden\" id=\"rank" . $question_no . "_options\" name=\"rank" . $question_no . "_options\" value=\"$part_id\" />\n";
      if ($options[0]['score_method'] == 'BonusMark') {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"". ($marks-1) . "\" />\n";
      } elseif ($options[0]['score_method'] == 'AllItemsCorrect') {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
      } else {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$marks\" />\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'extmatch') {
    $part_id = 1;
    if ($matching_media[0] != '') {
      echo '<p align="center">' . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0],$question_no) . '</p>';
    }
    echo '<ol type="i">';
    foreach ($matching_scenarios as $single_scenario) {
      echo '<li>';
      if ($single_scenario != '') echo "<div>$single_scenario</div>";
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<p>" . display_media($matching_media[$part_id],$matching_media_width[$part_id],$matching_media_height[$part_id],$question_no . '_' . $part_id) . "</p>\n";
      }
      echo "<br />\n";
      echo "<div class=\"option\">\n";
      $multi_answers = array();
      if(isset($matching_users_answers[$part_id - 1])) $multi_answers = explode('$', $matching_users_answers[$part_id - 1]);
      foreach ($matching_options as $single_option) {
        $tmp_answer_match = false;
        foreach ($multi_answers as $separate_tmp_answer) {
          if ($separate_tmp_answer == $single_option) {
            $tmp_answer_match = true;
          }
        }
        echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;&nbsp;$single_option<br />\n";
      }
      echo '</div><br /></li>';
      $part_id++;
    }
    $matching_answers = array();
    $matching_answers = explode('|', $options[0]['correct']);
    $marks += count($matching_answers);
    echo '</ol>';
  } elseif ($options[0]['q_type'] == 'sct') {
    echo "</table>\n</td></tr>\n";
  }
  
  // Display possible marks for question (if not Evaluation Questionnaire)
  if ($options[0]['q_type'] != 'hotspot' and $options[0]['q_type'] != 'likert') echo '</blockquote>';
  if ($display_option['paper_type'] < 3) {
    if ($marks == 1) {
      echo "<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;Marks)</div>\n<br />\n";
    } elseif ($marks > 1) {
      if ($options[0]['q_type'] == 'dichotomous' and ($options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain' or $options[0]['score_method'] == 'YN_Negative' or $options[0]['score_method'] == 'YN_NegativeAbstain')) {
        echo "<div class=\"no_marks\">($marks, negative marking)</div>\n<br />\n";
      } elseif ($options[0]['q_type'] == 'mrq') {
        if ($options[0]['score_method'] == 'SelectedPositive') {
          echo "<div class=\"no_marks\">($marks)</div>\n<br />\n";
        } else {
          echo "<div class=\"no_marks\">($marks, all options items marked with negative marking)</div>\n<br />\n";
        }
      } elseif ($options[0]['q_type'] == 'rank') {
        if ($options[0]['score_method'] == 'BonusMark') {
          echo "<div class=\"no_marks\">(" . ($marks - 1) . "&nbsp;marks for correct options, plus 1 bonus mark for fully correct order)</div>\n";
        } elseif ($display_option['score_method'] == 'AllItemsCorrect') {
          echo "<div class=\"no_marks\">($marks, mark)</div>\n<br />\n";
        } elseif ($options[0]['score_method'] == 'OrderNeighbours') {
          echo "<div class=\"no_marks\">($marks, half marks for neighbours)</div>\n<br />\n";
        } else {
          echo "<div class=\"no_marks\">($marks, exact order)</div>\n<br />\n";
        }
      } else {
        echo "<div class=\"no_marks\">($marks)</div>\n<br />\n";
      }
    }
  }
}                      // End function 'display_options'.


function old_display_options($options, $q_id, $current_theme) {

  global $question_no, $part_id, $labelcolor, $old_likert_label;

  $option_no = count($options);
  $part_id = 0;
  $marks = 0;

  if ($options[0]['q_type'] == 'extmatch') {
    echo ("<div class=\"option\"><select name=\"q" . $question_no . "\" multiple size=\"10\">\n");
  } elseif ($options[0]["q_type"] == "dichotomous") {
    echo ("<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n");
    if (($options[0]["score_method"] == "TF_NegativeAbstain") or ($options[0]["score_method"] == "TF_PostiveAbstain")) {
      echo ("<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">True</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">False</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td>&nbsp;</td></tr>\n");
    } elseif (($options[0]["score_method"] == "TF_Negative") or ($options[0]["score_method"] == "TF_Positive")) {
      echo ("<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">True</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">False</td></tr>\n");
    } elseif (($options[0]["score_method"] == "YN_NegativeAbstain") or ($options[0]["score_method"] == "YN_PostiveAbstain")) {
      echo ("<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">No</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td>&nbsp;</td></tr>\n");
    } else {
      echo ("<tr><td align=\"center\" width=\"40\" style=\"color: $labelcolor; font-size: 90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">No</td></tr>\n");
    }
  } elseif ($options[0]['q_type'] == 'mcq') {
    echo ("<blockquote>\n");
    if ($options[0]["score_method"] == 'vertical') {
      echo ("<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n");
    }
    if ($options[0]['score_method'] == 'dropdown') {
      echo ("<div class=\"option\"><select name=\"q" . $question_no . "\" size=\"1\">\n");
      echo ("<option value=\"\"></option>\n");
    }
  } elseif ($options[0]['q_type'] == 'mrq') {
    echo ("<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n");
  } elseif ($options[0]['q_type'] == 'rank') {
    echo ("<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n");
    for ($i=0; $i<$option_no; $i++) {
      if ($options[$i]["correct"] < 9990) {
        $total_rank_no++;
      } elseif ($display_option['score_method'] == 'StrictOrder') {
        $total_rank_no++;
      }
    }
               
    echo '<tr>';
    for ($i=1; $i<=$total_rank_no; $i++) {
      echo ("<td style=\"color: $labelcolor; font-size: 90%; text-align: center\">$i");
      if ($i == 1) {
        echo 'st';
      } elseif ($i == 2) {
        echo 'nd';
      } elseif ($i == 3) {
        echo 'rd';
      } else {
        echo 'th';
      }
      echo '</td>';
    }
    echo "<td>&nbsp;</td></tr>";
  } elseif ($options[0]['q_type'] == 'likert') {
    $str = $options[0]['score_method'];
    $likert_display = explode('/',$str);
    echo "<blockquote>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
    if ($old_likert_label != $str) {
      if ($likert_display[2] == 3) {
        echo "<tr><td style=\"color: $labelcolor; font-size: 90%\">$likert_display[0]</td><td>&nbsp;</td><td align=\"right\" style=\"color: $labelcolor; font-size: 90%\">$likert_display[1]</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr>";
        if ($likert_display[3] == 'true') {
          echo "<td style=\"color: $labelcolor; font-size: 90%\">N/A</td>";
        }
        echo "<td colspan=\"2\" style=\"color: $labelcolor; font-size: 90%\">$likert_display[0]</td>";
        $temp_end = $likert_display[2] - 4;
        for ($i=1; $i<=$temp_end; $i++) {
          echo "<td>&nbsp;</td>";
        }
        echo "<td align=\"right\" colspan=\"2\" style=\"color: $labelcolor; font-size: 90%\">$likert_display[1]</td><td>&nbsp;</td></tr>\n";
      }
    }
    $old_likert_label = $str;
  }
  $i = 0;
  
  foreach ($options as $display_option) {
    $part_id++;
    switch ($options[0]['q_type']) {
      case 'dichotomous':
        if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain') or ($options[0]["score_method"] == "YN_NegativeAbstain") or ($options[0]["score_method"] == "YN_PostiveAbstain")) {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"a\" /></td><td>" . $display_option["option_text"] . "</td></tr>\n";
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"a\" /></td><td>" . $display_option["option_text"] . "<br />" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"a\" /></td><td>" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
          }
        } else {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option["option_text"] . "</td></tr>\n";
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option["option_text"] . "<br />" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            echo "<tr><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td align=\"center\"><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
          }
        }
        $marks++;
        break;
      case 'likert':
        echo ("<tr>");
        if ($likert_display[3] == 'true') {
          echo ("<td style=\"background-color: #FFC0C0\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"n/a\" /></td>");
        }
        for ($i=1; $i<=$likert_display[2]; $i++) {
          echo ("<td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"$i\" /></td>");
        }
        echo ("<td>" . $display_option['leadin'] . "</td></tr>\n");
        $marks+=2;
        break;
      case 'mcq':
        if ($options[0]['score_method'] == 'vertical') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option["option_text"] . "</td></tr>\n";
          } elseif ($display_option['option_text'] != '' and $display_option["o_media"] != '') {  // Text and media
            echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option["option_text"] . "<br />" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
          } elseif ($display_option['option_text'] == '' and $display_option["o_media"] != '') {  // No Text and media
            echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "</td></tr>\n";
          }
        } elseif ($options[0]['score_method'] == 'horizontal') {
          if ($display_option['option_text'] != '' and $display_option["o_media"] == '') {        // Text and No media
            echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . $display_option["option_text"] . "&nbsp;&nbsp;";
          } elseif ($display_option['option_text'] != '' and $display_option["o_media"] != '') {  // Text and media
            echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . $display_option["option_text"] . "<br />" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "&nbsp;&nbsp;";
          } elseif ($display_option['option_text'] == '' and $display_option["o_media"] != '') {  // No Text and media
            echo "<img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />&nbsp;" . display_media($display_option["o_media"],$display_option["o_media_width"],$display_option["o_media_height"],"q" . $question_no . "_" . $part_id) . "&nbsp;&nbsp;";
          }
        } else {
          echo "<option value=\"" . $part_id . "\">" . $display_option["option_text"] . "</option>\n";
        }
        if ($part_id == $display_option['correct']) {
          $marks = $display_option['marks'];
        }
        break;
      case 'mrq':
        if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No image
          echo "<tr><td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td><td>" . $display_option["option_text"] . "</td></tr>\n";
        } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and image
          echo "<div><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" />" . $display_option["option_text"] . "<br /><img src=\"./media/" . $display_option["o_media"] . "\" width=\"" . $display_option["o_media_width"] . "\" height=\"" . $display_option["o_media_height"] . "\" alt=\"image\" border=\"1\"/></div>\n";
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and image
          echo "<div><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /><img src=\"./media/" . $display_option["o_media"] . "\" width=\"" . $display_option["o_media_width"] . "\" height=\"" . $display_option["o_media_height"] . "\" alt=\"image\" border=\"1\"/></div>\n";
        }
        if ($display_option['score_method'] == 'SelectedPositive') {
          if ($display_option["correct"] == 'y') {
            $marks++;  // 1 mark for correct options only
          }
        } else {
          $marks++;  // 1 mark for each and every item
        }
        break;
      case 'rank':
        $total_rank_no = 0;
        for ($i=0; $i<$option_no; $i++) {
          if ($options[$i]['correct'] < 9990) {
            $total_rank_no++;
          } elseif ($display_option['score_method'] == 'StrictOrder') {
            $total_rank_no++;
          }
        }
           
        if ($part_id == 1 and $display_option["score_method"] == 'BonusMark') {
          $marks++;  // Add one mark at start in case all options are answered correctly
        }
        
        echo ("<tr>");
	for ($i=1; $i<=$total_rank_no; $i++) {
          echo ("<td><img src=\"../artwork/print_checkbox.gif\" width=\"25\" height=\"24\" alt=\"checkbox\" /></td>");
        }
	echo ("<td>" . $display_option["option_text"] . "</td>\n</tr>\n");
	if ($display_option['correct'] < 9990) {
          $marks++;
        } elseif ($display_option['score_method'] == 'StrictOrder') {
          $marks++;
        }
	break;
      case 'blank':
        $blank_details = array();
        $blank_details = explode("<blank>",$display_option["option_text"]);
        $no_blanks = count($blank_details);
        echo ("<p>" . $blank_details[0]);
        $blank_count = 1;
        while ($blank_count < $no_blanks) {
          $answer_options = array();
          $answer_options = explode("</blank>",$blank_details[$blank_count]);
          $answer_list = array();
          $answer_list = explode(",",$answer_options[0]);
          if ($display_option["score_method"] == 'dropdown') {
            echo ("<select name=\"q" . $question_no . "_" . $part_id . "\">\n");
            echo ("<option value=\"n/a\"></option>\n");
            $no_answers = count($answer_list);
          
            // Shuffle the answers up.
            for ($i=1; $i<26; $i++) {
              $pos1 = rand(0,$no_answers-1);
              $pos2 = rand(0,$no_answers-1);
              if ($pos1 != $pos2) {
                $postemp = $answer_list[$pos2];
                $answer_list[$pos2] = $answer_list[$pos1];
                $answer_list[$pos1] = $postemp;
              }
            }
          
            for ($i=1; $i<=$no_answers; $i++) {
              echo ("<option value=\"" . $answer_list[$i-1] . "\">" . $answer_list[$i-1]. "</option>\n");
            }
            echo ("</select>");
          } else {
            echo ("<input type=\"text\" name=\"q" . $question_no . "_" . $part_id . "\" size=\"" . strlen($answer_list[0]) . "\" />");
          }
          echo ($answer_options[1]); // Bit after the closing </blank> tag.
          $blank_count++;
          $part_id++;
        }
        echo ("</p>\n");
        $marks = $no_blanks - 1;
        break;
      case 'textbox':
        $textbox_size = explode('x',$display_option["score_method"]);
        if ($textbox_size[1] == 1) {
          echo ("<p><input type=\"text\" name=\"q" . $question_no . "\" size=\"" . $textbox_size[0] . "\" /></p>\n");
        } else {
          echo ("<p><textarea name=\"q" . $question_no . "\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\" /></textarea></p>\n");
        }
        break;
    }                  // End switch
    $i++;
  }                    // End foreach loop

  if ($options[0]['q_type'] == 'extmatch') {
    echo ("</select></div></li>\n");
  } elseif ($options[0]["q_type"] == "mcq") {
    if ($options[0]["score_method"] == "vertical") {
      echo ("</table>\n");
    } elseif ($options[0]["score_method"] == "dropdown") {
      echo ("</select></div>\n");
    }
  } elseif ($options[0]["q_type"] == "dichotomous" or $options[0]["q_type"] == "mrq" or $options[0]["q_type"] == "rank" or $options[0]["q_type"] == "likert") {
    echo ("</table>\n");
    if ($options[0]["q_type"] == "mrq") {
      echo ("<input type=\"hidden\" id=\"mrq" . $question_no . "_options\" name=\"mrq" . $question_no . "_options\" value=\"$part_id\" />\n");
      echo ("<input type=\"hidden\" id=\"mrq" . $question_no . "_correct_no\" name=\"mrq" . $question_no . "_correct_no\" value=\"$marks\" />\n");
    } elseif ($options[0]["q_type"] == "rank") {
      echo ("<input type=\"hidden\" id=\"rank" . $question_no . "_options\" name=\"rank" . $question_no . "_options\" value=\"$part_id\" />\n");
      if ($options[0]["score_method"] == "BonusMark") {
        echo ("<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"". ($marks-1) . "\" />\n");
      } else {
        echo ("<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$marks\" />\n");
      }
    }
  }
  
  // Display possible marks for question (if not Evaluation Questionnaire)
  echo ("</blockquote>\n");
  if ($display_option['paper_type'] < 3) {
    if ($marks == 1) {
      echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;mark)</div>\n");
    } elseif ($marks > 1) {
      if (($options[0]["q_type"] == "tf" or $options[0]["q_type"] == "yes_no") and ($options[0]["score_method"] == "Negative" or $options[0]["score_method"] == "NegativeAbstain")) {
        echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;marks, negative marking)</div>\n");
      } elseif ($options[0]["q_type"] == "mrq") {
        if ($options[0]["score_method"] == "SelectedPositive") {
          echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;marks)</div>\n");
        } else {
          echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;marks, all options items marked with negative marking)</div>\n");
        }
      } elseif ($options[0]["q_type"] == "rank") {
        if ($options[0]["score_method"] == "BonusMark") {
          echo ("<div style=\"color: #808080; font-size: 80%\">(" . ($marks - 1) . "&nbsp;marks for correct options, plus 1 bonus mark for fully correct order)</div>\n");
        } elseif ($options[0]["score_method"] == "OrderNeighbours") {
          echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;marks, half marks for neighbours)</div>\n");
        } else {
          echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;marks, exact order)</div>\n");
        }
      } else {
        echo ("<div style=\"color: #808080; font-size: 80%\">($marks&nbsp;marks)</div>\n");
      }
    }
  }
}                      // End function 'display_options'.
?>