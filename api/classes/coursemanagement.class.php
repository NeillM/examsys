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
* Course api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Course class
 */
class coursemanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/coursemanagement';
    
    /**
     * Create/Update course
     * @param array $params course creation parameters
     * @return - success status and course id
     */
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('course_not_updated', 'course_does_not_exist'
            , 'course_not_created', 'course_already_exists', 'faculty_not_supplied'));
        $faculty = true;
        if (isset($params['id']) and $params['id'] !== '') {
            $courseid = \CourseUtils::courseid_exists($params['id'], $this->db);
            if ($courseid) {
                $details = \CourseUtils::get_course_details_by_id($params['id'], $this->db);
            }
        } else {
            $params['id'] = false;
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
        } else if($courseid) {
            $schoolid = $details['schoolid'];
        }
        
        // If creating/updating module with a new school, faculty needs to be supplied.
        if ($faculty) {
            // Get description if not provided.
            if ($courseid and (!isset($params['description']) or $params['description'] == '')) {
                $params['description'] = $details['description'];
            }
            
            // Get name if not provided.
            if ($courseid and (!isset($params['name']) or $params['name'] == '')) {
                $params['name'] = $details['name'];
            }
            
            // Update Course.
            if ($params['id']) {
                if ($courseid) {
                    $update = \CourseUtils::update_course($params['id'], $schoolid, $params['name'], $params['description'], $this->db);
                    if ($update) {
                        $data = array('status' => 'OK', 'id' => $params['id']);
                    } else {
                        $data = array('status' => $strings['course_not_updated'], 'id' => null);
                    }
                } else {
                    $data = array('status' => $strings['course_does_not_exist'], 'id' => null);
                }
            // Create Course.
            } else {
                $courseid = \CourseUtils::get_course_id($params['name'], $this->db);
                if (!$courseid) {
                    $id = \CourseUtils::add_course($schoolid, $params['name'], $params['description'], $this->db);
                    if ($id) {
                        $data = array('status' => 'OK', 'id' => $id);
                    } else {
                        $data = array('status' => $strings['course_not_created'], 'id' => null);
                    }
                } else {
                    $data = array('status' => $strings['course_already_exists'], 'id' => $courseid);
                }
            }
        } else {
            $data = array('status' => $strings['faculty_not_supplied'], 'id' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * Delete course
     * @param array $parms delete course parameters
     * @return success status and course id 
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('course_not_deleted_inuse', 'course_not_deleted'
            , 'course_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            $courseid = \CourseUtils::courseid_exists($params['id'], $this->db);
            $details = \CourseUtils::get_course_details_by_id($params['id'], $this->db);
        } else {
            $params['id'] = false;
        }
        if ($courseid) {
            // Only delete course if it contains no users.
            $users = \CourseUtils::get_users_on_course($details['name'], $this->db);
            if (isset($users) and $users > 0) {
                $data = array('status' => $strings['course_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \CourseUtils::delete_course_by_id($params['id'],$this->db);
                if ($deleted) {
                    $data = array('status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('status' => $strings['course_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('status' => $strings['course_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}