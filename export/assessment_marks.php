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
  
  require '../include/staff_auth.inc';
  require '../include/class_totals.inc';
  
  header('Content-type: application/octet-stream');
  header("Content-Disposition: attachment; filename=new_" . str_replace(' ', '_', $paper) . ".csv");
  $row_written = 0;
  foreach ($user_results as $individual) {
    $tmp_user_ID = $individual['username'];
    // Write out the headings.
    if ($row_written == 0) {
      // Only output personal data if assessment, do not show if survey.
      if ($paper_type < 3) {
        echo '"' . $string['gender'] . '","' . $string['title'] . '","' . $string['surname'] . '","' . $string['firstnames'] . '","' . $string['studentid'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
      } else {
        echo '"' . $string['gender'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
      }
      $q_no = 1;
      foreach ($paper_buffer as $q_id => $question) {
        //$q_id = $question['id'];
        if ($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') {
          $sub_parts = 0;
          $paper_answers = explode("|",$question['correct'][0]);
          if (array_key_exists($q_id,$excluded)) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '0000000000000000000000000000000000000000';
          }
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a],'$');
          
            if ($paper_answers[$a] != '' and substr($tmp_exclude,$a+$sub_parts,1) == '0') echo ',Q' . $q_no . '.' . ($a+1);
          }
        } elseif ($question['q_type'] == 'dichotomous' or $question['q_type'] == 'labelling' or $question['q_type'] == 'blank') {
          if (array_key_exists($q_id,$excluded)) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '0000000000000000000000000000000000000000';
          }
          for ($a=0; $a<count($question['correct']); $a++) {
            if (substr($tmp_exclude,$a,1) == '0') echo ',Q' . $q_no . '.' . ($a+1);
          }
        } else {
          if (!array_key_exists($q_id,$excluded)) echo ',Q' . $q_no;
        }
        $q_no++;
      }
      echo "\n";
    }
    // Write out the raw data.
    if ($individual['visible'] == 1) {
      if ($paper_type < 3) {
        echo '"' . $individual['gender'] . '","' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' .$individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
      } else {
        echo '"' . $individual['gender'] . '","' . $individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
      }
      foreach ($individual['mark_array'] as $individual_mark) echo ",$individual_mark";
      echo "\n";
    }
    $row_written++;
  }
  $mysqli->close();
?>