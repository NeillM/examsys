<?php
function ss_get_marks($mysqli, $paper_id, $exclude, &$marks_array) {
  $partID = 0;
  $old_q_id = 0;
  $stems = 0;
  $correct_no = 0;
  $old_score_method = '';
  $question_data = $mysqli->query("SELECT q_type, q_id, marks, correct, score_method, option_text FROM (papers, questions, options) WHERE papers.paper=".$paper_id." AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type != 'info' ORDER BY q_id, o_id, id_num");
  while ($row = $question_data->fetch_assoc()) {
    if ($old_q_id != $row['q_id'] and $old_q_id != 0) {
      if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
        if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id], '1') !== false) {
          $marks_array[$old_q_id][$partID] = 0;
        } else {
          $marks_array[$old_q_id][$partID] = 1;
        }
      }
      $stems = 0;
      $correct_no = 0;
      $temp_marks = 0;
      $partID = 0;
    }

    $old_q_id = $row['q_id'];
    if (!isset($exclude[$old_q_id])) { // If no record exists set to zero (i.e. included)
      $exclude[$old_q_id] = '00000000000000000000000000000';
    }

    switch ($row['q_type']) {
      case 'textbox':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $row['marks'];
        }
        break;
      case 'dichotomous':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = 1;
        } else {
          $marks_array[$old_q_id][$partID] = 0;
        }
        $partID++;
        break;
      case 'calculation':
      case 'timedate':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = 1;
        }
        break;
      case 'hotspot':
        $tmp_first_split = explode('|', $row['correct']);
        for ($i = 0; $i < count($tmp_first_split); $i++) {
          if (substr($exclude[$old_q_id], $i, 1) == '0') {
            $marks_array[$old_q_id][$i] = 1;
          } else {
            $marks_array[$old_q_id][$i] = 0;
          }
        }
        break;
      case 'blank':
        $no_blanks = substr_count($row['option_text'], '[blank');
        for ($i = 0; $i < $no_blanks; $i++) {
          if (substr($exclude[$old_q_id], $i, 1) == '0') {
            $marks_array[$old_q_id][$i] = 1;
          }
        }
        break;
      case 'matrix':
        $tmp_part = 0;
        $matching_correct = explode('|', $row['correct']);
        for ($part_id = 0; $part_id < count($matching_correct); $part_id++) {
          if (trim($matching_correct[$part_id]) != '') {
            if (substr($exclude[$old_q_id], $part_id, 1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = 1;
            }
            $tmp_part++;
          }
        }
        break;
      case 'extmatch':
        $tmp_part = 0;
        $matching_correct = explode('|', $row['correct']);
        for ($part_id = 0; $part_id < count($matching_correct); $part_id++) {
          $sub_array = explode('$', $matching_correct[$part_id]);
          for ($sub_part_id = 0; $sub_part_id < count($sub_array); $sub_part_id++) {
            if (substr($exclude[$old_q_id], $tmp_part, 1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = 1;
            } else {
              $marks_array[$old_q_id][$tmp_part] = 0;
            }
            $tmp_part++;
          }
        }
        break;
      case 'mcq':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $row['marks'];
          $partID++;
        }
        break;
      case 'mrq':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          if ($row['score_method'] == 'SelectedPositive') {
            if ($row['correct'] == 'y') {
              $marks_array[$old_q_id][$partID] = 1;
            }
            $partID++;
          } elseif ($row['score_method'] == 'AllItemsCorrect') {
            $marks_array[$old_q_id][0] = 1;
          } else {
            $marks_array[$old_q_id][$partID] = 1;
            $partID++;
          }
        }
        break;
      case 'rank':
        if ($row['correct'] < 9990) {
          if (substr($exclude[$old_q_id], $partID, 1) == '0') {
            $marks_array[$old_q_id][$partID] = 1;
          } else {
            $marks_array[$old_q_id][$partID] = 0;
          }
          $partID++;
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $row['correct']);
        $tmp_second_split = explode('$', $tmp_first_split[8]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
            if (substr($exclude[$old_q_id], $partID, 1) == '0') {
              $marks_array[$old_q_id][$partID] = 1;
            } else {
              $marks_array[$old_q_id][$partID] = 0;
            }
            $partID++;
          }
        }
        break;
    }

    $old_q_type = $row['q_type'];
    $old_score_method = $row['score_method'];
    $old_correct = $row['correct'];
    $old_option_text = $row['option_text'];
    $temp_marks = $row['marks'];
    if ($row['q_type'] == 'mrq') {
      if ($row['correct'] == 'y') $correct_no++;
    }
    if ($row['q_type'] == 'rank') {
      if ($row['correct'] < 9990) $correct_no++;
    }
    $stems++;
  }

  $question_data->close();

  if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
    if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id], '1') !== false) {
      $marks_array[$old_q_id][$partID] = 0;
    } else {
      $marks_array[$old_q_id][$partID] = 1;
    }
  }
}
?>