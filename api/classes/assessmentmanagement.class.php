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
        'PAPER_GENERAL_ERROR' => 200,
        'PAPER_NOT_DELETED' => 201,
        'PAPER_DOES_NOT_EXIST' => 202,
        'PAPER_NOT_DELETED_INUSE' => 203,
        'PAPER_NOT_CREATED' => 204,
        'PAPER_NOT_SCHEDULED' => 205,
        'PAPER_INVALID_TITLE' => 206,
        'PAPER_INVALID_OWNER' => 207,
        'PAPER_INVALID_ROLE' => 208,
        'PAPER_INVALID_YEAR' => 209,
        'PAPER_INVALID_PAPER' => 210,
        'PAPER_INVALID_MODULES' => 211,
        'PAPER_INVALID_START' => 212,
        'PAPER_NOT_UPDATED' => 213,
        'PAPER_SCHEDULE_SUMMATIVE' => 214,
        'PAPER_INVALID_TYPE' => 215
    );
    
    /**
     * Handle thrown exceptions
     * @param string $exception - the thrown exception
     * @return array containg the relevant status code and status message
     */
    private function handle_exception($exception) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_calendar_year_invalid', 'paper_owner_role_invalid',
            'paper_owner_does_not_exist', 'paper_title_inuse', 'paper_startdate_invalid', 'paper_general_error','paper_type_invalid'));
        switch ($exception) {
            case 'NON_UNIQUE_TITLE':
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_TITLE'], 'status' => $strings['paper_title_inuse'], 'id' => null);
                break;
            case 'INVALID_PAPER_TYPE':
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_TYPE'], 'status' => $strings['paper_type_invalid'], 'id' => null);
                break;
            case 'INVALID_USER':
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_OWNER'], 'status' => $strings['paper_owner_does_not_exist'], 'id' => null);
                break;
            case 'INVALID_ROLE':
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_ROLE'], 'status' => $strings['paper_owner_role_invalid'], 'id' => null);
                break;
            case 'INVALID_SESSION':
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_YEAR'], 'status' => $strings['paper_calendar_year_invalid'], 'id' => null);
                break;
            case 'INVALID_DATES':
                $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_START'], 'status' => $strings['paper_startdate_invalid'], 'id' => null);
                break;
            default:
                $data = array('statuscode' => $this->statuscodes['PAPER_GENERAL_ERROR'], 'status' => $strings['paper_general_error'], 'id' => null);
                break;
        }
        return $data;
    }
    
    /**
     * Create/Update assessment
     * @param array $parms create assessment parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array assessment id and status
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_created', 'paper_scheduled_summative', 'paper_does_not_exist',
            'paper_not_updated', 'paper_invalid_module', 'paper_invalid_lab', 'paper_module_error',));
        $error = array();
        $configObject = \Config::get_instance();
        $paper = new \assessment($this->db, $configObject);
        $papertype = $paper->get_type_value($params['type']);
        // Error if trying to create a summative exam when they are set to be scheduled only.
        if ($configObject->get('cfg_summative_mgmt') and $papertype == $paper::TYPE_SUMMATIVE) {
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
        
        if ($paperid) {
            // Get title if not provided.
            if (empty($params['title'])) {
                $params['title'] = $details['title'];
            }
            // Get owner if not provided.
            if (empty($params['owner'])) {
                $params['owner'] = $details['owner'];
            }
            // Get session if not provided.
            if (empty($params['session'])) {
                $params['session'] = $details['session'];
            }
            // Get start datetime if not provided.
            if (empty($params['startdatetime'])) {
                $params['startdatetime'] = $details['startdatetime'];
            }
            // Get end datetime if not provided.
            if (empty($params['enddatetime'])) {
                $params['enddatetime'] = $details['enddatetime'];
            }
             // Get end timezone if not provided.
            if (empty($params['timezone'])) {
                $params['timezone'] = $details['timezone'];
            }
        }
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
            
            // Update exam.
            if ($params['id']) {
                if ($paperid) {
                    try {
                        $id = $paper->update($params['id'], $params['title'], $details['type'], $params['owner'], $params['startdatetime'],
                            $params['enddatetime'], $labs, $params['duration'], $params['session'], $modulesarray, $params['timezone'], $userid);
                        if ($id) {
                            $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'error' => $error);
                        } else {
                            $data = array('statuscode' => $this->statuscodes['PAPER_NOT_UPDATED'], 'status' => $strings['paper_not_updated'], 'id' => null);
                        }
                    } catch (\Exception $e) {
                        $data = $this->handle_exception($e->getMessage());
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_PAPER'], 'status' => $strings['paper_does_not_exist'], 'id' => null);
                }
            // Create exam.
            } else {
                try {
                    $id = $paper->create($params['title'], $papertype, $params['owner'], $params['startdatetime'],
                        $params['enddatetime'], $labs, $params['duration'], $params['session'], $modulesarray, $params['timezone']);
                    if ($id) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'error' => $error);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['PAPER_NOT_CREATED'], 'status' => $strings['paper_not_created'], 'id' => null);
                    }
                } catch (\Exception $e) {
                    $data = $this->handle_exception($e->getMessage());
                }
            }
        }
        
        return $this->get_response($data, 'create', $params['nodeid'], $error);
    }
    
    /**
     * Schedule a summative assessment
     * @param array $parms schedule summative parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array summative assessment id and status
     */
    public function schedule($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_created', 'paper_not_scheduled', 'paper_invalid_module'));
        $error = array();
        $configObject = \Config::get_instance();
        $paper = new \assessment($this->db, $configObject);
        $papertype = $paper::TYPE_SUMMATIVE;

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
        // Create.
        try {
            $paperid = $paper->create($params['title'], $papertype, $params['owner'], $start,
                $end, $labs, $params['duration'], $params['session'], $modulesarray, $configObject->get('cfg_timezone'));
            if ($paperid) {
                // Schedule.
                $params['month'] = ltrim($params['month'], '-');
                $id = $paper->schedule($paperid, $params['month'], $params['barriers'], $params['cohort_size'], $params['notes'], $params['sittings'], $params['campus']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $paperid, 'error' => $error);
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_NOT_SCHEDULED'], 'status' => $strings['paper_not_scheduled'], 'id' => null);
                    // Not scheduled so remove new properties entry from db.
                    if (!\Paper_utils::complete_delete_paper($paperid, $this->db)) {
                        // Log warning to system if delete failed, as we want to clean up orhpaned papers.
                        $type = 'Assessment Management';
                        $errorstring = 'Error deleting unscheduled paper';
                        $errorfile = $_SERVER['PHP_SELF'];
                        $errorline = __LINE__ - 5;
                        $logger = new \logger($this->db);
                        $logger->record_application_warning($userid, $type, $errorstring, $errorfile, $errorline);
                    }
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOT_CREATED'], 'status' => $strings['paper_not_created'], 'id' => null);
            }
        } catch (\Exception $e) {
            $data = $this->handle_exception($e->getMessage());
        }
        
        return $this->get_response($data, 'schedule', $params['nodeid'], $error);
    }

    /**
     * Delete assessment
     * @param array $parms delete assessment parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array assessment id and status
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_deleted_inuse', 'paper_not_deleted'
            , 'paper_does_not_exist'));
        if (!empty($params['id'])) {
            $paperexists = \Paper_utils::paper_exists($params['id'], $this->db);
        } else {
            $paperexists = false;
        }
        if ($paperexists) {
            // Only delete assessment if no one has taken the paper.
            $inuse = \Paper_utils::paper_taken($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOT_DELETED_INUSE'], 'status' => $strings['paper_not_deleted_inuse'], 'id' => null);
            } else {
                $details = \Paper_utils::get_paper_properties($params['id'], $this->db);
                $deleted = \Paper_utils::delete_paper($params['id'], $details['owner'], $this->db);
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