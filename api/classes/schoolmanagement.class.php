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
* Dchool api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * School class
 */
class schoolmanagement extends \api\abstractmanagement {
    
    // The database connection.
    private $db;
    
    // Language pack component.
    private $langcomponent = 'api/schoolmanagement';
    
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
     * @brief Create/Update school
     * @param array $params school creation parameters
     * @return - success status and school id
     */
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_updated', 'school_does_not_exist'
            , 'school_created', 'school_alreads_exists', 'faculty_not_supplied'));
        $action = 'create';
        $faculty = true;
        if (isset($params['id']) and $params['id'] != '') {
            $schoolid = \SchoolUtils::schoolid_exists($params['id'], $this->db);
            $action = 'update';
            if ($schoolid) {
                $details = \SchoolUtils::get_school_details_by_id($params['id'], $this->db);
            }
        }
        
        // Get name if not provided.
        if ($schoolid and (!isset($params['name']) or $params['name'] == '')) {
            $params['name'] = $details['name'];
        }
        
        // Get faculty if provided.
        if (isset($params['faculty']) and $params['faculty'] != '') {
            $facultyid = \FacultyUtils::facultyid_by_name($params['faculty'], $this->db);
            if (!$facultyid) {
                $facultyid = \FacultyUtils::add_faculty($params['faculty'], $this->db);
            }
        // Get faculty if not provided.           
        } else if($schoolid) {
            $facultyid = $details['faculty'];
        } else {
            $faculty = false;
        }
        
        if ($faculty) {        
            // Update school.
            if ($action == 'update') {
                if ($schoolid) {
                    $update = \SchoolUtils::update_school($params['id'], $facultyid, $params['name'], $this->db);
                    if ($update) {
                        $data = array('status' => 'OK', 'id' => $params['id']);
                    } else {
                        $data = array('status' => $strings['school_not_updated'], 'id' => null);
                    }
                } else {
                    $data = array('status' => $strings['school_does_not_exist'], 'id' => null);
                }
            // Create school.
            } else {
                $schoolid = \SchoolUtils::get_school_id_by_name($params['name'], $this->db);
                if (!$schoolid) {
                    $id = \SchoolUtils::add_school($facultyid, $params['name'], $this->db);
                    if ($id) {
                        $data = array('status' => 'OK', 'id' => $id);
                    } else {
                        $data = array('status' => $strings['school_created'], 'id' => null);
                    }
                } else {
                    $data = array('status' => $strings['school_alreads_exists'], 'id' => $courseid);
                }
            }
        } else {
            $data = array('status' => $strings['faculty_not_supplied'], 'id' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * @brief Delete school
     * @param array $parms delete school parameters
     * @return success status and school id
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_deleted_inuse', 'school_not_deleted'
            , 'school_does_not_exist'));
        if (isset($params['id']) and $params['id'] != '') {
            $schoolid = \SchoolUtils::schoolid_exists($params['id'], $this->db);
        }
        if ($schoolid) {
            // Only delete school if it contains no modules or courses.
            $inuse = \SchoolUtils::get_modules_courses_in_school($params['id'], $this->db);
            if ($inuse) {
                $data = array('status' => $strings['school_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \SchoolUtils::delete_school($params['id'], $this->db);
                if ($deleted) {
                    $data = array('status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('status' => $strings['school_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('status' => $strings['school_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}