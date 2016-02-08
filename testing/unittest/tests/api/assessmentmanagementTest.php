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
 * Test assessmentmanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class assessmentmanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "assessmentmanagementTest" . DIRECTORY_SEPARATOR . "assessmentmanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "assessmentmanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test assessment create
     * @group api
     */
    public function test_create() {
        // Test paper create- SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 4,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "title" => "Test Formative",
            "type" => 'formative',
            "owner" => 1,
            "startdatetime" => "2016-05-30T09:00:00",
            "enddatetime" => "2016-05-30T10:00:00",
            "session" => 2016,
            "modules" => array(array('id' => 0, 'value' => 1)),
            "labs" => array(array('id' => 0, 'value' => 'Test lab')),
            "timezone" => "Europe/London");
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - EXCEPTION title in use.
        $responsearray['statuscode'] = 206;
        $responsearray['status'] = 'Assessment title is already in use';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2; 
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create- EXCEPTION invalid paper type.
        $responsearray['statuscode'] = 215;
        $responsearray['status'] = 'Paper type unknown';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3; 
        $params['title'] = "Test Formative 2"; 
        $params['type'] = 0;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - EXCEPTION invalid user.
        $responsearray['statuscode'] = 207;
        $responsearray['status'] = 'Assessment owner is invalid';
        $responsearray['nodeid'] = 4;
        $params['nodeid'] = 4; 
        $params['title'] = "Test Formative 2"; 
        $params['type'] = "formative";
        $params['owner'] = 1000;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - EXCEPTION invalid user role.
        $responsearray['statuscode'] = 208;
        $responsearray['status'] = 'Assessment owner role is invalid';
        $responsearray['nodeid'] = 5;
        $params['nodeid'] = 5; 
        $params['owner'] = 3;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - EXCEPTION invalid session.
        $responsearray['statuscode'] = 209;
        $responsearray['status'] = 'Calendar year invalid';
        $responsearray['nodeid'] = 6;
        $params['nodeid'] = 6; 
        $params['owner'] = 1;
        $params['session'] = 1970;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - EXCEPTION invalid dates.
        $responsearray['statuscode'] = 212;
        $responsearray['status'] = 'End date must be after start date';
        $responsearray['nodeid'] = 7;
        $params['nodeid'] = 7;
        $params['session'] = 2016;
        $params['startdatetime'] = "2016-05-30T10:00:00";
        $params['enddatetime'] = "2016-05-30T09:00:00";
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - ERROR invalid modules.
        $responsearray['statuscode'] = 211;
        $responsearray['status'] = 'Module error';
        $error = array();
        $error[0] = 'Invalid module 1000';
        $responsearray['error'] = $error;
        $responsearray['nodeid'] = 8;
        $params['nodeid'] = 8;
        $params['startdatetime'] = "2016-05-30T09:00:00";
        $params['enddatetime'] = "2016-05-30T10:00:00";
        $params['modules'] = array(array('id' => 0, 'value' => 1000));
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test paper create - ERROR invalid labs.
        $responsearray['statuscode'] = 100;
        $responsearray['status'] = 'OK';
        $error[0] = 'Invalid lab Test lab 2';
        $responsearray['error'] = $error;
        $responsearray['nodeid'] = 9;
        $responsearray['id'] = 5;
        $params['nodeid'] = 9;
        $params['modules'] = array();
        $params['labs'] = array(array('id' => 0, 'value' => 'Test lab 2'));
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test assessemnt deletion
     * @group api
     */
    public function test_delete() {
        // Test paper deletion- SUCCESS.
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
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
        // Check that the remaining properties are correct, when we delete a paper we actually jsut add a timestamp to the table
        // which makes creating a ficute to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type FROM properties WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteassessment')->getTable("properties");  
        $this->assertTablesEqual($expectedtable, $querytable); 
        // Test deleting a non existance paper.
        $responsearray['statuscode'] = 202;
        $responsearray['status'] = 'Paper does not exist';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
        // Test deleting a paper in use - first add an entry in log_metadata.
        $responsearray['statuscode'] = 203;
        $responsearray['status'] = 'Assessment not deleted, as has been taken by a user';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
        // Test deleting a paper in use - second add an entry in log4_overall.
        $responsearray['statuscode'] = 203;
        $responsearray['status'] = 'Assessment not deleted, as has been taken by a user';
        $responsearray['nodeid'] = 4;
        $params['nodeid'] = 4;
        $params['id'] = 3;
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
    }
    
}
