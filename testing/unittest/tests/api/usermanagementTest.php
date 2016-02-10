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
 * Test usermanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class usermanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "usermanagementTest" . DIRECTORY_SEPARATOR . "usermanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "usermanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test user creation
     * @group api
     */
    public function test_create() {
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        // Test user create - SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1003,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "username" => "testy",
            "surname" => "tester",
            "role" => "Student",
            "course" => "TEST2");
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Test user create - ERROR already exists
        $responsearray['nodeid'] = 2;
        $responsearray['statuscode'] = 706;
        $responsearray['status'] = 'User already exists';
        $params['nodeid'] = 2;
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Test user create - ERROR invalid role
        $responsearray['nodeid'] = 3;
        $responsearray['statuscode'] = 707;
        $responsearray['status'] = 'User has invalid role';
        $responsearray['id'] = null;
        $params['nodeid'] = 3;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'unknownrole';
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Test user create - ERROR invalid course
        $responsearray['nodeid'] = 4;
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $params['nodeid'] = 4;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Student';
        $params['course'] = 'TEST22';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test user update
     * @group api
     */
    public function test_update() {
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        // Test user update - SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1001,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "id" => 1001,
            "forename" => "test");
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Test user update - ERROR user does not exist
        $responsearray['nodeid'] = 2;
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['nodeid'] = 2;
        $params['id'] = '99';
        $params['surname'] = 'unknown';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test user deletion
     * @group api
     */
    public function test_delete() {
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        // Test user deletion - SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1001,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "id" => 1001);
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Check that the remaining user are correct, when we delete a user we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT id, password, surname, username, roles, grade FROM users WHERE user_deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
        // Test deleting a non existance faculty.
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Test deleting a user in use. case 1 - in log_metadata
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 1000;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Test deleting a user in use. case 2 - in log4_overall
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 1002;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
    }
}