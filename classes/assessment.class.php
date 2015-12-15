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
* Assessment package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Assessment helper class.
 */
class assessment {
    
    /**
     * Formative paper type
     */
    const TYPE_FORMATIVE = 0;
    /**
     * Progress paper type
     */
    const TYPE_PROGESSS = 1;
    /**
     * Summative paper type
     */
    const TYPE_SUMMATIVE = 2;
    /**
     * Survety paper type
     */
    const TYPE_SURVEY = 3;
    /**
     * OSCE paper type
     */
    const TYPE_OSCE = 4;
    /**
     * Offline paper type
     */
    const TYPE_OFFLINE = 5;
    /**
     * Peer revoew paper type
     */
    const TYPE_PEERREVIEW = 6;

    // DB connection
    private $db;
    
    // Cenrtalised summative management?
    private $summative_mgmt;
    
    // Server time zone.
    private $server_timezone;
    
    // Supported time zones.
    private $timezones;
    
    // Supported cohort sizes
    private $cohort_sizes;
    
    // Max paper duration
    private $max_duration;
       
    // Paper type name and keys
    private $type;
        
    // Maximum number of exam sittings
    private $max_sittings;
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'classes/assessment';

    /**
     * @brief Constuctor
     * @param mysqli $db 
     * @param object $configObject 
     */
    function __construct($db, $configObject) {
        $this->db = $db;
        $this->summative_mgmt = $configObject->get('cfg_summative_mgmt');
        $this->server_timezone = $configObject->get('cfg_timezone');
        $this->type = array('formative' => self::TYPE_FORMATIVE,
            'progress' => self::TYPE_PROGESSS,
            'summative' => self::TYPE_SUMMATIVE,
            'survey' => self::TYPE_SURVEY,
            'osce' => self::TYPE_OSCE,
            'offline' => self::TYPE_OFFLINE,
            'peer_review' => self::TYPE_PEERREVIEW);
        $configObject->set_db_object($db);
        $configObject->load_settings('core');
        $settings = (object) $configObject->get_setting('core');
        $this->timezones = $settings->timezones;
        $this->cohort_sizes = $settings->cohort_sizes;
        $this->max_duration = $settings->max_duration;
        $this->max_sittings = $settings->max_sittings;
    }
    
    /**
     * Get the numeric value of the paper type
     * @param string $type paper type
     * @return int|bool value or false on error
     */
    public function get_type_value($type) {
        if (array_key_exists($type, $this->type)) {
            return $this->type[$type];
        } else {
            return false;
        }
    }
    
    /**
     * Create an assesment
     * @param string $papertitle - New paper title
     * @param string $papertype - Type of paper
     * @param integer $paperowner - Owner of paper
     * @param string $startdate - Start date of paper
     * @param string $enddate  - End date of paper
     * @param array $labs - Labs the paper can be taken in
     * @param integer $duration - Length of time associated with the paper
     * @param string $session - Academic session the paper is relevant to
     * @param array $modules - Modules that have the paper available to them
     * @param string $timezone - timezone paper is being taken in
     * @return integer|bool - id of new assessment or false on error
     */
    public function create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone) {
       
        // Check title is unique.
        $uniquetitle = Paper_utils::is_paper_title_unique($papertitle, $this->db);
        if (!$uniquetitle) {
            throw new Exception('NON_UNIQUE_TITLE');
        }
        // Check paper type is valid.
        if (!in_array($papertype, $this->type)) {
            throw new Exception('INVALID_PAPER_TYPE');
        }
        // Check owner exists.
        $userid = UserUtils::userid_exists($paperowner, $this->db);
        if (!$userid) {
            throw new Exception('INVALID_USER');
        } else {
            // Check owners role.
            $staff = UserUtils::has_user_role($paperowner, 'Staff', $this->db);
            if (!$staff) {
                throw new Exception('INVALID_ROLE');
            }
        }
        // Check session.
        $yearutils = new yearutils($this->db);
        $validsession = array_key_exists($session, $yearutils->get_supported_years());
        if (!$validsession) {
             throw new Exception('INVALID_SESSION');
        }
        // Check startdate and enddate
        if ($papertype != self::TYPE_SUMMATIVE and $enddate <= $startdate) {
            throw new Exception('INVALID_DATES');
        }
        // Verify timezone is supported, revert to server timezone if not.
        $decode_timezones = json_decode($this->timezones, true);
        if (!array_key_exists($timezone, $decode_timezones)) {
            $timezone = $this->server_timezone;
        }
        // Set up start date and end date based on timezone.
        $datesarray = $this->setup_start_end_dates($papertype, $startdate, $enddate, $timezone);
        $startdate = $datesarray[0];
        $enddate = $datesarray[1];
        
        // Set the summative rubric
        if ($papertype == self::TYPE_SUMMATIVE) {
            $langpack = new langpack();
            $default_rubric = $langpack->get_string($this->langcomponent, 'summative_rubric');
        } else {
            $default_rubric = '';
        }
        // Set calulator on/off
        if ($papertype == self::TYPE_FORMATIVE or $papertype == self::TYPE_PROGESSS or $papertype == self::TYPE_SUMMATIVE) {
            $default_calc = 1;
        } else {
            $default_calc = 0;
        }
        // Enforce Interface boundaries.
        if (!empty($duration)) {
            if ($duration > $this->max_duration) {
                $duration = $this->max_duration;
            } elseif ($duration < 0) {
                $duration = 0;
            }
        }
        $timestamp = time();
        $result = $this->db->prepare("INSERT INTO properties (paper_title,
                    start_date,
                    end_date,
                    timezone,
                    paper_type,
                    paper_ownerID,
                    labs,
                    rubric, 
                    calculator,
                    exam_duration,
                    created,
                    calendar_year) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result->bind_param('sssssissiiii', $papertitle, $startdate, $enddate, $timezone, $papertype, $paperowner, $labs, $default_rubric, $default_calc, $duration, $timestamp, $session);
        $result->execute();
        $result->close();
        if ($this->db->errno != 0) {
            return false;
        }
        $property_id = $this->db->insert_id;
        if ($property_id) {
            // Add to Modules.
            foreach ($modules as $module) {
                $result = $this->db->prepare("INSERT INTO properties_modules (property_id, idMod) VALUES (?, ?)");
                $result->bind_param('ii', $property_id, $module);
                $result->execute();
                $result->close();
            }
            
            // Crypt name generation.
            $crypt_name = $property_id . $timestamp . $paperowner; 
            
            $result = $this->db->prepare("UPDATE properties SET crypt_name = ? WHERE property_id = ?");
            $result->bind_param('si', $crypt_name, $property_id);
            $result->execute();
            $result->close();
        } 
        return $property_id;
    }

    /**
     * Update an assesment
     * This should be used to update the basic information that is required by assessments.
     * Note: the properties are validated against system rules
     * Note: the paper type of an assessment cannot be updated
     * 
     * @param integer $id - id of paper
     * @param string $papertitle - New paper title
     * @param integer $paperowner - Owner of paper
     * @param string $startdate - Start date of paper
     * @param string $enddate  - End date of paper
     * @param array $labs - Labs the paper can be taken in
     * @param integer $duration - Length of time associated with the paper
     * @param string $session - Academic session the paper is relevant to
     * @param array $modules - Modules that have the paper available to them
     * @param string $timezone - timezone paper is being taken in
     * @param integer $userid - rogo user id of change implementor
     * @return bool - true on success
     */
    public function update($id, $papertitle, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid) {
        
        $changes = array();
        $params = array();
        $details = Paper_utils::get_paper_properties($id, $this->db);
        if ($papertitle != $details['title']) {
            // Check title is unique.
            $uniquetitle = Paper_utils::is_paper_title_unique($papertitle, $this->db);
            if (!$uniquetitle) {
                throw new Exception('NON_UNIQUE_TITLE');
            }
            $params['paper_title'] = array('s', $papertitle);
            $changes[] = array('old'=>$details['title'], 'new'=>$papertitle, 'part'=>'name');
        }

        if ($paperowner != $details['owner']) {
            // Check owner exists.
            $userexists = UserUtils::userid_exists($paperowner, $this->db);
            if (!$userexists) {
                throw new Exception('INVALID_USER');
            } else {
                // Check owners role.
                $staff = UserUtils::has_user_role($paperowner, 'Staff', $this->db);
                if (!$staff) {
                    throw new Exception('INVALID_ROLE');
                }
            }
            $params['paper_ownerID'] = array('i', $paperowner);
            $changes[] = array('old'=>$details['owner'], 'new'=>$paperowner, 'part'=>'owner');
        }
        
        if ($session != $details['session']) {
            // Check session.
            $yearutils = new yearutils($this->db);
            $validsession = array_key_exists($session, $yearutils->get_supported_years());
            if (!$validsession) {
                 throw new Exception('INVALID_SESSION');
            }
            $params['calendar_year'] = array('i', $session);
            $changes[] = array('old'=>$details['session'], 'new'=>$session, 'part'=>'session');
        }
        
        // Check startdate and enddate
        if ($enddate <= $startdate) {
            throw new Exception('INVALID_DATES');
        }
        if ($startdate != $details['startdatetime']) {
            $params['start_date'] = array('s', $startdate);
            $changes[] = array('old'=>$details['startdatetime'], 'new'=>$startdate, 'part'=>'startdate');
        }
        if ($enddate != $details['enddatetime']) {
            $params['end_date'] = array('s', $enddate);
            $changes[] = array('old'=>$details['enddatetime'], 'new'=>$enddate, 'part'=>'enddate');
        }
        // Set up start date and end date based on timezone.
        $datesarray = $this->setup_start_end_dates($papertype, $startdate, $enddate, $timezone);
        $startdate = $datesarray[0];
        $enddate = $datesarray[1];
        
        
        // Verify timezone is supported, revert to server timezone if not.
        $decode_timezones = json_decode($this->timezones, true);
        if (!array_key_exists($timezone, $decode_timezones)) {
            $timezone = $this->server_timezone;
        }
        if ($timezone != $details['timezone']) {
            $params['timezone'] = array('s', $timezone);
            $changes[] = array('old'=>$details['timezone'], 'new'=>$timezone, 'part'=>'timezone');
        }
        
        // Enforce Interface boundaries.
        if (!empty($duration)) {
            if ($duration > $this->max_duration) {
                $duration = $this->max_duration;
            } elseif ($duration < 0) {
                $duration = 0;
            }
        }
        if ($duration != $details['duration']) {
            $params['exam_duration'] = array('i', $duration);
            $changes[] = array('old'=>$details['duration'], 'new'=>$duration, 'part'=>'duration');
        }

        if ($labs != $details['labs']) {
            $params['labs'] = array('s', $labs);
            $changes[] = array('old'=>$details['labs'], 'new'=>$labs, 'part'=>'labs');
        }

        // Update if changes made.
        if (count($changes) > 0) {
            if (!$this->db_update_assessment($id, $params)) {
                return false;
            }
            // Log changes.
            $logger = new Logger($this->db);
            foreach ($changes as $change) {
                $logger->track_change('Paper', $id, $userid, $change['old'], $change['new'], $change['part']);
            }
        }
        
        // Update to Modules.
        $current_modules = Paper_utils::get_modules($id, $this->db);
        foreach ($modules as $module) {
            if (!array_key_exists($module, $current_modules)) {
                $result = $this->db->prepare("INSERT INTO properties_modules (property_id, idMod) VALUES (?, ?)");
                $result->bind_param(ii, $id, $module);
                $result->execute();
                $result->close();
            }
        }
        foreach ($current_modules as $index => $value) {
            if (!in_array($index, $modules)) {
                $result = $this->db->prepare("DELETE FROM properties_modules WHERE property_id = ? and idMod = ?");
                $result->bind_param(ii, $id, $module);
                $result->execute();
                $result->close();
            }
        }
        
        return true;
    }
    
    /**
     * Update assessment properties
     * This function should be used to update bulk properties for an assessment.
     * Note: property validation does not occur in this function.
     * 
     * @param integer $id id of assessment
     * @param array $params properties to update. The array has the following strucutre:
     *    key - the database field name [0] - The type of the value passed [1] - The value to be set in the database
     * @return bool true on success false otherwise
     */
    public function db_update_assessment($id, $params) {
        $table = 'properties';
        $table_idx = 'property_id';
        return DBUtils::exec_db_update($table, $table_idx, $params, $id, $this->db);
    }
    
    /**
     * Insert assessment 
     * This function should be used to insert all properties for an assessment.
     * Note: property validation does not occur in this function.
     * 
     * @param array $params properties to insert. The array has the following strucutre:
     *    key - the database field name [0] - The type of the value passed [1] - The value to be set in the database
     * @return bool true on success false otherwise
     */
    public function db_insert_assessment($params) {
        $table = 'properties';
        return DBUtils::exec_db_insert($table, $params, $this->db);
    }
    
    /**
     * Schedule a summative assessment 
     * @param integer $paperid paper id
     * @param integer $month the month the exam should be scheduled in
     * @param integer $barriers are barrier required
     * @param string $cohort_size size of cohort taking the exam in a sitting
     * @param string $notes misc notes on paper
     * @param integer $sittings number of sittings required for all cohort to take exam
     * @param string $campus the camps where the exam should be taken
     * @return integer|bool schedule id or false if error
     */
    public function schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus) {
        // Check paper is summative.
        if (Paper_utils::get_paper_type($paperid, $this->db) != self::TYPE_SUMMATIVE) {
            return false;
        }
        // Enforce cohort size interface restrictions.
        $decode_cohort_sizes = json_decode($this->cohort_sizes, true);
        if (!in_array($cohort_size, $decode_cohort_sizes)) {
            $cohort_size = '<whole cohort>';
        }
        // Enforce sittings interface restrictions.
        if (!empty($sittings)) {
            if ($sittings > $this->max_sittings) {
                $sittings = $this->max_sittings;
            } elseif ($sittings < 1) {
                $sittings = 1;
            }
        } else {
            $sittings = 1;
        }
        // Default no barriers.
        if (empty($barriers)) {
            $barriers = 0;
        }
        $result = $this->db->prepare("INSERT INTO scheduling (paperID, period, barriers_needed, cohort_size, notes, sittings, campus)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result->bind_param('iiissis', $paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus);
        $result->execute();
        $result->close();
        if ($this->db->errno != 0) {
            return false;
        }
        return $this->db->insert_id;
    }

    /**
     * Calculate start and end times based on timezone 
     * @param string $papertype type of paper
     * @param string $fromdatetime when the assessment starts
     * @param string $todatetime when the assessment finishes
     * @param string $timezone timezone assessment is being taken in
     * @return array start and end times
     */
    public function setup_start_end_dates($papertype, $fromdatetime, $todatetime, $timezone) {
        if (!$this->summative_mgmt or $papertype != self::TYPE_SUMMATIVE) {
            
            $server_timezone = new DateTimeZone($this->server_timezone);
            $target_timezone = new DateTimeZone($timezone);

            $start_date = new dateTime($fromdatetime, $target_timezone);
            $start_date->setTimezone($server_timezone);

            $end_date = new dateTime($todatetime, $target_timezone);
            $end_date->setTimezone($server_timezone);

            if ($timezone < 0) {
                $start_date->modify("+" . abs($timezone) . " hour");
                $end_date->modify("+" . abs($timezone) . " hour");
            } elseif ($timezone > 0) {
                $start_date->modify("-" . $timezone . " hour");
                $end_date->modify("-" . $timezone . " hour");
            }
            
            return array($start_date->format("YmdHis"), $end_date->format("YmdHis"));
        }
        // Summative exams do not have a start/end date if centrally scheduled.
        return array(NULL, NULL);
    }
}

