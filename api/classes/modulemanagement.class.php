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
       
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/modulemanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'MODULE_NOT_DELETED' => 500,
        'MODULE_DOES_NOT_EXIST' => 501,
        'MODULE_NOT_DELETED_INUSE' => 502,
        'MODULE_NOT_UPDATED' => 503,
        'MODULE_NOT_CREATED' => 504,
        'MODULE_ALREADY_EXISTS' => 505,
        'MODULE_INVALID_FACULTY' => 506,
        'MODULE_INVALID_USER' => 507,
        'MODULE_USER_NOT_ENROLLED' => 508,
        'MODULE_USER_NOT_UNENROLLED' => 509,
        'MODULE_SESSION_NOT_SUPPLIED' => 510
    );
           
    /**
     * Enrol student on a Module.
     * @param array $params module enrol parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array - success status and enrolment id
     */
    public function enrol($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_not_enrolled', 'user_does_not_exist', 'user_already_enrolled'));
        $userexists = \UserUtils::userid_exists($params['userid'], $this->db);
        if ($userexists) {
            $yearutils = new \yearutils($this->db);
            if (empty($params['session'])) {
                $session = $yearutils->get_current_session();
            } else {
                $session = $params['session'];
            }
            $ret = \UserUtils::add_student_to_module($params['userid'], $params['moduleid'], $params['attempt'], $session, $this->db, 1);
            if ($ret === 0) {
                // Already enrolled so just update. Essential the web service taking ownership.
                $id = \UserUtils::get_enrolement_id($params['userid'], $params['moduleid'], $session, $this->db);
                \UserUtils::update_module_enrolement($id, $params['attempt'], 1, $this->db);
                $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id);
            } else {
                if ($ret) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $ret);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_USER_NOT_ENROLLED'], 'status' => $strings['user_not_enrolled'], 'id' => null);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_USER'], 'status' => $strings['user_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'enrol', $params['nodeid']);
    }
    
    /**
     * UnEnrol student on a Module.
     * @param array $params module enrol parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array - success status and enrolment id
     */
    public function unenrol($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_not_unenrolled', 'user_does_not_exist', 'session_not_supplied'));
        $userexists = \UserUtils::userid_exists($params['userid'], $this->db);
        if ($userexists) {
            $yearutils = new \yearutils($this->db);
            if (empty($params['session'])) {
                $data = array('statuscode' => $this->statuscodes['MODULE_SESSION_NOT_SUPPLIED'], 'status' => $strings['session_not_supplied'], 'id' => null);
            } else {
                $session = $params['session'];
                $ret = \UserUtils::remove_student_from_module($params['userid'], $params['moduleid'], $session, $this->db);
                if ($ret) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $ret);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_USER_NOT_UNENROLLED'], 'status' => $strings['user_not_unenrolled'], 'id' => null);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_USER'], 'status' => $strings['user_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'unenrol', $params['nodeid']);
    }
    
    /**
     * Create/Update module
     * @param array $params module creation parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and module id
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_updated', 'module_does_not_exist',
            'module_not_created', 'module_already_exists', 'faculty_not_supplied'));
        $faculty = true;
        if (!empty($params['id'])) {
            $moduleid = \module_utils::get_moduleid_from_id($params['id'], $this->db);
            if ($moduleid) {
                $details = \module_utils::get_full_details_by_ID($params['id'], $this->db);
            }
        } else {
            $params['id'] = false;
            $moduleid = false;
        }
        // Get school id if school name provided.
        if (!empty($params['school'])) {
            $schoolid = \SchoolUtils::school_name_exists($params['school'], $this->db);
            if (!$schoolid) {
                if (isset($params['faculty']) and $params['faculty'] !== '') {
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
            if ($moduleid and (empty($params['modulecode']))) {
                $params['modulecode'] = $details['moduleid'];
            }
            
            // Get name if not provided.
            if ($moduleid and (empty($params['name']))) {
                $params['name'] = $details['fullname'];
            }
            
            // Get student management system if not provided.
            if ($moduleid and (empty($params['sms']))) {
                $params['sms'] = $details['sms'];
            }
            
            // Update Module.
            if ($params['id']) {
                if ($moduleid) {
                    $update = \module_utils::update_module_by_id($params['id'], $moduleid, 
                        $params['name'], $schoolid, $params['sms'], $this->db);
                    if ($update) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['MODULE_NOT_UPDATED'], 'status' => $strings['module_not_updated'], 'id' => null);
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_DOES_NOT_EXIST'], 'status' => $strings['module_does_not_exist'], 'id' => null);
                }
            // Create Module.
            } else {
                $moduleid = \module_utils::get_idMod($params['modulecode'], $this->db);
                if (!$moduleid) {
                    if (empty($params['sms'])) {
                        $params['sms'] = 'rogo webservice';
                    }
                    $id = \module_utils::add_modules($params['modulecode'], $params['name'], 1, $schoolid, '', $params['sms'],
                        '', false, false, false, false, '', '', $this->db, false, '', '', '', 0);
                    if ($id) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['MODULE_NOT_CREATED'], 'status' => $strings['module_not_created'], 'id' => null);
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_ALREADY_EXISTS'], 'status' => $strings['module_already_exists'], 'id' => $moduleid);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_FACULTY'], 'status' => $strings['faculty_not_supplied'], 'id' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }

    /**
     * Delete module
     * @param array $parms delete module parameters
     * @param integer $userid rogo user id linked to web service client
     * @return success status and module id
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_deleted_inuse', 'module_not_deleted',
            'module_does_not_exist'));
        if (!empty($params['id'])) {
            $moduleid = \module_utils::get_moduleid_from_id($params['id'], $this->db);
        } else {
            $moduleid = false;
        }
        if ($moduleid) {
             // Only delete module if it contains no enrolments, and no papers
            $inuse = \module_utils::module_in_use($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['MODULE_NOT_DELETED_INUSE'], 'status' => $strings['module_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \module_utils::delete_module($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_NOT_DELETED'], 'status' => $strings['module_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['MODULE_DOES_NOT_EXIST'], 'status' => $strings['module_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}