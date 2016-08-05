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
* Utility class for radnom block related functionality
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 The University of Nottingham
*/


Class random_utils {

  /**
   * Function to get the radnom question ids based on the parent question id
   * @param integer $id question id
   * @param mysqli $db db connection
   * @return array|bool random question ids or false is non found
   */
  static public function get_random_qids_for_question($id, $db) {
    $random = $db->prepare("SELECT q_id FROM random_link WHERE id = ?");
    $random->bind_param('i', $id);
    $random->execute();
    $random->store_result();
    $random->bind_result($q_id);
    if ($random->num_rows == 0) {
      $qids = false;
    } else {
      $qids = array();
      while ($random->fetch()) {
        $qids[] = $q_id;
      }
    }
    $random->close();
    return $qids;
  }
  
  /**
   * Insert random question reference row
   * @param integer $id parent question id
   * @param integer $q_id child question id
   * @param mysqli $db db connection
   * @return bool true on success, false otherwise
   */
  static public function insert_random_link($id, $q_id, $db) {
    $sql = $db->prepare("INSERT INTO random_link (id, q_id) VALUES (?, ?)");
    $sql->bind_param('ii', $id, $q_id);
    $sql->execute();
    $sql->close();
    if ($db->errno != 0) {
        return false;
    }
    return true;
  }
  
  /**
   * delete random question reference row
   * @param integer $id parent question id
   * @param mysqli $db db connection
   * @return bool true on success, false otherwise
   */
  static public function delete_random_link($id, $db) {
    $sql = $db->prepare("DELETE FROM random_link  WHERE id = ?");
    $sql->bind_param('i', $id);
    $sql->execute();
    $sql->close();
    if ($db->errno != 0) {
        return false;
    }
    return true;
  }
}