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
* Assessment api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Assessment class
 */
class assessmentmanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/assessmentmanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'PAPER_NOT_DELETED' => 200,
        'PAPER_DOES_NOT_EXIST' => 201,
        'PAPER_NOT_DELETED_INUSE' => 202,
        'PAPER_NOT_CREATED' => 203,
        'PAPER_NOT_SCHEDULED' => 204,
        'PAPER_INVALID_TITLE' => 205,
        'PAPER_INVALID_OWNER' => 206,
        'PAPER_INVALID_ROLE' => 207,
        'PAPER_INVALID_YEAR' => 208,
        'PAPER_INVALID_PAPER' => 209,
        'PAPER_INVALID_MODULES' => 210,
        'PAPER_INVALID_START' => 211,
        'PAPER_NOT_UPDATED' => 210,
        'PAPER_SCHEDULE_SUMMATIVE' => 211
    );
    
    /**
     * Create/Update assessment
     * @param array $parms create assessment parameters
     * @return assessment id and status
     */
    public function create($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_created', 'paper_calendar_year_invalid'
            , 'paper_owner_role_invalid', 'paper_owner_does_not_exist', 'paper_title_inuse', 'paper_scheduled_summative'
            , 'paper_does_not_exist', 'paper_startdate_invalid' , 'paper_not_updated', 'paper_invalid_module', 'paper_invalid_lab'
            , 'paper_module_error'));
        $error = array();
        $configObject = \Config::get_instance();
        // Error if trying to create a summative exam when they are set to be scheduled only.
        if ($configObject->get('cfg_summative_mgmt') and $params['type'] == 'summative') {
            $data = array('statuscode' => $this->statuscodes['PAPER_SCHEDULE_SUMMATIVE'], 'status' => $strings['paper_scheduled_summative'], 'id' => null);
            return $this->get_response($data, 'create', $params['nodeid'], $error);
        } 
        if (!empty($params['id'])) {
            $paperid = \Paper_utils::paper_exists($params['id'], $this->db);
            // Get current paper properties.
            if ($paperid) {
                $details = \Paper_utils::get_paper_properties($params['id'], $this->db);
            }
        } else {
            $paperid = false;
        }
        
        // Get title if not provided.
        if ($paperid and (empty($params['title']))) {
            $params['title'] = $details['title'];
            $uniquetitle = true;
        } else {
            // If updating and not changing the title do not check.
            if ($paperid and $params['title'] === $details['title']) {
                $uniquetitle = true;
            } else {
                // Check paper title.
                $uniquetitle = \Paper_utils::is_paper_title_unique($params['title'], $this->db);
                if (!$uniquetitle) {
                    $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_TITLE'], 'status' => $strings['paper_title_inuse'], 'id' => null);
                }
            }
        }
        // Get owner if not provided.
        if ($paperid and (empty($params['owner']))) {
            $params['owner'] = $details['owner'];
            $userid = true;
            $staff = true;
        } else {
            // Check owner exists.
            $userid = \UserUtils::userid_exists($params['owner'], $this->db);
            if (empty($data) and !$userid) {
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_OWNER'], 'status' => $strings['paper_owner_does_not_exist'], 'id' => null);
            }
            // Check owners role.
            $staff = \UserUtils::has_user_role($params['owner'], 'Staff', $this->db);
            if (empty($data) and !$staff) {
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_ROLE'], 'status' => $strings['paper_owner_role_invalid'], 'id' => null);
            }
        }
        // Get session if not provided.
        if ($paperid and (empty($params['session']))) {
            $params['session'] = $details['session'];
            $validsession = true;
        } else {
            // Check session.
            $yearutils = new \yearutils($this->db);
            $validsession = array_key_exists($params['session'], $yearutils->get_supported_years());
            if (empty($data) and !$validsession) {
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_YEAR'], 'status' => $strings['paper_calendar_year_invalid'], 'id' => null);
            }
        }
        // Get start datetime if not provided.
        $dateformat = 'Y-m-d H:i:s';
        if ($paperid and (empty($params['startdatetime']))) {
            $start = $details['startdatetime'];
        } else {
            $start = date($dateformat, strtotime($params['startdatetime']));
        }
        if ($paperid and (empty($params['enddatetime']))) {
            $end = $details['enddatetime'];
        } else {
            $end = date($dateformat, strtotime($params['enddatetime']));
        }
        // Check start/enddates
        if (empty($data) and $end <= $start) {
            $validstart = false;
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_START'], 'status' => $strings['paper_startdate_invalid'], 'id' => null);
        } else {
            $validstart = true;
        }
        if (empty($data)) {
            // Check modules
            $modulesarray = array();
            if (count($params['modules']) > 0) {
                foreach ($params['modules'] as $module) {
                    $moduleid = \module_utils::get_moduleid_from_id($module['value'], $this->db);
                    if ($moduleid) {
                        $modulesarray[] = $module['value'];
                    } else {
                        $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                    }
                }
            }
            if (count($error) > 0) {
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_MODULES'], 'status' => $strings['paper_module_error'], 'id' => null);
            } else {
                if ($paperid and (empty($params['labs']))) {
                    $params['labs'] = $details['labs'];
                } else {
                    // Check labs.
                    // Currently we are working with the lab name as there is no lab management web service.
                    $labfactory = new \LabFactory($this->db);
                    $labsarray = array();
                    if (count($params['labs']) > 0) {
                        foreach ($params['labs'] as $lab) {
                            // We allow empty lab elements so labs so the paper can have all labs removed.
                            if ($lab['value'] != '') {
                                $labid = $labfactory->get_lab_id($lab['value']);
                                if ($labid) {
                                    $labsarray[] = $labid;
                                } else {
                                    $error[$lab['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_lab'), $lab['value']);
                                }
                            } else {
                                $labsarray[] = '';
                            }
                        }
                    }
                    if (count($labsarray) > 0) {
                        $labs = implode(',', $labsarray);
                    } else {
                        $labs = '';
                    }
                }
                if ($paperid and (empty($params['duration']))) {
                    $params['duration'] = $details['duration'];   
                }       
                $paper = new \assessment($this->db, $configObject);
                // Update exam.
                if ($params['id']) {
                    if ($paperid) {
                        $id = $paper->update($params['id'], $params['title'], $params['owner'], $start,
                            $end, $labs, $params['duration'], $params['session'], $modulesarray, $params['timezone']);
                        if ($id) {
                            $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $paperid, 'error' => $error);
                        } else {
                            $data = array('statuscode' => $this->statuscodes['PAPER_NOT_UPDATED'], 'status' => $strings['paper_not_updated'], 'id' => null);
                        }
                    } else {
                        $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_PAPER'], 'status' => $strings['paper_does_not_exist'], 'id' => null);
                    }
                // Create exam.
                } else {
                    $id = $paper->create($params['title'], $params['type'], $params['owner'], $start,
                        $end, $labs, $params['duration'], $params['session'], $modulesarray, $params['timezone']);
                    if ($id) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'error' => $error);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['PAPER_NOT_CREATED'], 'status' => $strings['paper_not_created'], 'id' => null);
                    }
                }
            }
        }
        
        return $this->get_response($data, 'create', $params['nodeid'], $error);
    }
    
    /**
     * Schedule a summative assessment
     * @param array $parms schedule summative parameters
     * @return summative assessment id and status
     */
    public function schedule($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_title_inuse', 'paper_calendar_year_invalid', 'paper_not_created',
            'paper_not_scheduled', 'paper_owner_does_not_exist', 'paper_owner_role_invalid', 'paper_invalid_module'));
        $error = array();
        $params['type'] = 'summative';
        // Check paper title.
        $uniquetitle = \Paper_utils::is_paper_title_unique($params['title'], $this->db);
        if (!$uniquetitle) {
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_TITLE'], 'status' => $strings['paper_title_inuse'], 'id' => null);
        }
        // Check owner exists.
        $userid = \UserUtils::userid_exists($params['owner'], $this->db);
        if (empty($data) and !$userid) {
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_OWNER'], 'status' => $strings['paper_owner_does_not_exist'], 'id' => null);
        }
        // Check owners role.
        $staff = \UserUtils::has_user_role($params['owner'], 'Staff', $this->db);
        if (empty($data) and !$staff) {
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_ROLE'], 'status' => $strings['paper_owner_role_invalid'], 'id' => null);
        }
        // Check session.
        $yearutils = new \yearutils($this->db);
        $validsession = array_key_exists($params['session'], $yearutils->get_supported_years());
        if (empty($data) and !$validsession) {
            $data = array('statuscode' =>$this->statuscodes['PAPER_INVALID_YEAR'], 'status' => $strings['paper_calendar_year_invalid'], 'id' => null);
        }
        if (empty($data)) {
            // Check modules
            $modulesarray = array();
            if (count($params['modules']) > 0) {
                foreach ($params['modules'] as $module) {
                    $moduleid = \module_utils::get_moduleid_from_id($module['value'], $this->db);
                    if ($moduleid) {
                        $modulesarray[] = $module['value'];
                    } else {
                        $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                    }
                }
            }
            $labs = '';
            $start = '';
            $end = '';
            $configObject = \Config::get_instance();
            $paper = new \assessment($this->db, $configObject);
            // Create.
            $paperid = $paper->create($params['title'], $params['type'], $params['owner'], $start,
                $end, $labs, $params['duration'], $params['session'], $modulesarray);
            if ($paperid) {
                // Schedule.
                $params['month'] = ltrim($params['month'], '-');
                $id = $paper->schedule($paperid, $params['month'], $params['barriers'], $params['cohort_size'], $params['notes'], $params['sittings'], $params['campus']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $paperid, 'error' => $error);
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_NOT_SCHEDULED'], 'status' => $strings['paper_not_scheduled'], 'id' => null);
                    // Not scheduled so remove new properties entry from db.
                    \Paper_utils::complete_delete_paper($paperid, $this->db);
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOT_CREATED'], 'status' => $strings['paper_not_created'], 'id' => null);
            }
        }
        return $this->get_response($data, 'schedule', $params['nodeid'], $error);
    }

    /**
     * Delete assessment
     * @param array $parms delete assessment parameters
     * @return assessment id and status
     */
    public function delete($params) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_deleted_inuse', 'paper_not_deleted'
            , 'paper_does_not_exist'));
        if (!empty($params['id'])) {
            $paperid = \Paper_utils::paper_exists($params['id'], $this->db);
        }
        if ($paperid) {
            // Only delete assessment if no one has taken the paper.
            $inuse = \Paper_utils::paper_taken($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOT_DELETED_INUSE'], 'status' => $strings['paper_not_deleted_inuse'], 'id' => null);
            } else {
                $deleted = \Paper_utils::delete_paper($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_NOT_DELETED'], 'status' => $strings['paper_not_deleted'], 'id' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['PAPER_DOES_NOT_EXIST'], 'status' => $strings['paper_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}