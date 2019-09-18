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

namespace testing\datagenerator;

/**
 * Generates Rogo Campuses and Labs.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class labs extends generator {
    /** @var int Stores how many campuses have been created. */
    protected static $campusescreated = 0;

    /** @var int Stores how many labs have been created. */
    protected static $labscreated = 0;

    /** @var int Stores how many exam pcs have been created. */
    protected static $pcscreated = 0;

    /**
     * Creates a campus.
     *
     * @param array|stdClass $parameters
     * @return array
     * @throws data_error
     */
    public function create_campus($parameters) {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $number = ++self::$campusescreated;
        $defaults = array(
            'name' => "Campus $number",
            'isdefault' => 0,
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $values['id'] = $this->insert_campus($values);
        return $values;
    }

    /**
     * Creates an entry for an exam PC.
     *
     * @param type $parameters
     * @return array
     * @throws data_error
     */
    public function create_exam_pc($parameters) {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $number = ++self::$labscreated;
        $subnumber = (int)($number / 256);
        $lastnumber = ($number % 255) + 1; // Must not be 0, or we have a broadcast address.
        $defaults = array(
            'lab' => '',
            'address' => "192.168.$subnumber.$lastnumber", // We will be able to generate 65280 IP addresses before we get a duplicate.
            'hostname' => "test$number.example.com",
            'low_bandwidth' => 0,
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $lab = $values['lab'];
        $values['lab'] = $this->get_lab_id($values['lab']);
        if ($values['lab'] === false) {
            throw new data_error("Lab '$lab' does not exist.");
        }
        $values['id'] = $this->insert_exam_pc($values);
        return $values;
    }

    /**
     * Creates a lab.
     *
     * @param type $parameters
     * @return array
     * @throws data_error
     */
    public function create_lab($parameters) {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $number = ++self::$labscreated;
        $defaults = array(
            'name' => "Lab $number",
            'campus' => $this->get_default_campus(),
            'building' => 'My building',
            'room' => random_int(1, 50),
            'timetabling' => '',
            'support' => '',
            'plagarism' => '',
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $campus = $values['campus'];
        $values['campus'] = $this->get_campus_id($values['campus']);
        if (empty($values['campus'])) {
            throw new data_error("Campus '$campus' does not exist.");
        }
        $values['id'] = $this->insert_lab($values);
        return $values;
    }

    /**
     * Gets the database id of a Campus from it's name.
     *
     * @param string $name
     * @return int The id of the campus record, or 0 if none is found.
     */
    protected function get_campus_id($name) {
        $campuses = $this->get_campuses();
        foreach ($campuses as $id => $campus) {
            if (trim($campus['campusname']) === trim($name)) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Gets details of all the campuses in Rogo.
     *
     * @return array
     */
    protected function get_campuses() {
        $helper = new \campus($this->db);
        return $helper->get_all_campus_details();
    }

    /**
     * Gets the default campus.
     *
     * @return string
     */
    protected function get_default_campus() {
        $campuses = $this->get_campuses();
        foreach ($campuses as $campus) {
            if (!empty($campus['isdefault'])) {
                // We found the default campus.
                return $campus['campusname'];
            }
        }
        return '';
    }

    /**
     * Gets the id of a lab from it's name.
     *
     * @param string $name
     * @return int|false The database id of the lab, or false if it could not be found.
     */
    protected function get_lab_id($name) {
        $helper = new \LabFactory($this->db);
        return $helper->get_lab_id($name);
    }

    /**
     * Inserts the exam pc into the database.
     *
     * @param array $values
     * @throws data_error If passed parameter is invalid
     * @return int The database id of the new lab record.
     */
    protected function insert_campus($values) {
        $query = $this->db->prepare("INSERT INTO campus (name, isdefault) VALUES (?, ?)");
        $query->bind_param('si', $values['name'], $values['isdefault']);
        if (!$query->execute()) {
            // The campus was not successfully inserted.
            throw new data_error("Campus {$values['name']} not inserted into database");
        }
        return $query->insert_id;
    }

    /**
     * Inserts the campus into the database.
     *
     * @param array $values
     * @return int The database id of the new lab record.
     * @throws data_error If passed parameter is invalid
     */
    protected function insert_exam_pc($values) {
        $query = $this->db->prepare("INSERT INTO client_identifiers (lab, address, hostname, low_bandwidth) VALUES (?, ?, ?, ?)");
        $query->bind_param('issi', $values['lab'], $values['address'], $values['hostname'], $values['low_bandwidth']);
        if (!$query->execute()) {
            // The exam pc was not successfully inserted.
            throw new data_error("PC {$values['hostname']} ({$values['address']}) not inserted into database");
        }
        return $query->insert_id;
    }

    /**
     * Inserts a lab into the database.
     *
     * @param array $values
     * @return int The database id of the new lab record.
     * @throws data_error If passed parameter is invalid
     */
    protected function insert_lab($values) {
        $sql = "INSERT INTO labs (name, campus, building, room_no, timetabling, it_support, plagarism) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $this->db->prepare($sql);
        $query->bind_param('sisssss', $values['name'], $values['campus'], $building, $room_no, $timetabling, $it_support, $plagarism);
        if (!$query->execute()) {
            // The lab was not successfully inserted.
            throw new data_error("Lab {$values['name']} not inserted into database");
        }
        return $query->insert_id;
    }
}
