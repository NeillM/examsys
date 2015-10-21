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
* Faculty api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Faculty class
 */
class facultymanagement extends \api\abstractmanagement {
    
    // The database connection.
    private $db;
    
    // Language pack component.
    private $langcomponent = 'api/facultymanagement';
    
    /**
     * @brief Constructor
     * @param mysqli $mysqli the database connection
     * @return  
     */
    function __construct($mysqli, $configObject = null) {
        $this->db = $mysqli;
    }
    
     /**
     * @brief Return response to request
     * @param array $data 
     * @param string $action
     * @param string $nodeid
     * @return  
     */
    public function get_response($data, $action, $nodeid, $error = null) {
        return $response = array(
            "status" => $data['status'],
            "id" => $data['id'],
            "node" => $action,
            "nodeid" => $nodeid);
    }
    
    /**
     * @brief Create/Update faculty
     * @param array $params faculty creation parameters
     * @return - success status and faculty id
     */
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_updated', 'faculty_does_not_exist'
            , 'faculty_not_created', 'faculty_already_exists'));
        $action = 'create';
        if (isset($params['id']) and $params['id'] != '') {
            $facultyid = \FacultyUtils::faculty_name_by_id($params['id'], $this->db);
            $action = 'update';
            if ($facultyid) {
                $details = \FacultyUtils::get_faculty_details_by_id($params['id'], $this->db);
            }
        }
        
        // Get name if not provided.
        if ($facultyid and (!isset($params['name']) or $params['name'] == '')) {
            $params['name'] = $details['name'];
        }
            
        // Update faculty.
        if ($action == 'update') {
            if ($facultyid) {
                $update = \FacultyUtils::update_faculty($params['id'], $params['name'],  $this->db);
                if ($update) {
                    $data = array('status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('status' => $strings['faculty_not_updated'], 'id' => null);
                }
            } else {
                $data = array('status' => $strings['faculty_does_not_exist'], 'id' => null);
            }
        // Create faculty.
        } else {
            $facultyid = \FacultyUtils::facultyid_by_name($params['name'], $this->db);
            if (!$facultyid) {
                $id = \FacultyUtils::add_faculty($params['name'], $this->db);
                if ($id) {
                    $data = array('status' => 'OK', 'id' => $id);
                } else {
                    $data = array('status' => $strings['faculty_not_created'], 'id' => null);
                }
            } else {
                $data = array('status' => $strings['faculty_already_exists'], 'id' => $courseid);
            }
        }
        
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * @brief Delete faculty
     * @param array $parms delete faculty parameters
     * @return success status and faculty id 
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_deleted_inuse', 'faculty_not_deleted'
            , 'faculty_does_not_exist'));
        if (isset($params['id']) and $params['id'] != '') {
            $facultyid = \FacultyUtils::faculty_name_by_id($params['id'], $this->db);
        }
        if ($facultyid) {
            // Only delete faculty if it contains no schools.
            $schools = \FacultyUtils::get_schools_in_faculty($params['id'], $this->db);
            if (isset($schools) and $schools > 0) {
                $data = array('status' => $strings['faculty_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \FacultyUtils::delete_faculty($params['id'], $this->db);
                if ($deleted) {
                    $data = array('status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('status' => $strings['faculty_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('status' => $strings['faculty_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}