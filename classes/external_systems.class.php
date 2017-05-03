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
* External Systems package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * External Systems helper class.
 */
class external_systems {
    /**
     * The db connection
     * @var mysqli
     */
    private $db;
    /**
     * Constructor
     * @return void 
     */
    function __construct() {
        $configObject = Config::get_instance();
        $this->db = $configObject->db;
    }
    /**
     * Get external system mapped to a user
     * @param string $user_id user
     * @return string|null external system
     */
    public function get_mapped_externalsystem($user_id) {
        $result = $this->db->prepare("SELECT s.name FROM external_systems s, external_systems_mapping m WHERE s.id = m.ext_id AND m.user_id = ?");
        $result->bind_param('i', $user_id);
        $result->execute();
        $result->bind_result($name);
        $result->fetch();
        $result->close();
        if ($this->db->errno != 0) {
            return null;
        }
        return $name;
    }
    /**
     * Get external system id mapped to a user
     * @param string $user_id user
     * @return string|null external system
     */
    public function get_mapped_externalsystem_id($user_id) {
        $result = $this->db->prepare("SELECT ext_id FROM external_systems_mapping WHERE user_id = ?");
        $result->bind_param('i', $user_id);
        $result->execute();
        $result->bind_result($id);
        $result->fetch();
        $result->close();
        if ($this->db->errno != 0) {
            return null;
        }
        return $id;
    }
    /**
     * Get external systems
     * @return array external systems
     */
    public function get_all_externalsystems() {
        $exts = array();
        $result = $this->db->prepare("SELECT id, name FROM external_systems");
        $result->execute();
        $result->bind_result($extid, $name);
        while ($result->fetch()) {
            $exts[$extid] = $name;
        }
        $result->close();
        return $exts;
    }
    /**
     * Get external systems details
     * @return array external systems
     */
    public function get_all_externalsystems_details() {
        $exts = array();
        $result = $this->db->prepare("SELECT id, name, type FROM external_systems");
        $result->execute();
        $result->bind_result($extid, $name, $type);
        while ($result->fetch()) {
            $exts[$extid] = array('name' => $name, 'type' => $type);
        }
        $result->close();
        return $exts;
    }
    /**
     * Get API external systems
     * @return array external systems
     */
    public function get_all_api_externalsystems() {
        $exts = array();
        $result = $this->db->prepare("SELECT id, name FROM external_systems WHERE type = 'api'");
        $result->execute();
        $result->bind_result($extid, $name);
        while ($result->fetch()) {
            $exts[$extid] = $name;
        }
        $result->close();
        return $exts;
    }
    /**
     * Insert external system mapping for user
     * @param integer $userid internal user id
     * @param integer $extsys external system id
     */
    public function insert_external_system_mapping($userid, $extsys) {
        $result = $this->db->prepare("INSERT INTO external_systems_mapping (user_id, ext_id) values (?, ?)");
        $result->bind_param('ii', $userid, $extsys);
        $result->execute();
        $result->close();
    }
    /**
     * Update external system mapping for user
     * @param integer $userid internal user id
     * @param integer $extsys external system id
     */
    public function update_external_system_mapping($userid, $extsys) {
        if (!is_null($this->get_mapped_externalsystem_id($userid))) {
            $result = $this->db->prepare("UPDATE external_systems_mapping SET ext_id = ? WHERE user_id = ?");
            $result->bind_param('ii', $extsys, $userid);
            $result->execute();
            $result->close();
        } else {
            $this->insert_external_system_mapping($userid, $extsys);
        }
    }
    /**
     * Delete external system mapping for user
     * @param integer $userid internal user id
     */
    public function delete_external_system_mapping($userid) {
        if (!is_null($this->get_mapped_externalsystem_id($userid))) {
            $result = $this->db->prepare("DELETE FROM external_systems_mapping WHERE user_id = ?");
            $result->bind_param('i', $userid);
            $result->execute();
            $result->close();
        }
    }
    /**
     * Insert external system
     * @param string $name name of external system
     * @param string $type type of external system
     */
    public function insert_external_system($name, $type) {
        $result = $this->db->prepare("INSERT INTO external_systems (name, type) VALUES (?, ?)");
        $result->bind_param('ss', $name, $type);
        $result->execute();
        $result->close();
    }
    /**
     * Delete external system
     * @param string $id id of external system
     */
    public function delete_external_system($id) {
        $result = $this->db->prepare("DELETE FROM external_systems WHERE id = ?");
        $result->bind_param('i', $id);
        $result->execute();
        $result->close();
    }
    /**
     * Check if external system exists
     * @param integer $id internal id of external system
     * @return boolean
     */
    public function external_system_exits($id) {
        $exists = false;
        $result = $this->db->prepare("SELECT NULL FROM external_systems WHERE id = ?");
        $result->bind_param('i', $id);
        $result->execute();
        $result->store_result();
        if ($result->num_rows > 0) {
            $exists = true;
        }
        $result->close();
        return $exists;
    }
    /**
     * Check if external system is in use
     * @param integer $id internal id of external system
     * @return boolean
     */
    public function external_system_inuse($id) {
        $exists = false;
        if ($this->external_system_exits($id)) {
            // If not api external system flag as in use
            $apis = $this->get_all_api_externalsystems();
            if (!array_key_exists($id, $apis)) {
                return true;
            }
            // Check is api mapped to user.
            $result = $this->db->prepare("SELECT NULL FROM external_systems_mapping WHERE ext_id = ?");
            $result->bind_param('i', $id);
            $result->execute();
            $result->store_result();
            if ($result->num_rows > 0) {
                $exists = true;
            }
            $result->close();
        }
        return $exists;
    }
}