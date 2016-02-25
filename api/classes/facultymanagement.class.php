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
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/facultymanagement';
       
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'FACUTLY_NOT_DELETED' => 400,
        'FACUTLY_DOES_NOT_EXIST' => 401,
        'FACUTLY_NOT_UPDATED' => 402,
        'FACUTLY_NOT_CREATED' => 403,
        'FACUTLY_NOT_DELETED_INUSE' => 404,
        'FACUTLY_ALREADY_EXISTS' => 405,
        'FACUTLY_NAME_NOT_SUPPLIED' => 406
    );
    
    /**
     * Create/Update faculty
     * @param array $params faculty creation parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and faculty id
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_updated', 'faculty_does_not_exist'
            , 'faculty_not_created', 'faculty_already_exists', 'faculty_name_not_supplied'));
        if (isset($params['id']) and $params['id'] !== '') {
            $facultyid = \FacultyUtils::faculty_name_by_id($params['id'], $this->db);
            if ($facultyid) {
                $details = \FacultyUtils::get_faculty_details_by_id($params['id'], $this->db);
            }
        } else {
            $params['id'] = false;
        }
        
        // Name must be supplied.
        if (!isset($params['name']) or $params['name'] === '') {
            $data = array('statuscode' => $this->statuscodes['FACUTLY_NAME_NOT_SUPPLIED'], 'status' => $strings['faculty_name_not_supplied'], 'id' => null);
        } else {
            // Update faculty.
            if ($params['id']) {
                if ($facultyid) {
                    $update = \FacultyUtils::update_faculty($params['id'], $params['name'],  $this->db);
                    if ($update) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_UPDATED'], 'status' => $strings['faculty_not_updated'], 'id' => null);
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['FACUTLY_DOES_NOT_EXIST'], 'status' => $strings['faculty_does_not_exist'], 'id' => null);
                }
            // Create faculty.
            } else {
                $facultyid = \FacultyUtils::facultyid_by_name($params['name'], $this->db);
                if (!$facultyid) {
                    $id = \FacultyUtils::add_faculty($params['name'], $this->db);
                    if ($id) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_CREATED'], 'status' => $strings['faculty_not_created'], 'id' => null);
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['FACUTLY_ALREADY_EXISTS'], 'status' => $strings['faculty_already_exists'], 'id' => $facultyid);
                }
            }
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * Delete faculty
     * @param array $parms delete faculty parameters
     * @param integer $userid rogo user id linked to web service client
     * @return success status and faculty id 
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_deleted_inuse', 'faculty_not_deleted'
            , 'faculty_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            $facultyid = \FacultyUtils::faculty_name_by_id($params['id'], $this->db);
        } else {
            $facultyid = false;
        }
        if ($facultyid) {
            // Only delete faculty if it contains no schools.
            $schools = \FacultyUtils::count_schools_in_faculty($params['id'], $this->db);
            if (isset($schools) and $schools > 0) {
                $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_DELETED_INUSE'], 'status' => $strings['faculty_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \FacultyUtils::delete_faculty($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_DELETED'], 'status' => $strings['faculty_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['FACUTLY_DOES_NOT_EXIST'], 'status' => $strings['faculty_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}