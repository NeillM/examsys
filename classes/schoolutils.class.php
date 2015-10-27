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
* Utility class for functionality related to schools
*
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/


Class SchoolUtils {

    /**
     * Adds a new school to the 'schools' table and returns its new ID.
     * @param int $facultyID    - ID of the faculty to which the new school belongs.
     * @param string $school    - Name of the new school
     * @param object $db        - Link to mysqli
     *
     * @return int              - The ID of the school.
     */
    static function add_school($facultyID, $school, $db) {
        if ($facultyID === '' or $school === '') {
          return false;
        }

                    $schoolID = SchoolUtils::school_name_exists($school, $db);
                    if ($schoolID !== false) {
                      return $schoolID;
                    }

        $result = $db->prepare("INSERT INTO schools(school, facultyID) VALUES (?, ?)");
        $result->bind_param('si', $school, $facultyID);
        $result->execute();
        $result->close();
        if ($db->errno != 0) {
          return false;
        }

        return $db->insert_id;
    }

    /**
     * Returns an array of schools (that are not deleted).
     * @param object $db        - Link to mysqli
     *
     * @return array            - An array of schools keyed by ID and holding school name and faculty ID.
     */
     static function get_school_list_by_id($db) {
        $school_list = array();

        $stmt = $db->prepare("SELECT id, school, facultyID FROM schools WHERE deleted IS NULL");
        $stmt->execute();
        $stmt->bind_result($id, $school, $faculityID);
        while ($stmt->fetch()) {
          $school_list[$id]['school'] = $school;
          $school_list[$id]['faculityID'] = $faculityID;
        }
        $stmt->close();

        return $school_list;
    }

    /**
     * Returns the ID of a school from a provided name.
     * @param int $school_name  - Name of the school to be looked up.
     * @param object $db        - Link to mysqli
     *
     * @return int              - ID of the school.
     */
    static function get_school_id_by_name($school_name, $db) {
        if ($school_name == '') {
          return false;
        }

        $stmt = $db->prepare("SELECT id FROM schools WHERE deleted IS NULL and school = ?");
        $stmt->bind_param('s', $school_name);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        return $id;
    }


    /**
     * Get the schools a member of staff with 'Admin' rights has access to.
     * @param int $admin_userid - ID of the member of staff user
     * @param object $db        - Link to mysqli
     *
     * @return array            - List of schools the member of staff has access to.
     */
    static function get_admin_schools($admin_userid, $db) {
        $school_list = array();

        $stmt = $db->prepare("SELECT schools_id FROM admin_access WHERE userID = ?");
        $stmt->bind_param('i', $admin_userid);
        $stmt->execute();
        $stmt->bind_result($school);
        while ($stmt->fetch()) {
            $school_list[] = $school;
        }
        $stmt->close();

        return $school_list;
    }

    /**
     * Check if a school name exists in a given Faculty
     * @param int $facultyID  - ID of faculty to check
     * @param string $school  - School name to check
     * @param object $db      - Link to mysqli
     *
     * @return bool           - True if school name already exists for the faculty
     */
    static function school_exists_in_faculty($facultyID, $school, $db) {
        $row_no = 0;

        $query = 'SELECT id FROM schools WHERE school = ? AND facultyID = ? AND deleted IS NULL';
        $stmt = $db->prepare($query);
        $stmt->bind_param('si', $school, $facultyID);
        $stmt->execute();
        $stmt->store_result();
        $row_no = $stmt->num_rows;
        $stmt->close();

        return $row_no > 0;
     }

    /**
     * Check if a school ID exists
     * @param int $schoolID - ID of the school to check
     * @param object $db    - Link to mysqli
     *
     * @return bool         - True if the school ID is found
     */
    static function schoolid_exists($schoolID, $db) {
        $row_no = 0;

        $query = 'SELECT id FROM schools WHERE id = ? AND deleted IS NULL';
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $schoolID);
        $stmt->execute();
        $stmt->store_result();
        $row_no = $stmt->num_rows;
        $stmt->close();

        return $row_no > 0;
    }

    /**
     * Check if a school name already exists
     * @param int $school   - Name of the school to check
     * @param object $db    - Link to mysqli
     *
     * @return bool         - True if the school name is found
     */
    static function school_name_exists($school, $db) {
        $schoolID = 0;
        $row_no = 0;

        $stmt = $db->prepare('SELECT id FROM schools WHERE school = ? AND deleted IS NULL');
        $stmt->bind_param('s', $school);
        $stmt->execute();
        $stmt->bind_result($schoolID);
        $stmt->store_result();
        $stmt->fetch();
        $row_no = $stmt->num_rows;
        $stmt->close();

        if ($row_no > 0) {
            return $schoolID;
        } else {
            return false;
        }
    }

    static function get_school_faculty($schoolID, $db) {
        $school_name = false;

        $stmt = $db->prepare('SELECT school FROM schools WHERE id = ? AND deleted IS NULL');
        $stmt->bind_param('i', $schoolID);
        $stmt->execute();
        $stmt->bind_result($school_name);
        $stmt->store_result();
        $stmt->fetch();
        $stmt->close();

        return $school_name;
    }

    /**
     * Delete a school by setting a flag
     * @param int $schoolID - ID of the school to delete
     * @param object $db    - Link to mysqli
     *
     * @return bool         - Return false if no schoolID is passed.
     */
     static function delete_school($schoolID, $db) {
        if ($schoolID == '') {
          return false;
        }

        $result = $db->prepare("UPDATE schools SET deleted = NOW() WHERE id = ?");
        $result->bind_param('i', $schoolID);
        $result->execute();
        $result->close();
        return true;
      }
      
    /**
     * Updates a school
     * @param integer $id  - School id in rogo.
     * @param int $facultyID - ID of the faculty to which the new school belongs.
     * @param string $school - Name of the new school
     * @param object $db - Link to mysqli
     *
     * @return int|bool - The ID of the school / false on error.
     */
    static function update_school($id, $facultyID, $school, $db) {
        if ($facultyID === '' or $school === '') {
          return false;
        }

        $schoolID = SchoolUtils::school_name_exists($school, $db);
        if ($schoolID !== false) {
          return false;
        }

        $result = $db->prepare("UPDATE schools set school = ?, facultyID = ? where id = ?");
        $result->bind_param('sii', $school, $facultyID, $id);
        $result->execute();
        $result->close();
        if ($db->errno != 0) {
          return false;
        }

        return true;
    }
    
  /**
   * Get factulty details
   * @param integer $id 
   * @param mysqli $db 
   * @return array details
   */
  static function get_school_details_by_id($id, $db) {
    $result = $db->prepare("SELECT school, facultyID FROM schools WHERE id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $faculty);
    $result->fetch();
    $result->close();

    return array('name' => $name, 'faculty' => $faculty);
  }
  
  /**
   * Check if school contains modules or courses
   * @param integer $id school id
   * @param mysqli $db 
   * @return  
   */
  static function school_in_use($id, $db) {
    $result = $db->prepare("SELECT NULL FROM courses WHERE schoolid = ?
        UNION SELECT NULL FROM modules WHERE schoolid = ?");
    $result->bind_param('ii', $id, $id);
    $result->execute();
    $result->fetch();
    if ($result->num_rows > 0) {
        $result->close();
        return true;
    }
    $result->close();
    return false;
  }
  
  /**
   *  Generate a school id based on name and faculty.
   * @param string $school - school name
   * @param string $faculty - faculty name
   * @param mysqli $db
   * @return integer 
  */
  static function generate_school_id($school, $faculty, $db) {
    $facultyid = FacultyUtils::facultyid_by_name($faculty, $db);
    if (!$facultyid) {
        // Add new faculty.
        $facultyid = FacultyUtils::add_faculty($faculty, $db);
    }
    // Add new school to faculty.
    $schoolid = SchoolUtils::add_school($facultyid, $school, $db);
    return $schoolid;
  }
}