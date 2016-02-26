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
     * Create a student response array for creation
     * @return array the response array  
     */
    private function create_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1003,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
    }
    /**
     * Create a student parameter array for creation
     * @return array the param array  
     */
    private function create_param_array() {
        return array(
            "nodeid" => 1,
            "username" => "testy",
            "surname" => "tester",
            "role" => "Student",
            "course" => "TEST2",
            "modules" => array(array('name' => 'moduleid', 'id' => 0, 'value' => 1)));
    }
    /**
     * Create a staff parameter array for creation
     * @return array the param array  
     */
    private function create_staff_param_array() {
        return array(
            "nodeid" => 1,
            "username" => "staff",
            "surname" => "staffy",
            "role" => "Staff",
            "course" => "University Lecturer",
            "modules" => array(array('name' => 'moduleid', 'id' => 0, 'value' => 1)));
    }
    /**
     * Create a response array for updates
     * @return array the response array  
     */
    private function update_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1001,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for updates
     * @return array the param array  
     */
    private function update_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 1001,
            "forename" => "test",
            "modules" => array(array('name' => 'moduleid', 'id' => 0, 'value' => 2)));
    }
    /**
     * Create a response array for deletion
     * @return array the response array  
     */
    private function delete_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1001,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for deletion
     * @return array the param array  
     */
    private function delete_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 1001);
    }
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
     * Test successful student creation
     * @group api
     */
    public function test_create_student_success() {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Check user is enrolled on expected moulde.
        $querytable = $this->getConnection()->createQueryTable('modules_student', 'SELECT id, userID, idMod FROM modules_student');
        $expectedtable = $this->get_expected_data_set('createuser')->getTable("modules_student");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test successful staff creation
     * @group api
     */
    public function test_create_staff_success() {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_staff_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Check user is enrolled on expected moulde.
        $querytable = $this->getConnection()->createQueryTable('modules_staff', 'SELECT memberID, idMod FROM modules_staff');
        $expectedtable = $this->get_expected_data_set('createuser')->getTable("modules_staff");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user creation exception user exists
     * @group api
     */
    public function test_create_exception_user() {
        // Test user create - ERROR already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 706;
        $responsearray['status'] = 'User already exists';
        $responsearray['id'] = 1000;
        $params['username'] = 'unit';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test user creation exception invalid user role
     * @group api
     */
    public function test_create_exception_role() {
        // Test user create - ERROR invalid role
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 707;
        $responsearray['status'] = 'User has invalid role';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'unknownrole';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test user creation exception invalid course
     * @group api
     */
    public function test_create_exception_course() {
        // Test user create - ERROR invalid course
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Student';
        $params['course'] = 'TEST22';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test staff creation exception invalid course
     * @group api
     */
    public function test_create_staff_exception_course() {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_staff_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Staff';
        $params['course'] = 'Invalid';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test successful user update
     * @group api
     */
    public function test_update_success() {
        // Test user update - SUCCESS.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Check user is enrolled on expected moulde.
        $querytable = $this->getConnection()->createQueryTable('modules_student', 'SELECT id, userID, idMod FROM modules_student');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("modules_student");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
     /**
     * Test user update exception user does not exist
     * @group api
     */
    public function test_update_exception_user() {
        // Test user update - ERROR user does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['id'] = '99';
        $params['surname'] = 'unknown';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test successful user deletion
     * @group api
     */
    public function test_delete_success() {
        // Test user deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Check that the remaining user are correct, when we delete a user we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT id, password, surname, username, roles, grade FROM users WHERE user_deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user deletion exception user does not exist
     * @group api
     */
    public function test_delete_exception_user() {
        // Test deleting a non existance user.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Test id not supplied.
        $params = array(
            "nodeid" => 1);
        $this->assertEquals($responsearray, $user->delete($params, $userid));
    }
    /**
     * Test user deletion exception user does not exist
     * @group api
     */
    public function test_delete_exception_inuse() {
        // Test deleting a user in use. case 1 - in log_metadata
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $responsearray['id'] = null;
        $params['id'] = 1000;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Test deleting a user in use. case 2 - in log4_overall
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $params['id'] = 1002;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
    }
}