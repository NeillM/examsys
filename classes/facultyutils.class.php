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
 * Utility class for installer related functionality
 *
 * @author Anthony Brown, Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */


Class FacultyUtils {

  static function facultyid_exists($facultyID, $db) {
    $result = $db->prepare("SELECT id FROM faculty WHERE id = ? AND deleted IS NULL");
    $result->bind_param('i', $facultyID);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_paperid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $exist = false;
    } else {
      $exist = true;
    }
    $result->free_result();
    $result->close();

    return $exist;
  }

  static function add_faculty($faculty, $db) {

    $result = $db->prepare("INSERT INTO faculty(name) VALUES(?)");
    $result->bind_param('s', $faculty);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }

    return $db->insert_id;
  }

  static function get_faculty_id_by_name($faculty, $db) {
    $stmt = $db->prepare("SELECT id FROM schools WHERE name = ?");
    $stmt->bind_param('s', $faculty);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->store_result();
    $stmt->fetch();
    $row = $stmt->num_rows;
    $stmt->close();
    if ($row == 0) {
      $stmt = $db->prepare("SELECT id FROM faculty WHERE deleted IS NULL and name='UNKNOWN Faculty'");
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->store_result();
      $stmt->fetch();
      $stmt->close();
    }
    return $id;
  }
}
