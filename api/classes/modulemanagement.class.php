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
* Module api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Course class
 */
class modulemanagement extends \api\abstractmanagement {
    
    // The database connection.
    private $db;
    
    // Language pack component.
    private $langcomponent = 'api/modulemanagement';
    
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
     * @brief Enrol student on a Module.
     * @param array $params module enrol parameters
     * @return array - success status and enrolment id
     */
    public function enrol($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_not_enrolled', 'user_does_not_exist'));
        $userid = \UserUtils::userid_exists($params['userid'], $this->db);
        if ($userid) {
            $yearutils = new \yearutils($this->db);
            if ($params['session'] == '') {
                $session = $yearutils->get_current_session();
            } else {
                $session = $params['session'];
            }
            $ret = \UserUtils::add_student_to_module($params['userid'], $params['moduleid'], $params['attempt'], $session, $this->db, 1);
            if ($ret) {
                $data = array('status' => 'OK', 'id' => $ret);
            } else {
                $data = array('status' => $strings['user_not_enrolled'], 'id' => null);
            }
        } else {
            $data = array('status' => $strings['user_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'enrol', $params['nodeid']);
    }
    
    /**
     * @brief UnEnrol student on a Module.
     * @param array $params module enrol parameters
     * @return array - success status and enrolment id
     */
    public function unenrol($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_not_unenrolled', 'user_does_not_exist'));
        $userid = \UserUtils::userid_exists($params['userid'], $this->db);
        if ($userid) {
            $yearutils = new \yearutils($this->db);
            if ($params['session'] == '') {
                $session = $yearutils->get_current_session();
            } else {
                $session = $params['session'];
            }
            $ret = \UserUtils::remove_student_from_module($params['userid'], $params['moduleid'], $session, $this->db);
            if ($ret) {
                $data = array('status' => 'OK', 'id' => $ret);
            } else {
                $data = array('status' => $strings['user_not_unenrolled'], 'id' => null);
            }
        } else {
            $data = array('status' => $strings['user_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'unenrol', $params['nodeid']);
    }
    
    /**
     * @brief Create/Update module
     * @param array $params module creation parameters
     * @return - success status and module id
     */
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_updated', 'module_does_not_exist',
            'module_not_created', 'module_already_exists', 'faculty_not_supplied'));
        $faculty = true;
        $action = 'create';
        if (isset($params['id']) and $params['id'] != '') {
            $moduleid = \module_utils::get_moduleid_from_id($params['id'], $this->db);
            $action = 'update';
            if ($moduleid) {
                $details = \module_utils::get_full_details_by_ID($params['id'], $this->db);
            }
        }
        // Get school id if school name provided.
        if (isset($params['school']) and $params['school'] != '') {
            $schoolid = \SchoolUtils::school_name_exists($params['school'], $this->db);
            if (!$schoolid) {
                if (isset($params['faculty']) and $params['faculty'] != '') {
                    $schoolid = \SchoolUtils::generate_school_id($params['school'], $params['faculty'], $this->db);
                } else {
                    $faculty = false;
                }
            }
        // Get school id if school name not provided.
        } else if($moduleid) {
            $schoolid = $details['schoolid'];
        }
        
        if ($faculty) {
            // Get module code if not provided.
            if ($moduleid and (!isset($params['modulecode']) or $params['modulecode'] == '')) {
                $params['modulecode'] = $details['modulecode'];
            }
            
            // Get name if not provided.
            if ($moduleid and (!isset($params['name']) or $params['name'] == '')) {
                $params['name'] = $details['name'];
            }
            
            // Get student management system if not provided.
            if ($moduleid and (!isset($params['sms']) or $params['sms'] == '')) {
                $params['sms'] = $details['sms'];
            }
            
            // Update Module.
            if ($action == 'update') {
                if ($moduleid) {
                    $update = \module_utils::update_module_by_id($params['id'], $moduleid, 
                        $params['name'], $schoolid, $params['sms'], $this->db);
                    if ($update) {
                        $data = array('status' => 'OK', 'id' => $params['id']);
                    } else {
                        $data = array('status' => $strings['module_not_updated'], 'id' => null);
                    }
                } else {
                    $data = array('status' => $strings['module_does_not_exist'], 'id' => null);
                }
            // Create Module.
            } else {
                $moduleid = \module_utils::get_idMod($params['modulecode'], $this->db);
                if (!$moduleid) {
                    $id = \module_utils::add_modules($params['modulecode'], $params['name'], 1, $schoolid, '', $params['sms'],
                        '', false, false, false, false, '', '', $this->db, false, '', '', '', 0);
                    if ($id) {
                        $data = array('status' => 'OK', 'id' => $id);
                    } else {
                        $data = array('status' => $strings['module_not_created'], 'id' => null);
                    }
                } else {
                    $data = array('status' => $strings['module_already_exists'], 'id' => $courseid);
                }
            }
        } else {
            $data = array('status' => $strings['faculty_not_supplied'], 'id' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }

    /**
     * @brief Delete module
     * @param array $parms delete module parameters
     * @return success status and module id
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_deleted_inuse', 'module_not_deleted',
            'module_does_not_exist'));
        if (isset($params['id']) and $params['id'] != '') {
            $moduleid = \module_utils::get_moduleid_from_id($params['id'], $this->db);
        }
        if ($moduleid) {
             // Only delete module if it contains no enrolments, and no papers
            $inuse = \module_utils::get_enrol_papers_on_module($params['id'], $this->db);
            if ($inuse) {
                $data = array('status' => $strings['module_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \module_utils::delete_module($params['id'], $this->db);
                if ($deleted) {
                    $data = array('status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('status' => $strings['module_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('status' => $strings['module_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}