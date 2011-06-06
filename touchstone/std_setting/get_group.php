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

  function rnd_mark_from_type($old_question_type) {
    global $temp_marks, $total_marks, $total_random_mark, $stems, $correct_no, $old_option_text, $old_correct, $old_score_method, $old_score_method, $old_q_media_width, $old_q_media_height;
    switch ($old_question_type) {
      case 'calculation':
        $total_marks += 1;
        break;
      case 'dichotomous':
        if ($old_score_method == 'TF_Positive' or $old_score_method == 'TF_PositiveAbstain' or $old_score_method == 'YN_Positive' or $old_score_method == 'YN_PositiveAbstain') {
          $total_random_mark += 0.5 * $stems;
        }
        $total_marks += $stems;
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $old_correct);
        $tmp_second_split = explode('\$', $tmp_first_split[8]);
        $label_count = 0;
        $placeholders = 0;
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 150) $placeholders++;
        }
        $total_marks += $placeholders;
        $total_random_mark += ($placeholders / $label_count);
        break;
      case 'flash':
      case 'textbox':
        $total_marks += $temp_marks;
        break;
      case 'mcq':
        $total_random_mark += $temp_marks / $stems;
        $total_marks += $temp_marks;
        break;
      case 'mrq':
        if ($old_score_method == 'SelectedPositive') {
          $total_random_mark += ($correct_no * $correct_no) / $stems;
          $total_marks += $correct_no;
        } elseif ($old_score_method == 'AllItemsCorrect') {
          $figure = 1;
          for ($i=$stems; $i>($stems-$correct_no); $i--) {
            $figure *= (1/$i);
          }
          $total_random_mark += $figure;
          $total_marks += 1;
        } else {
          $total_random_mark += ($stems / 2);
          $total_marks += $stems;
        }
        break;
      case 'matrix':
        $correct_no = 0;
        $matching_correct = explode('|', $old_correct);
        for ($part_id=0; $part_id<10; $part_id++) {
          if ($matching_correct[$part_id] != '') $correct_no++;
        }
        $total_random_mark += (1 / $stems) * $correct_no;
        $total_marks += $correct_no;
        break;
      case 'extmatch':
        $correct_array = explode('|',$old_correct);
        foreach ($correct_array as $individual_correct) {
          if (trim($individual_correct) != '') {
            $correct_no = substr_count($individual_correct,'$') + 1;
            $total_random_mark += ($correct_no * $correct_no) / $stems;
            $total_marks += $correct_no;
          }
        }
        break;
      case 'rank':
        $correct++;
        if ($old_score_method == 'StrictOrder') {
          if ($correct_no == $stems) {
            $total_random_mark += 1;
          } else {
            $na = $stems - $correct_no;
            $total_random_mark += ($correct_no / $stems) + (($stems - $correct_no) / ($stems / $na));
          }
          $total_marks += $stems;
        } elseif ($old_score_method == 'AllItemsCorrect') {
          $total_random_mark += 1 / factorial($stems);
          $total_marks += 1;
        } elseif ($old_score_method == 'OrderNeighbours') {
          $total_random_mark += 1 + (($stems - 2) / $stems) + (1/$stems);
          $total_marks += $correct_no;
        } elseif ($old_score_method == 'BonusMark') {
          $total_random_mark += (($correct_no * $correct_no) / $stems) + (factorial($stems-$correct_no)/factorial($stems));
          $total_marks += $correct_no + 1;
        }
        break;
      case 'blank':
        $blank_details = explode("\[blank",$old_option_text);
        $array_size = count($blank_details);
        $blank_count = 1;
        while ($blank_count < $array_size) {
          $blank_details[$blank_count] = '[blank' . $blank_details[$blank_count];
          $closing_blank_pos = strpos($blank_details[$blank_count],'[/blank]');
          $tmp_first_part = substr($blank_details[$blank_count],0,$closing_blank_pos);
          $choice_no = substr_count($tmp_first_part,',') + 1;
          $results = array();
          if (ereg("mark=\"([0-9]{1,3})\"",$blank_details[$blank_count],$results)) {
            $total_marks += $results[1];
            if ($old_score_method == 'dropdown') $total_random_mark += (1 / $choice_no) * $results[1];
          } else {
            $total_marks++;
            if ($old_score_method == 'dropdown') $total_random_mark += 1 / $choice_no;
          }
          $blank_count++;
        }
        break;
      case 'hotspot':
        $hotspot_image_area = $old_q_media_width * $old_q_media_height;
        $total_marks += $temp_marks;
        $coords_array = explode(';',$old_correct);
        $master_area_total = 0;
        for ($area_no = 0; $area_no < (count($coords_array)-1); $area_no += 3) {
          $individual_coords = array();
          $individual_coords = explode(',',$coords_array[$area_no+1]);
          if ($coords_array[$area_no] == 'polygon') {
            $individual_coords[] = $individual_coords[0];
            $individual_coords[] = $individual_coords[1];
            $area_total = 0;
            for ($i = 0; $i < count($individual_coords); $i += 2) {
              $first_part = hexdec($individual_coords[$i]) * hexdec($individual_coords[$i+3]);
              $second_part = hexdec($individual_coords[$i+2]) * hexdec($individual_coords[$i+1]);
              $area_total += $first_part - $second_part;
            }
            $master_area_total += abs($area_total) / 2;
          } elseif ($coords_array[$area_no] == 'rectangle') {
            $ellipse_x_radius = hexdec($individual_coords[2]) - hexdec($individual_coords[0]);
            $ellipse_y_radius = hexdec($individual_coords[3]) - hexdec($individual_coords[1]);
            $master_area_total += $ellipse_x_radius * $ellipse_y_radius;
          } elseif ($coords_array[$area_no] == 'ellipse') {
            $ellipse_x_radius = (hexdec($individual_coords[2]) - hexdec($individual_coords[0])) / 2;
            $ellipse_y_radius = (hexdec($individual_coords[3]) - hexdec($individual_coords[1])) / 2;
            $master_area_total += $ellipse_x_radius * $ellipse_y_radius * pi();
          }
        }
        $total_random_mark += ($master_area_total/$hotspot_image_area) * $temp_marks;
        break;
    }
  }

  require '../include/staff_auth.inc';
  $paperID = $_GET['paperID'];
  $marks_array = array();
  
  // Calculate marks for the current paper.
  $partID = 0;
  $total_marks = 0;
  $old_q_id = 0;
  $stems = 0;
  $old_score_method = '';
  $question_data = $mysqli->query("SELECT q_type, q_id, marks, correct, score_method, q_media_height, q_media_width, option_text FROM (papers, questions, options) WHERE papers.paper=$paperID AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY o_id");
  while ($row = $question_data->fetch_assoc()) {
    if ($old_q_id != $row['q_id'] and $old_q_id != 0) {
      if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
        $marks_array[$old_q_id][$partID] = 1;
      }
      rnd_mark_from_type($old_q_type);
      $stems = 0;
      $correct_no = 0;
      $temp_marks = 0;
      $partID = 0;
    }
    
    $old_q_id = $row['q_id'];
    switch ($row['q_type']) {
      case 'dichotomous':
        $marks_array[$old_q_id][$partID] = 1;
        $partID++;
        break;
      case 'mcq':
        $marks_array[$old_q_id][$partID] = $row['marks'];
        $partID++;
        break;
      case 'mrq':
        if ($row['score_method'] == 'SelectedPositive') {
          if ($row['correct'] == 'y') {
            $marks_array[$old_q_id][$partID] = 1;
            $partID++;
          }
        } elseif ($row['score_method'] == 'AllItemsCorrect') {
          $marks_array[$old_q_id][0] = 1;
        } else {
          $marks_array[$old_q_id][$partID] = 1;
          $partID++;
        }
        break;
      case 'rank':
        if ($row['correct'] < 9990) {
          $marks_array[$old_q_id][$partID] = 1;
          $partID++;
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $row['correct']);
        $tmp_second_split = explode('\$', $tmp_first_split[8]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 20) {
            $marks_array[$old_q_id][$partID] = 1;
            $partID++;
          }
        }
        break;
    }
    
    $old_q_type = $row['q_type'];
    $old_score_method = $row['score_method'];
    $old_correct = $row['correct'];
    $old_q_media_width = $row['q_media_width'];
    $old_q_media_height = $row['q_media_height'];
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
    $marks_array[$old_q_id][$partID] = 1;
  }
  rnd_mark_from_type($old_q_type);
?>
<html>
<head>

<title>TouchStone: List Settings</title>

<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
td {font-size:80%; color:black}
a {color:black}
a:hover {color:white; background-color:#000080}
.heading {background-color:#EBEADB; color:black}
</style>
</head>

<body>
<form action="group_set_angoff.php" method="post">
<?php
  echo "<table onclick=\"reviewOff()\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td class=\"heading\"><div style=\"font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:40px; text-indent:-40px\"><a onmouseover=\"move_in('image1')\" onmouseout=\"move_out('image1')\" href=\"../paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "\" target=\"_top\"><img name=\"image1\" src=\"../artwork/up_folder_icon_off.gif\" style=\"vertical-align: middle\" width=\"32\" height=\"38\" alt=\"Up\" border=\"0\" /></a>&nbsp;Select Reviews to Include</div></td></tr>\n";
  echo "</table>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td class=\"heading\" style=\"width:18px\">&nbsp;</td><td class=\"heading\" width=\"150\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Standard Setter&nbsp;</td><td class=\"heading\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Date&nbsp;</td><td class=\"heading\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Cut Score</td><td class=\"heading\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Method</td><td width=\"25%\" class=\"heading\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp</td></tr>\n";
  echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"6\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";

  $old_date = '';
  $old_title = '';
  $old_initials = '';
  $old_surname = '';
  $old_questionID = 0;
  $question_no = 0;
  $std_total = 0;
  $review_no = 0;
  $ebel_percents = array();
  $ebel_marks = array();
  
  $results = $mysqli->query("SELECT questionID, group_review, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, rating, standards_setting.setterID, method, title, initials, surname FROM standards_setting, users WHERE standards_setting.setterID=users.id AND paperID=$paperID ORDER BY standards_setting.std_set DESC, standards_setting.setterID, standards_setting.id");
  if ($results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
      if ($old_questionID != $row['questionID']) {
        $partID = 0;
        $questionID = $row['questionID'];
        $rating_array = explode(',',$row['rating']);
        foreach ($rating_array as $individual_rating) {
          if (isset($marks_array[$questionID][$partID])) {
            if (isset($ebel_marks[$individual_rating])) {
              $ebel_marks[$individual_rating] += $marks_array[$questionID][$partID];
            } else {
              $ebel_marks[$individual_rating] = $marks_array[$questionID][$partID];
            }
          }
          $partID++;
        }
      }
      if ($old_date != $row['std_set'] and $old_date != '') {
        $review_no++;
        if ($old_method == 'Modified Angoff') {
          $cut_score = round($std_total/$question_no);
        }
        if ($old_group_review == 'No') {
          echo "<tr><td align=\"center\"><input type=\"checkbox\" name=\"member$review_no\" value=\"$old_setterID,$old_std_set\" checked /></td><td>&nbsp;$old_title $old_initials $old_surname</td><td>&nbsp;$old_display_date</td><td style=\"text-align:right\">" . round($cut_score) . "%&nbsp;</td><td>&nbsp;$old_method</td><td></td></tr>\n";
        }
        $question_no = 0;
        $std_total = 0;
      }
      if ($row['rating'] != '') {
        $q_sections = explode(',',$row['rating']);
        foreach ($q_sections as $part) {
          $std_total += $part;
          $question_no++;
        }
      }
      $old_date = $row['std_set'];
      $old_display_date = $row['display_date'];
      $old_method = $row['method'];
      $old_title = $row['title'];
      $old_initials = $row['initials'];
      $old_surname = $row['surname'];
      $old_setterID = $row['setterID'];
      $old_std_set = $row['std_set'];
      $old_group_review = $row['group_review'];
      $old_questionID = $row['questionID'];
    }
    $review_no++;
    if ($old_method == 'Modified Angoff') {
      $cut_score = round($std_total/$question_no);
    }
    if ($old_group_review == 'No') {
      echo "<tr><td align=\"center\"><input type=\"checkbox\" name=\"member$review_no\" value=\"$old_setterID,$old_std_set\" checked /></td><td>&nbsp;$old_title $old_initials $old_surname</td><td>&nbsp;$old_display_date</td><td style=\"text-align:right\">" . round($cut_score) . "%&nbsp;</td><td>&nbsp;$old_method</td><td></td></tr>\n";
    }
  }
  $results->close();
  $mysqli->close();
  echo "<table>\n";
?>
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<br /><p><input type="submit" name="submit" style="width:100px" value="Review" /></p>
</form>
</body>
</html>
