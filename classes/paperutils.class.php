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
 * Utility class for paper related functionality
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/rogostaticsingleton.class.php';

Class Paper_utils extends RogoStaticSingleton {
  public static $inst = NULL;
  public static $class_name = 'PaperUtils';

  /**
  * constructor
  */
  private function __construct() {}
}

Class PaperUtils {

  /**
  * Add a question onto a paper
  *
  * @param $paperID ID of the paper to be used
  * @param $questionID ID of the question to be added
  * @param $screen_no number of the screen to add to
  * @param $display_pos the display position of the new question
  * @param $db Database connection
  */
  public function add_question($paperID, $questionID, $screen_no, $display_pos, $db) {
    $result = $db->prepare("INSERT INTO papers VALUES (NULL, ?, ?, ?, ?)");
    $result->bind_param('iiii', $paperID, $questionID, $screen_no, $display_pos);
    $result->execute();
    $result->close();
  } 

  /**
  * Return the user ID of the paper owner
  *
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return integer 
  */
  public function get_ownerID($paperID, $db) {
    $modules = array();
    $result = $db->prepare("SELECT paper_ownerID FROM properties WHERE property_id = ? LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($paper_ownerID);
    $result->fetch();
    $result->close();

    return $paper_ownerID;
  }
  
  public function get_textual_feedback($paperID, $db, $direction = 'ASC') {
    $textual_feedback = array();
    $i = 1;
    
    $result = $db->prepare("SELECT boundary, msg FROM paper_feedback WHERE paperID = ? ORDER BY boundary $direction");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($boundary, $msg);
    while ($result->fetch()) {
      $textual_feedback[$i]['msg'] = $msg;
      $textual_feedback[$i]['boundary'] = $boundary;
      $i++;
    }
    $result->close();
    
    return $textual_feedback;
  }

  /**
  * Return a array of modules assigned to a paper
  *
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return array 
  */
  public function get_modules($paperID, $db) {
    $modules = array();
    $result = $db->prepare("SELECT idMod, moduleid FROM (modules, properties_modules) WHERE idMod = id AND property_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($idMod, $moduleid);
    while($result->fetch()) {
      $modules[$idMod] = $moduleid;
    }
    $result->close();
    
    return $modules;
  } 

  /**
  * Return a array of metadata pairs assigned to a paper
  *
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return array 
  */
  public function get_metadata($paperID, $db) {
    $metadata = array();
  
    $result = $db->prepare("SELECT name, value FROM paper_metadata_security WHERE paperID = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($security_type, $security_value);
    $result->store_result();
    while ($result->fetch()) {
      $metadata[$security_type] = $security_value;
    }
    $result->close();
    
    return $metadata;
  } 

  /**
  * updates the modules on a paper removes modules if the user has permission to do so and then adds in the new modules
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  public function update_modules($paper_modules, $paperID, $db, $userObject) {
    $staff_modules = $userObject->get_staff_modules();
    if (count($staff_modules) < 0) {
      $user_modules = get_staff_modules($userObject->get_user_ID(), $db, $userObject->get_user_ID());
    }

    if (count($staff_modules) > 0) {
      if ($userObject->has_role('SysAdmin')) { 
        $user_can_delete = ''; //no restrictions
      } else {
        $user_can_delete = "AND idMod IN (" . implode(',', array_keys($staff_modules)) . ")"; //users can only remove modules if they are on the team
      }
      
      $editProperties = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? $user_can_delete");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();
    }
    
    Paper_utils::add_modules($paper_modules, $paperID, $db);
  }

  /**
  * Add modules to a paper ignoring duplicates
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  public function add_modules($paper_modules, $paperID, $db) {
    $editProperties = $db->prepare("INSERT INTO properties_modules VALUES(?, ?) ON DUPLICATE KEY UPDATE idMod = idMod");
    foreach ($paper_modules as $idMod => $ModuleID) {
      $editProperties->bind_param('ii', $paperID, $idMod);
      $editProperties->execute();
    }
    $editProperties->close();
  }

  /**
  * remove modules from a paper 
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  public function remove_modules($paper_modules, $paperID, $db) {
    $remove = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? and idMod = ?");
    foreach ($paper_modules as $idMod => $ModuleID) {
      $remove->bind_param('ii', $paperID, $idMod);
      $remove->execute();
    }
    $remove->close();
  }

  public function get_title($paperID, $db) {
    $result = $db->prepare("SELECT paper_title FROM properties WHERE property_id = ? LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($paper_title);
    $result->fetch();
    $result->close();
    
    if ($paper_title == '') {
      return false;
    }
    
    return $paper_title;
  }
  
  public function is_paper_title_unique($title, $db) {
    $unique = true;
    $result = $db->prepare("SELECT property_id FROM properties WHERE paper_title = ? LIMIT 1");
    $result->bind_param('s', $title);
    $result->execute();  
    $result->store_result();
    $result->bind_result($tmp_id);
    $rows_found = $result->num_rows;
    $result->free_result();
    $result->close();
    
    if ($rows_found > 0) {
      $unique = false;
    }
    
    return $unique;
  }

  /**
  * Delete a paper from rogo (N.B sets the deleted field we don't actuality delete the row form the papers table)
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  public function delete_paper($paperID, $db) {
    //delete the paper
    $update = $db->prepare("UPDATE properties SET deleted=NOW(), paper_ownerID=-1 WHERE property_id=?");
    $update->bind_param('i', $paperID);
    $update->execute();
    $update->close();
  }

  /**
  * caculates the number of screens on a paper
  * return @int max 
  */
  public function get_numder_of_screens($paperID, $db) {
    $no_screens = 0;
    $result = $db->prepare("SELECT max(screen) FROM papers WHERE paper = ? group by paper LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    if($result->num_rows <= 0) {
      $result->close();
      return 0;
    } else {
      $result->bind_result($no_screens);
      $result->fetch();
      $result->close();
      return $no_screens;
    }
  }
  
  public function displayIcon($paper_type, $title, $initials, $surname, $locked,  $retired) {
    global $string;
    $paper_type = strval($paper_type);
    
    if ($retired != '') {
      $retired = '_retired';
    }
    
    if (isset($surname)) {
      $alt = "&#013;Author: $title $initials $surname";
    } else {
      $alt = '';
    }
    
    switch ($paper_type) {
      case '0':
        $html = "<img src=\"../artwork/formative" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '1':
        $html = "<img src=\"../artwork/progress" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '2':
        $html = "<img src=\"../artwork/summative" . $retired . $locked . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '3':
        $html = "<img src=\"../artwork/survey" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '4':
        $html = "<img src=\"../artwork/osce" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '5':
        $html = "<img src=\"../artwork/offline" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '6':
        $html = "<img src=\"../artwork/peer_review" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case 'objectives':
        $html = "<img src=\"../artwork/feedback_release_icon.png\" width=\"48\" height=\"48\" alt=\"Objectives Feedback\" />";
        break;
      case 'questions':
        $html = "<img src=\"../artwork/question_release_icon.png\" width=\"48\" height=\"48\" alt=\"Questions Feedback\" />";
        break;
    }
    return $html;
  }
  
}