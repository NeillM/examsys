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
     * Test assessemnt deletion
     * @group api
     */
    public function test_delete() {
        // Test paper deletion- SUCCESS.
        $successresponsearray = array(
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
        $this->assertEquals($successresponsearray, $assessment->delete($params, $userid));
        // Check that the remaining properties are correct, when we delete a paper we actually jsut add a timestamp to the table
        // which makes creating a ficute to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type FROM properties WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteassessment')->getTable("properties");  
        $this->assertTablesEqual($expectedtable, $querytable); 
        // Test deleting a non existance paper.
        $errorresponsearray = array(
            "statuscode" => 202,
            "status" => 'Paper does not exist',
            "id" => null,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "id" => 99);
        $this->assertEquals($errorresponsearray, $assessment->delete($params, $userid));
        // Test deleting a paper in use - first add an entry in log_metadata.
        $errorresponsearray = array(
            "statuscode" => 203,
            "status" => 'Assessment not deleted, as has been taken by a user',
            "id" => null,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 3);
        $params = array(
            "nodeid" => 3,
            "id" => 2);
        $this->assertEquals($errorresponsearray, $assessment->delete($params, $userid));
        // Test deleting a paper in use - second add an entry in log4_overall.
        $errorresponsearray = array(
            "statuscode" => 203,
            "status" => 'Assessment not deleted, as has been taken by a user',
            "id" => null,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 4);
        $params = array(
            "nodeid" => 4,
            "id" => 3);
        $this->assertEquals($errorresponsearray, $assessment->delete($params, $userid));
    }
    
}
