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
  
  // Capture the paper makeup.
  $paper_buffer = array();
  $question_no = 0;

  $old_q_id = 0;
  $option_no = 0;
  $query_string = "SELECT paper_title, q_id, q_type, screen, id_num, score_method FROM (properties, papers, questions, options) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=" . $_GET['paperID'] . " AND q_type!='info' AND questions.q_id=options.o_id ORDER BY screen, display_pos, id_num";
  $paper_query = $mysqli->query($query_string,$link_id);
  while ($row = $paper_query->fetch_assoc()) {
    $paper = $row['paper_title'];
    if ($old_q_id != $row['q_id']) {
      if ($old_q_id > 0) {
        $paper_buffer[$question_no]['ID'] = $old_q_id;
        $paper_buffer[$question_no]['type'] = $old_q_type;
        $paper_buffer[$question_no]['screen'] = $old_screen;
        $paper_buffer[$question_no]['option_no'] = $option_no;
        $paper_buffer[$question_no]['score_method'] = $old_score_method;
        $question_no++;
        $option_no = 0;
      }
    }
    $old_q_id = $row['q_id'];
    $old_q_type = $row['q_type'];
    $old_screen = $row['screen'];
    $old_score_method = $row['score_method'];
    $option_no++;
  }
  $paper_buffer[$question_no]['ID'] = $old_q_id;
  $paper_buffer[$question_no]['type'] = $old_q_type;
  $paper_buffer[$question_no]['screen'] = $old_screen;
  $paper_buffer[$question_no]['option_no'] = $option_no;
  $paper_buffer[$question_no]['score_method'] = $old_score_method;
  $question_no++;


  header('Content-type: application/octet-stream');
  header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".csv");

  $user_no = 0;
  $paper_string = $mysqli->query("SELECT COUNT(question) AS question_no FROM (papers, questions) WHERE papers.question=questions.q_id AND q_type!='info' AND paper=" . $_GET['paperID'],$link_id);
  while ($row = $paper_string->fetch_assoc()) {
    $number_of_questions = $row['question_no'];
  }
  $exclude = '';
  if ($_GET['complete'] == 1) {
    $paper_string = $mysqli->query("SELECT userID, COUNT(id) AS answer_no FROM log3 WHERE q_paper=" . $_GET['paperID'] . " AND started>=" . $_GET['startdate'] . " AND started<=" . $_GET['enddate'] . " GROUP BY username",$link_id);
    while ($row = $paper_string->fetch_assoc()) {
      if ($row['answer_no'] < $number_of_questions or $row['answer_no'] > $number_of_questions) {
        $exclude .= ' AND log.userID != "' . $row['userID'] . '"';
      }
    }
  }

  $log_array = array();
  $hits = 0;
  if($_GET['repdegree'] == 'Staff') {
    $log_query = $mysqli->query("SELECT DISTINCT sid.student_id, username, title, surname, initials, grade, gender, log3.year, started, log3.q_id, user_answer, q_type, screen FROM (log3, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log3.q_id=questions.q_id AND q_paper=" . $_GET['paperID'] . " AND users.id=log3.userID AND users.roles LIKE 'Staff%' AND started>=" . $_GET['startdate'] . " AND started<=" . $_GET['enddate'],$link_id);
  } else {
    $log_query = $mysqli->query("SELECT DISTINCT sid.student_id, username, title, surname, initials, grade, gender, log3.year, started, log3.q_id, user_answer, q_type, screen FROM (log3, questions, users) LEFT JOIN sid ON users.id=sid.userID WHERE log3.q_id=questions.q_id AND q_paper=" . $_GET['paperID'] . " AND log3.year LIKE '" . $_GET['repyear'] . "' AND users.id=log3.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE '" . $_GET['repdegree'] . "' AND started>=" . $_GET['startdate'] . " AND started<=" . $_GET['enddate'],$link_id);
  }
  while ($row = $log_query->fetch_assoc()) {
    $user_ID = $row['username'];
    $question_ID = $row['q_id'];
    $screen_no = $row['screen'];
    $log_array[$user_ID][$screen_no][$question_ID] = $row['user_answer'];
    $log_array[$user_ID]['student_id'] = $row['student_id'];
    $log_array[$user_ID]['username'] = $user_ID;
    $log_array[$user_ID]['degree'] = $row['grade'];
    $log_array[$user_ID]['year'] = $row['year'];
    $log_array[$user_ID]['started'] = $row['started'];
    $log_array[$user_ID]['title'] = $row['title'];
    $log_array[$user_ID]['surname'] = $row['surname'];
    $log_array[$user_ID]['initials'] = $row['initials'];
    $log_array[$user_ID]['gender'] = $row['gender'];
    $user_no++;
  }
  
  $row_written = 0;
  foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['username'];
    // Write out the headings.
    if ($row_written == 0) {
      // Only output personal data if assessment, do not show if survey.
      echo 'Gender,Student ID,Degree,Year,Submitted,';
      for ($i=0; $i<$question_no; $i++) {
        $tmp_question_ID = $paper_buffer[$i]['ID'];
        $tmp_screen = $paper_buffer[$i]['screen'];
        if ($i>0) echo ',';
        switch ($paper_buffer[$i]['type']) {
          case 'blank':
            $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],'|');
            for ($sec=1; $sec<=$sections; $sec++) {
              if ($sec > 1) echo ',';
              echo 'Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'extmatch':
            $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],'|') + 1;
            for ($sec=1; $sec<=$sections; $sec++) {
              if ($sec > 1) echo ',';
              echo 'Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'matrix':
            $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],'|');
            for ($sec=1; $sec<=$sections; $sec++) {
              if ($sec > 1) echo ',';
              echo 'Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'rank':
            $sections = substr_count($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],',');
            for ($sec=1; $sec<=$sections; $sec++) {
              if ($sec > 1) echo ',';
              echo 'Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'dichotomous':
            $sections = strlen($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
            for ($sec=1; $sec<=$sections; $sec++) {
              if ($sec > 1) echo ',';
              echo 'Q' . ($i+1) . '.' . $sec;
            }
            break;
          case 'mrq':
            $sections = $paper_buffer[$i]['option_no'];
            for ($sec=1; $sec<=$sections; $sec++) {
              if ($sec > 1) echo ',';
              echo 'Q' . ($i+1) . '.' . $sec;
            }
            if ($paper_buffer[$i]['score_method'] == 'other') echo ',Q' . ($i+1) . '.' . $sec;
            break;
          default:
            echo 'Q' . ($i+1);
            break;
        }
      }
      echo "\n";
    }
    // Write out the raw data.
    echo $individual['gender'] . ',' . $individual['student_id'] . ',' . $individual['degree'] . ',' . $individual['year'] . ',' . $individual['started'] . ',';
    for ($i=0; $i<$question_no; $i++) {
      $tmp_question_ID = $paper_buffer[$i]['ID'];
      $tmp_screen = $paper_buffer[$i]['screen'];
      if ($i>0) echo ',';
      switch ($paper_buffer[$i]['type']) {
        case 'blank':
          $log_array[$tmp_user_ID][$tmp_question_ID] = $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID];
          $tmp_answers = str_replace('|',',',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          echo substr($tmp_answers,1);
          break;
        case 'extmatch':
          $log_array[$tmp_user_ID][$tmp_question_ID] = $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID];
          $tmp_answers = str_replace('|',',',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          echo $tmp_answers;
          break;
        case 'matrix':
          $log_array[$tmp_user_ID][$tmp_question_ID] = substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID],1);
          $tmp_answers = str_replace('|',',',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          echo substr($tmp_answers,1);
          break;
        case 'rank':
          $buffer = substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], 1);
          $buffer = str_replace('9999','',$buffer);
          $buffer = str_replace('9990','n/a',$buffer);
          echo $buffer;
          break;
        case 'hotspot':
          echo $log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID][0];
          break;
        case 'dichotomous':
        case 'mrq':
          $chars = $paper_buffer[$i]['option_no'];
          for ($char_pos=0; $char_pos<$chars; $char_pos++) {
            if ($char_pos > 0) echo ',';
            echo substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], $char_pos, 1);
          }
          if ($paper_buffer[$i]['score_method'] == 'other') {
            if (substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], $char_pos, 1) == 'n') {
              echo ',n';
            } else {
              echo ',' . substr($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID], $char_pos+1);
            }
          }
          break;
        case 'textbox':
          $tmp_data = trim($log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          $tmp_data = preg_replace("/(\r\n|\n|\r)/", "", $tmp_data);
          $tmp_data = str_replace('"',"'",$tmp_data);
          
          if (substr($tmp_data,0,1) == '-') $tmp_data = trim(substr($tmp_data,1));
          echo '"' . $tmp_data . '"';
          break;
        default:
          echo str_replace('u','',$log_array[$tmp_user_ID][$tmp_screen][$tmp_question_ID]);
          break;
      }
    }
    echo "\n";
    $row_written++;
  }
  $mysqli->close();
?>