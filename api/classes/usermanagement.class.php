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
* Course enrolment api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Person class
 */
class usermanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/usermanagement';
        
    /**
     * Enrol users onto modules.
     * @param integer $id - user id
     * @param array $modules - modules to (un)enrol user
     * @param string $role  - role of user
     * @return array error status
     */
    private function user_modules($id, $modules, $role) {
        $langpack = new \langpack();
        $error = array();
        $yearutils = new \yearutils($this->db);
        $session = $yearutils->get_current_session();
        if (count($modules) > 0) {
            foreach ($modules as $module) {
                if ($module['name'] == 'moduleid') {
                    if ($role == 'Student') {
                        $enrol = \UserUtils::add_student_to_module($id, $module['value'], 1, $session, $this->db, 1);
                        if (!$enrol) {   
                            $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'enrol_onto_module_fail'), $module['value']);
                        }
                    } elseif ($role == 'Staff') {
                        $enrol = \UserUtils::add_staff_to_module($id, $module['value'], $this->db);
                        if (!$enrol) {
                            $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'enrol_onto_module_fail'), $module['value']);
                        }
                    }
                }
            }
        }
        return $error;
    }
    
    /**
     * Return response to request
     * @param array $data - Response data
     * @param string $action - Relevant action
     * @param integer $nodeid - Request Node id
     * @param array $error - array of errors generated
     * return array response to operation, id of construct or error message.
     */
    public function get_response($data, $action, $nodeid, $error) {
        return $response = array(
            "status" => $data['status'],
            "id" => $data['id'],
            "error" => $error,
            "node" => $action,
            "nodeid" => $nodeid);
    }
    
    /**
     * Create/Update user
     * @param array $params create user params
     * @return - success status and user id
     */ 
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_invalid_role', 'user_does_not_exist'
            , 'user_not_updated', 'user_not_created', 'course_does_not_exist'));
        $error = array();
        $userid = false;
        // Student and Staff users only.
        $studentroles = array('Student', 'Left', 'Graduate');
        $staffroles = array('Staff', 'Inactive Staff');
        $roles = array_merge($studentroles, $staffroles);
        
        if (isset($params['id']) and $params['id'] !== '') {
            $userid = \UserUtils::userid_exists($params['id'], $this->db);
            if ($userid) {
                $details = \UserUtils::get_full_details_by_ID($params['id'], $this->db);
            }
        } else {
            $params['id'] = false;
        }
        
        if ($userid) {
            // Set defaults if not provided.
            $paramnames = array('username', 'password', 'title', 'forename', 'surname', 'email', 'course',
                'gender', 'year', 'role', 'studentid', 'initials');
            foreach ($paramnames as $name) {
                if (!isset($params[$name]) or $params[$name] === '') {
                    $params[$name] = $details[$name];
                }
            }
        } 
        
        if (!$userid and $params['id']) {
            $data = array('status' => $strings['user_does_not_exist'], 'id' => null);
        } else {
            if (!in_array($params['role'], $roles)) {
                $data = array('status' => $strings['user_invalid_role'], 'id' => null);
            } else {
                // Students.
                if (in_array($params['role'], $studentroles)) {
                    $course = \CourseUtils::course_exists($params['course'], $this->db);
                // Staff.
                } else {
                    $staffcourses = array('University Lecturer', 'NHS Lecturer');
                    if (in_array($params['course'], $staffcourses)) {
                        $course = $params['course'];
                    } else {
                        $course = false;
                    }
                }

                if ($course) {
                    // Update.
                    if ($params['id']) {
                        $update = \UserUtils::update_user($params['id'], $params['username'], $password, $params['title'],
                                    $params['forename'], $params['surname'], $params['email'], $params['course'],
                                    $params['gender'], $params['year'], $params['role'], $params['studentid'], $this->db, $params['initials']);
                        if ($update) {
                            $error = $this->user_modules($params['id'], $params['modules'], $params['role']);
                            $data = array('status' => 'OK', 'id' => $params['id']);
                        } else {
                            $data = array('status' => $strings['user_not_updated'], 'id' => null);
                        }
                    // Create.
                    } else {
                        $id = \UserUtils::create_user($params['username'], $password, $params['title'],
                            $params['forename'], $params['surname'], $params['email'], $params['course'],
                            $params['gender'], $params['year'], $params['role'], $params['studentid'], $this->db, $params['initials']);
                        if ($id) {
                            $error = $this->user_modules($id, $params['modules'], $params['role']);
                            $data = array('status' => 'OK', 'id' => $id, 'error' => $error);
                        } else {
                            $data = array('status' => $strings['user_not_created'], 'id' => null);
                        }
                    } 
                } else {
                    $data = array('status' => $strings['course_does_not_exist'], 'id' => null);
                }
            }
        }
        return $this->get_response($data, 'create', $params['nodeid'], $error);
    }
 
    /**
     * Delete user
     * @param array $parms delete user parameters
     * @return  
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_paper_exists' ,'user_not_deleted',
            'user_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            $userid = \UserUtils::userid_exists($params['id'], $this->db);
        } else {
            $params['id'] = false;
        }
        if ($userid) {
            // Only delete user they have taken no papers
            $inuse = \UserUtils::user_paper_started($params['id'], $this->db);
            if ($inuse) {
                $data = array('status' => $strings['user_paper_exists'], 'id' => null);
            } else {
                $deleted = \UserUtils::delete_userID($params['id'], $this->db);
                if ($deleted) {
                    $data = array('status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('status' => $strings['user_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('status' => $strings['user_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }  
}