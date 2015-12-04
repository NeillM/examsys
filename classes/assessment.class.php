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
 * Formative paper type
 */
define('TYPE_FORMATIVE', 0);
/**
 * Progress paper type
 */
define('TYPE_PROGESSS', 1);
/**
 * Summative paper type
 */
define('TYPE_SUMMATIVE', 2);
/**
 * Survety paper type
 */
define('TYPE_SURVEY', 3);
/**
 * OSCE paper type
 */
define('TYPE_OSCE', 4);
/**
 * Offline paper type
 */
define('TYPE_OFFLINE', 5);
/**
 * Peer revoew paper type
 */
define('TYPE_PEERREVIEW', 6);

/**
 * Assessment helper class.
 */
class assessment {
    
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
    private $langcomponent = 'classes/assessmentmanagement';

    /**
     * @brief Constuctor
     * @param mysqli $db 
     * @param object $configObject 
     */
    function __construct($db, $configObject) {
        $this->db = $db;
        $this->summative_mgmt = $configObject->get('cfg_summative_mgmt');
        $this->server_timezone = $configObject->get('cfg_timezone');
        $this->type = array('formative' => TYPE_FORMATIVE,
            'progress' => TYPE_PROGESSS,
            'summative' => TYPE_SUMMATIVE,
            'survey' => TYPE_SURVEY,
            'osce' => TYPE_OSCE,
            'offline' => TYPE_OFFLINE,
            'peer_review' => TYPE_PEERREVIEW);
        $configObject->set_db_object($db);
        $configObject->load_settings('core');
        $settings = (object) $configObject->get_setting('core');
        $this->timezones = $settings->timezones;
        $this->cohort_sizes = $settings->cohort_sizes;
        $this->max_duration = $settings->max_duration;
        $this->max_sittings = $settings->max_sittings;
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
    public function create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone = '') {
       
        // Check title is unique.
        $uniquetitle = Paper_utils::is_paper_title_unique($papertitle, $this->db);
        if (!$uniquetitle) {
            throw new Exception('NON_UNIQUE_TITLE');
        }
        // Check paper type is valid.
        if (!array_key_exists($papertype, $this->type)) {
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
        if ($papertype != 'summative' and $enddate <= $startdate) {
            throw new Exception('INVALID_DATES');
        }
        // Set the summative rubric
        if ($papertype == 'summative') {
            $langpack = new langpack();
            $default_rubric = $langpack->get_string($this->langcomponent, 'summative_rubric');
        } else {
            $default_rubric = '';
        }
        // Set calulator on/off
        if ($papertype == 'formative' or $papertype == 'progress' or $papertype == 'summative') {
            $default_calc = 1;
        } else {
            $default_calc = 0;
        }
        // Enforce Interface boundaries.
        if ($duration != 'NULL') {
            if ($duration > $this->max_duration) {
                $duration = $this->max_duration;
            } elseif ($duration < 0) {
                $duration = 0;
            }
        } 
        // Summative exams do not have a start/end date if centrally scheduled.
        if ($papertype == 'summative') {
            if ($this->summative_mgmt) {
                $startdate = NULL;
                $enddate = NULL;
            }
        } 
        // Verify timezone is supported, revert to server timezone if not.
        $decode_timezones = json_decode($this->timezones, true);
        if (!array_key_exists($timezone, $decode_timezones)) {
            $timezone = $this->server_timezone;
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
        $result->bind_param('sssssissiiii', $papertitle, $startdate, $enddate, $timezone, $this->type[$papertype], $paperowner, $labs, $default_rubric, $default_calc, $duration, $timestamp, $session);
        $result->execute();
        $result->close();
        if ($db->errno != 0) {
            return false;
        }
        $property_id = $this->db->insert_id;
        if ($property_id) {
            // Add to Modules.
            foreach ($modules as $module) {
                $result = $this->db->prepare("INSERT INTO properties_modules (property_id, idMod) VALUES (?, ?)");
                $result->bind_param(ii, $property_id, $module);
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
     * 
     * Note the paper type of an assessment cannot be updated
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
     * @return bool - true on success
     */
    public function update($id, $papertitle, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone = '') {
        
        // Check title is unique.
        $uniquetitle = Paper_utils::is_paper_title_unique($papertitle, $this->db);
        if (!$uniquetitle) {
            throw new Exception('NON_UNIQUE_TITLE');
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
        if ($enddate <= $startdate) {
            throw new Exception('INVALID_DATES');
        }   
        // Enforce Interface boundaries.
        if (!empty($duration)) {
            if ($duration > $this->max_duration) {
                $duration = $this->max_duration;
            } elseif ($duration < 0) {
                $duration = 0;
            }
        } else {
            $duration = null;
        }      
        // Verify timezone is supported, revert to server timezone if not.
        $decode_timezones = json_decode($this->timezones, true);
        if (!array_key_exists($timezone, $decode_timezones)) {
            $timezone = $this->server_timezone;
        }
        $result = $this->db->prepare("UPDATE properties SET paper_title = ?,
                    start_date = ?,
                    end_date = ?,
                    timezone = ?,
                    paper_ownerID = ?,
                    labs = ?,
                    exam_duration = ?,
                    calendar_year = ? 
                WHERE property_id = ?");
        $result->bind_param('ssssisiii', $papertitle, $startdate, $enddate, $timezone, $paperowner, $labs, $duration, $session, $id);
        $result->execute();
        $result->close();
        if ($this->db->errno != 0) {
            return false;
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
     * Schedule a summative assessment 
     * @param integer $paperid paper id
     * @param integer $month the month the exam should be scheduled in
     * @param integer $barriers are barrier required
     * @param integer $cohort_size size of cohort taking the exam in a sitting
     * @param string $notes misc notes on paper
     * @param integer $sittings number of sittings required for all cohort to take exam
     * @param string $campus the camps where the exam should be taken
     * @return integer|bool schedule id or false if error
     */
    public function schedule($paperid, $month, $barriers = 0, $cohort_size = '<whole cohort>', $notes = '', $sittings = 1, $campus = '') {
        // Check paper is summative.
        if (Paper_utils::get_paper_type($paperid, $this->db) != TYPE_SUMMATIVE) {
            return false;
        }
        // Enforce cohort size interface restrictions
        $decode_cohort_sizes = json_decode($this->cohort_sizes, true);
        if (!in_array($cohort_size, $decode_cohort_sizes)) {
            $cohort_size = '<whole cohort>';
        }
        // Enforce sittings interface restrictions
        if ($sittings > $this->max_sittings) {
            $sittings = $this->max_sittings;
        } elseif ($sittings < 1) {
            $sittings = 1;
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

}

