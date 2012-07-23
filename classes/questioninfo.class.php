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
* 
* Utility class for question related functions
* 
* @author Anthony Brown and Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class QuestionInfo {

  
  /**
   * Get the question information
   * @paraminteger $q_id
   * @param resource $db
   * @return formated HTML for display of question information
   */
  static function get_full_querstion_information($q_id, $db) {
    
    $html = "";
    $line_no = 0;
    $icons = array('formative','progress','summative','survey','osce','offline');
    $performance = array();

    //get performace data from all papers this question has appered on
    $result = $mysqli->prepare("SELECT paperID, cohort_size, DATE_FORMAT(taken,\"$cfg_short_date\"), part_no, p, d FROM performance_main, performance_details WHERE performance_main.id=performance_details.perform_id AND q_id=?");
    $result->bind_param('i', $_GET['q_id']);
    $result->execute();
    $result->bind_result($paperID, $cohort_size, $taken, $part_no, $p, $d);
    while ($result->fetch()) {
      $performance[$paperID][$part_no] = array('cohort'=>$cohort_size, 'taken'=>$taken, 'p'=>$p, 'd'=>$d);
    }
    $result->close();
    
    $question_data = $mysqli->prepare("SELECT email, title, surname, initials, DATE_FORMAT(creation_date,\"%d/%m/%Y %H:%i\") AS creation_date, DATE_FORMAT(last_edited,\"%d/%m/%Y %H:%i\") AS last_edited, DATE_FORMAT(locked,\"$cfg_long_date_time\") AS locked, q_group, q_type, std, status FROM (users, questions) WHERE users.id=questions.ownerID AND q_id=? LIMIT 1");
    $question_data->bind_param('i', $_GET['q_id']);
    $question_data->execute();
    $question_data->bind_result($email, $title, $surname, $initials, $creation_date, $last_edited, $locked, $q_group, $q_type, $std, $status);
    $question_data->store_result();
    $question_data->fetch();
    $question_data->close(); 
    
    if ($q_group == '') $q_group = '<span style="color:#808080">N/A</span>';
    if ($locked == '') $locked = '<span style="color:#808080">N/A</span>';


    if (strpos($userroles,'Demo') !== false) {
      $owner = 'Dr J, Bloggs (<a href="">joe.bloggs@uni.ac.uk</a>)';
    } else {
      $owner = "$title $initials $surname (<a href=\"mailto:$email\">$email</a>)";
    }
    echo "<tr><td style=\"width:70px\">" . $string['author'] . "</td><td>$owner</td></tr>\n";
    echo "<tr><td>" . $string['created'] . "</td><td>$creation_date</td></tr>\n";
    echo "<tr><td>" . $string['modified'] . "</td><td>$last_edited</td></tr>\n";
    echo "<tr><td>" . $string['locked'] . "</td><td>$locked</td></tr>\n";
    echo "<tr><td>" . $string['teams'] . "</td><td>$q_group</td></tr>\n";
    echo "<tr><td>Copies:</td><td></td></tr>\n";
    echo "</table>\n";
    
    echo "<div style=\"margin:5px; display:block; height:95px; overflow-y:scroll; border:1px solid #95AEC8; font-size:100%; background-color:white\">\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" style=\"width:100%\">";
    echo "<tr><th>Type</th><th>Paper Name</th><th>Question No</th></tr>\n";
    check4Copies($mysqli);
    check4Copied($mysqli);
    echo "</table>\n</div>\n<br />\n";

    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"2\">" . $string['followingpapers'] . "</td></tr>\n";
    echo "</table>\n"
    
    echo "<div style=\"margin:5px; display:block; height:210px; overflow-y:scroll; border:1px solid #95AEC8; font-size:100%; background-color:white\">\n<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:100%\">";
    echo "<tr><th></th><th>" . $string['papername'] . "</th><th>" . $string['screenno'] . "</th><th>" . $string['examdate'] . "</th><th>" . $string['cohort'] . "</th><th></th><th>" . $string['p'] . "</th><th>" . $string['d'] . "</th></tr>\n";

    $result = $mysqli->prepare("SELECT paper_title, paper_type, paper, screen, properties.deleted FROM (papers, properties) WHERE properties.property_id=papers.paper AND question=?");
    $result->bind_param('i', $_GET['q_id']);
    $result->execute();
    $result->bind_result($paper_title, $paper_type, $paper, $screen, $deleted);
    $result->store_result();
    while ($result->fetch()) {
      echo "<tr><td><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"0\" /></td>";
      $title_split = explode('[deleted', $paper_title);
      if (isset($title_split[1])) {
        echo "<td><a href=\"\" style=\"color:#808080\" onclick=\"loadPaper('$paper')\">" . $title_split[0] . "</a></td>";
      } else {
        echo "<td><a href=\"\" onclick=\"loadPaper('$paper')\">" . $title_split[0] . "</a></td>";
      }
      if ($deleted != '') {
        echo "<td style=\"color:red\">&lt;deleted " . str_replace(']','',$title_split[1]) . "&gt;</td>";
      } else {
        echo "<td class=\"num\">$screen</td>";
      }
      
      if (isset($performance[$paper][1]['taken'])) {
        echo "<td>" . $performance[$paper][1]['taken'] . "</td><td class=\"num\">" . $performance[$paper][1]['cohort'] . "</td><td style=\"text-align:right\">" . displayParts($performance[$paper], $q_type) . "</td><td class=\"num\">" . displayP($performance[$paper], $q_type) . "</td><td class=\"num\">" . displayD($performance[$paper], $q_type) . "</td>";
      } else {
        echo "<td></td><td></td><td></td><td></td><td></td>";
      }
      echo "</tr>\n";
      $line_no++;
    }
    $result->close();
    $mysqli->close();
  ?>
  </table>
  </div>

    return $html;
  }


  static function multiPartQuestion($type) {
    if ($type == 'blank' or $type == 'dichotomous' or $type == 'extmatch' or $type == 'hotspot' or $type == 'labelling' or $type == 'matrix') {
      return true;
    } else {
      return false;
    }
  }
  
  static function displayParts($perform_data, $q_type) {
    $html = '';
    $numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii');
    if (multiPartQuestion($q_type)) {
      for ($i=0; $i<count($perform_data); $i++) {
        $html .= $numerals[$i] . '.<br />';
      }
    }
    
    return $html;
  }
  
  static function displayP($perform_data, $q_type) {
    $html = '';
    
    if (multiPartQuestion($q_type)) {
      foreach ($perform_data as $single_data) {
        $html .= pWarning(number_format($single_data['p']/100, 2)) . '<br />';
      }
    } else {
      $html = pWarning(number_format($perform_data[1]['p']/100, 2));
    }
    
    return $html;
  }
  
  static function pWarning($value) {
    if ($value < 0.2) {
      return '<span style="color:#C00000">' . $value . '</span>';
    } else {
      return $value;
    }
  }
    
  static function displayD($perform_data, $q_type) {
    $html = '';
    
    if (multiPartQuestion($q_type)) {
      foreach ($perform_data as $single_data) {
        $html .= dWarning(number_format($single_data['d']/100, 2)) . '<br />';
      }
    } else {
      $html = dWarning(number_format($perform_data[1]['d']/100, 2));
    }
    
    return $html;
  }
    
  static function dWarning($value) {
    if ($value <= 0.15) {
      return '<span style="color:#C00000">' . $value . '</span>';
    } else {
      return $value;
    }
  }
    
  static function check4Copies($db) {
    $row_number = 0;
    
    // Get the ID of the original question.
    $copy_data = $db->prepare("SELECT old FROM track_changes WHERE type='Copied Question' AND typeID=? LIMIT 1");
    $copy_data->bind_param('i', $_GET['q_id']);
    $copy_data->execute();
    $copy_data->bind_result($copyID);
    $copy_data->store_result();
    $copy_data->fetch();
    $copy_data->close();
        
    if (isset($copyID)) {
      // Look up what paper it was used on.
      $copy_question_no = 0;
      $row_no = 1;
      $copy_data = $db->prepare("SELECT property_id, paper_title, question, q_type FROM (papers, properties, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND paper=(SELECT paper FROM papers WHERE question=? LIMIT 1) ORDER BY screen, display_pos");
      $copy_data->bind_param('i', $copyID);
      $copy_data->execute();
      $copy_data->bind_result($copy_paperID, $copy_paper_title, $copy_question, $copy_q_type);
      $copy_data->store_result();
      while ($copy_data->fetch()) {
        if ($copy_q_type != 'info') $row_number++;
        if ($copy_question == $copyID) $copy_question_no = $row_number;
      }
      $copy_data->close();
      if ($copy_question_no == 0) {
        return "<tr><td>Copy of</td><td colspan=\"2\">Question ID #$copyID</td></tr>\n";
      } else {
        return "<tr><td>Copy of</td><td><a href=\"\" onclick=\"loadPaper('$copy_paperID')\">$copy_paper_title</a></td><td>$copy_question_no</td></tr>\n";
      }
    } else {
      return "<tr><td></td><td></td></tr>\n";
    }
  }
  
  
 static function check4Copied($db) {
    // Get the ID of the original question.
    $ids = array();
    $copy_data = $db->prepare("SELECT typeID FROM track_changes WHERE type='Copied Question' AND old = ? AND typeID != ?");
    $copy_data->bind_param('ii', $_GET['q_id'], $_GET['q_id']);
    $copy_data->execute();
    $copy_data->bind_result($typeID);
    $copy_data->store_result();
    while ($copy_data->fetch()) {
      $ids[] = $typeID;
    }
    $copy_data->close();
    
    foreach ($ids as $copyID) {
      // Look up what paper it was used on.
      $copy_question_no = 0;
      $row_number = 0;
      $row_no = 1;
      $copy_data = $db->prepare("SELECT property_id, paper_title, question, q_type FROM (papers, properties, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND paper=(SELECT paper FROM papers WHERE question=? LIMIT 1) ORDER BY screen, display_pos");
      $copy_data->bind_param('i', $copyID);
      $copy_data->execute();
      $copy_data->bind_result($copy_paperID, $copy_paper_title, $copy_question, $copy_q_type);
      $copy_data->store_result();
      while ($copy_data->fetch()) {
        if ($copy_q_type != 'info') $row_number++;
        if ($copy_question == $copyID) $copy_question_no = $row_number;
      }
      $copy_data->close();
      if ($copy_question_no == 0) {
        return "<tr><td>Source for</td><td colspan=\"2\">Question ID #$copyID</td></tr>\n";
      } else {
        return "<tr><td>Source for</td><td><a href=\"\" onclick=\"loadPaper('$copy_paperID')\">$copy_paper_title</a></td><td>$copy_question_no</td></tr>\n";
      }
    }
  }

}
?>