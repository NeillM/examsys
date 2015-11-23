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
* Oauth package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Encryption helper class.
 * Interfaces with the vendor/bshaffer/oauth2-server-php
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
       
    // Paper type name and keys
    private $type;
 
    /**
     * Language pack component.
     */
    private $langcomponent = 'classes/assessmentmanagement';

    /**
     * @brief Constuctor
     * @param mysqli $db 
     * @param object $configObject 
     * @return  
     */
    function __construct($db, $configObject) {
        $this->db = $db;
        $this->summative_mgmt = $configObject->get('cfg_summative_mgmt');
        $this->server_timezone = $configObject->get('cfg_timezone');
        $this->type = array('formative' => 0,
            'progress' => 1,
            'summative' => 2,
            'survey' => 3,
            'osce' => 4,
            'offline' => 5,
            'peer_review' => 6);
        $configObject->set_db_object($db);
        $configObject->load_settings('core');
        $settings = (object) $configObject->get_setting('core');
        $this->timezones = $settings->timezones;
    }
    
    /**
     * @brief Create an assesment
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
     * @return integer - id of new assessment
     */
    public function create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration = 'NULL', $session, $modules, $timezone = '') {
       
        if ($papertype == 'summative') {
            $langpack = new \langpack();
            $default_rubric = $langpack->get_string($this->langcomponent, 'summative_rubric');
        } else {
            $default_rubric = '';
        }
        if ($papertype == 'formative' or $papertype == 'progress' or $papertype == 'summative') {
            $default_calc = 1;
        } else {
            $default_calc = 0;
        }
        // Enforce Interface boundaries.
        // Move to cfg db table when we have one.
        if ($duration != 'NULL') {
            if ($duration > 779) {
                $duration = 779;
            } elseif ($duration < 0) {
                $duration = 0;
            }
        } 
        if ($papertype == 'summative') {
            if ($this->summative_mgmt) {
                $startdate = NULL;
                $enddate = NULL;
            }
        } 
        // Set timezone if not supplied.
        if ($timezone == '') {
            $timezone = $this->server_timezone;
        }
        // Verify timezone id supported.
        $decode_timezones = json_decode($this->timezones, true);
        if (!array_key_exists($timezone, $decode_timezones)) {
            return false;
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
    public function update($id, $papertitle, $paperowner, $startdate, $enddate, $labs, $duration = 'NULL', $session, $modules, $timezone = '') {       
        // Enforce Interface boundaries.
        // Move to cfg db table when we have one.
        if ($duration != 'NULL') {
            if ($duration > 779) {
                $duration = 779;
            } elseif ($duration < 0) {
                $duration = 0;
            }
        }        
        // Set timezone if not supplied.
        if ($timezone == '') {
            $timezone = $this->server_timezone;
        }
        // Verify timezone id supported.
        $decode_timezones = json_decode($this->timezones, true);
        if (!array_key_exists($timezone, $decode_timezones)) {
            return false;
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
        if (Paper_utils::get_paper_type($paperid, $this->db) != 2) {
            return false;
        }
        // Enforce cohort size interface restrictions
        // Move to cfg db table when we have one.
        $cohorts_sizearray = array('0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300', '301-400', '401-500');
        if (!in_array($cohort_size, $cohorts_sizearray)) {
            $cohort_size = '<whole cohort>';
        }
        // Enforce sittings interface restrictions
        // Move to cfg db table when we have one.
        if ($sittings > 6) {
            $sittings = 6;
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

