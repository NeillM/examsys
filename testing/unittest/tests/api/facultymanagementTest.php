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

use testing\unittest\unittestdatabase;

/**
 * Test facultyemanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class facultymanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "facultymanagementTest" . DIRECTORY_SEPARATOR . "facultymanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "facultymanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test faculty creation
     * @group api
     */
    public function test_create() {
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        // Test faculty creation - SUCCESS
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 3,
            "error" => null,
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "name" => 'TEST3');
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
        // Test faculty creation - ERROR faculty already exists
        $responsearray = array(
            "statuscode" => 405,
            "status" => 'Faculty already exists',
            "id" => 1,
            "error" => null,
            "node" => 'create',
            "nodeid" => 3);
        $params = array(
            "nodeid" => 3,
            "name" => 'TEST');
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test faculty update
     * @group api
     */
    public function test_update() {
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        // Test faculty update - SUCCESS
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1,
            "error" => null,
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "id" => 1,
            "name" => 'TEST3');
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
        // Test faculty update - ERROR faculty does not exist
        $responsearray = array(
            "statuscode" => 401,
            "status" => 'Faculty does not exist',
            "id" => null,
            "error" => null,
            "node" => 'create',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "id" => 100,
            "name" => 'TEST4',
            "description" => 'Create test');
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test faculty deletion
     * @group api
     */
    public function test_delete() {
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        // Test faculty deletion - SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "id" => 1);
        $this->assertEquals($responsearray, $faculty->delete($params, $userid));
        // Check that the remaining faculty are correct, when we delete a faculty we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('faculty', 'SELECT id, name FROM faculty WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletefaculty')->getTable("faculty");  
        $this->assertTablesEqual($expectedtable, $querytable);
        // Test deleting a non existance faculty.
        $responsearray['statuscode'] = 401;
        $responsearray['status'] = 'Faculty does not exist';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $faculty->delete($params, $userid));
        // Test deleting a faculty in use.
        $responsearray['statuscode'] = 404;
        $responsearray['status'] = 'Faculty not deleted, as contains schools';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $faculty->delete($params, $userid)); 
    }
}