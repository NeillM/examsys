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
 * Test schoolmanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class schoolmanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "schoolmanagementTest" . DIRECTORY_SEPARATOR . "schoolmanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "schoolmanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test school create
     * @group api
     */
    public function test_create() {
        $school = new \api\schoolmanagement($this->db);
        $userid = 1;
        // Test school creation - SUCCESS
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 4,
            "error" => null,
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "name" => 'CREATE',
            "faculty" => 'Test faculty');
        $this->assertEquals($responsearray, $school->create($params, $userid));
        // Test school creation - ERROR school already exists
        $responsearray = array(
            "statuscode" => 606,
            "status" => 'School already exists',
            "id" => 1,
            "error" => null,
            "node" => 'create',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "name" => 'Test school',
            "faculty" => 'Test faculty');
        $this->assertEquals($responsearray, $school->create($params, $userid));
         // Test school creation - ERROR faculty not supplied
        $responsearray = array(
            "statuscode" => 605,
            "status" => 'Faculty not supplied',
            "id" => null,
            "error" => null,
            "node" => 'create',
            "nodeid" => 3);
        $params = array(
            "nodeid" => 3,
            "name" => 'CREATE 2',
            "faculty" => '');
        $this->assertEquals($responsearray, $school->create($params, $userid));
    }
    /**
     * Test school update
     * @group api
     */
    public function test_update() {
        $school = new \api\schoolmanagement($this->db);
        $userid = 1;
        // Test school update - SUCCESS
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
            "name" => 'CREATE2',
            "faculty" => 'Test faculty');
        $this->assertEquals($responsearray, $school->create($params, $userid));
        // Test school update - ERROR school does not exist
        $responsearray = array(
            "statuscode" => 601,
            "status" => 'School does not exist',
            "id" => null,
            "error" => null,
            "node" => 'create',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "id" => 100,
            "name" => 'CREATE',
            "faculty" => 'Test faculty');
        $this->assertEquals($responsearray, $school->create($params, $userid));
    }
    /**
     * Test school deletion
     * @group api
     */
    public function test_delete() {
        $school = new \api\schoolmanagement($this->db);
        $userid = 1;
        // Test school deletion - SUCCESS.
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
        $this->assertEquals($responsearray, $school->delete($params, $userid));
        // Check that the remaining schools are correct, when we delete a school we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('schools', 'SELECT id, school, facultyID FROM schools WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteschool')->getTable("schools");  
        $this->assertTablesEqual($expectedtable, $querytable);
        // Test deleting a non existance school.
        $responsearray['statuscode'] = 601;
        $responsearray['status'] = 'School does not exist';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $school->delete($params, $userid));
        // Test deleting a school in use - in a course.
        $responsearray['statuscode'] = 604;
        $responsearray['status'] = 'School not deleted, as in use by a course or module';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $school->delete($params, $userid));
        // Test deleting a school in use - in a module.
        $responsearray['statuscode'] = 604;
        $responsearray['status'] = 'School not deleted, as in use by a course or module';
        $responsearray['nodeid'] = 4;
        $params['nodeid'] = 4;
        $params['id'] = 3;
        $this->assertEquals($responsearray, $school->delete($params, $userid));
    }
}
