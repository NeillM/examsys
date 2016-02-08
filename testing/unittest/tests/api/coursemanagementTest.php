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
 * Test coursemanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class coursemanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "coursemanagementTest" . DIRECTORY_SEPARATOR . "coursemanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "coursemanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test course create
     * @group api
     */
    public function test_create() {
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        // Test course creation - SUCCESS
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 3,
            "error" => null,
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "name" => 'CREATE',
            "description" => 'Create test',
            "school" => 'School test',
            "faculty" => 'Faculty test');
        $this->assertEquals($responsearray, $course->create($params, $userid));
        // Test course creation - SUCCESS (not supplying faculty)
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 4,
            "error" => null,
            "node" => 'create',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "name" => 'CREATE2',
            "description" => 'Create test 2',
            "school" => 'School test');
        $this->assertEquals($responsearray, $course->create($params, $userid));
        // Test course creation - ERROR course already exists
        $responsearray = array(
            "statuscode" => 306,
            "status" => 'Course already exists',
            "id" => 3,
            "error" => null,
            "node" => 'create',
            "nodeid" => 3);
        $params = array(
            "nodeid" => 3,
            "name" => 'CREATE',
            "description" => 'Create test',
            "school" => 'School test',
            "faculty" => 'Faculty test');
        $this->assertEquals($responsearray, $course->create($params, $userid));
         // Test course creation - ERROR invalid faculty
        $responsearray = array(
            "statuscode" => 303,
            "status" => 'Faculty not supplied',
            "id" => null,
            "error" => null,
            "node" => 'create',
            "nodeid" => 4);
        $params = array(
            "nodeid" => 4,
            "name" => 'CREATE 3',
            "description" => 'Create test 3',
            "school" => 'School test 2');
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test course update
     * @group api
     */
    public function test_update() {
        // Init course.
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $params = array(
            "nodeid" => 1,
            "name" => 'CREATE',
            "description" => 'Create test',
            "school" => 'School test',
            "faculty" => 'Faculty test');
        $course->create($params, $userid);
        // Test course update - SUCCESS
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
            "description" => 'Create test 2');
        $this->assertEquals($responsearray, $course->create($params, $userid));
        // Test course uddate - ERROR course does not exist
        $responsearray = array(
            "statuscode" => 301,
            "status" => 'Course does not exist',
            "id" => null,
            "error" => null,
            "node" => 'create',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "id" => 100,
            "name" => 'CREATE',
            "description" => 'Create test');
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test course deletion
     * @group api
     */
    public function test_delete() {
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        // Test course deletion - SUCCESS.
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
        $this->assertEquals($responsearray, $course->delete($params, $userid));
        // Check that the remaining courses are correct, when we delete a course we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('courses', 'SELECT id, name, description, schoolid FROM courses WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletecourse')->getTable("courses");  
        $this->assertTablesEqual($expectedtable, $querytable);
        // Test deleting a non existance cuoprse.
        $responsearray['statuscode'] = 301;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $course->delete($params, $userid));
        // Test deleting a course in use.
        $responsearray['statuscode'] = 302;
        $responsearray['status'] = 'Course not deleted, as users enrolled';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $course->delete($params, $userid)); 
    }
}
