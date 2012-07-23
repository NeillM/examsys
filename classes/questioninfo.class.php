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
    
    $html = "$q_id - ;-)";

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