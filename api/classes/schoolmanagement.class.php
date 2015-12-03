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
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/schoolmanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'SCHOOL_NOT_DELETED' => 600,
        'SCHOOL_DOES_NOT_EXIST' => 601,
        'SCHOOL_NOT_UPDATED' => 602,
        'SCHOOL_NOT_CREATED' => 603,
        'SCHOOL_NOT_DELETED_INUSE' => 604
        'SCHOOL_FACULTY_INVALID' => 605
        'SCHOOL_ALREADY_EXISTS' => 606
    );
        
    /**
     * Create/Update school
     * @param array $params school creation parameters
     * @return - success status and school id
     */
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_updated', 'school_does_not_exist'
            , 'school_created', 'school_alreads_exists', 'faculty_not_supplied'));
        $faculty = true;
        if (isset($params['id']) and $params['id'] !== '') {
            $schoolid = \SchoolUtils::schoolid_exists($params['id'], $this->db);
            if ($schoolid) {
                $details = \SchoolUtils::get_school_details_by_id($params['id'], $this->db);
            }
        } else {
            $params['id'] = false;
            $schoolid = false;
        }
        
        // Get name if not provided.
        if ($schoolid and (!isset($params['name']) or $params['name'] === '')) {
            $params['name'] = $details['name'];
        }
        
        // Get faculty if provided.
        if (isset($params['faculty']) and $params['faculty'] !== '') {
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
            if ($params['id']) {
                if ($schoolid) {
                    $update = \SchoolUtils::update_school($params['id'], $facultyid, $params['name'], $this->db);
                    if ($update) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_UPDATED'], 'status' => $strings['school_not_updated'], 'id' => null);
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['SCHOOL_DOES_NOT_EXIST'], 'status' => $strings['school_does_not_exist'], 'id' => null);
                }
            // Create school.
            } else {
                $schoolid = \SchoolUtils::get_school_id_by_name($params['name'], $this->db);
                if (!$schoolid) {
                    $id = \SchoolUtils::add_school($facultyid, $params['name'], $this->db);
                    if ($id) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_CREATED'], 'status' => $strings['school_not_created'], 'id' => null);
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['SCHOOL_ALREADY_EXISTS'], 'status' => $strings['school_already_exists'], 'id' => $schoolid);
                }
            }
        } else {
            if (!$schoolid) {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_DOES_NOT_EXIST'], 'status' => $strings['school_does_not_exist'], 'id' => null);
            } else {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_FACULTY_INVALID'], 'status' => $strings['faculty_not_supplied'], 'id' => null);
            }
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * Delete school
     * @param array $parms delete school parameters
     * @return success status and school id
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_deleted_inuse', 'school_not_deleted'
            , 'school_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            $schoolid = \SchoolUtils::schoolid_exists($params['id'], $this->db);
        } else {
            $params['id'] = false;
        }
        if ($schoolid) {
            // Only delete school if it contains no modules or courses.
            $inuse = \SchoolUtils::school_in_use($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_DELETED_INUSE'], 'status' => $strings['school_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \SchoolUtils::delete_school($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_DELETED'], 'status' => $strings['school_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['SCHOOL_DOES_NOT_EXIST'], 'status' => $strings['school_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}