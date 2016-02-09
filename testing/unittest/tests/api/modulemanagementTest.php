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
 * Test modulemanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class modulemanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "modulemanagementTest" . DIRECTORY_SEPARATOR . "modulemanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "modulemanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test module creation
     * @group api
     */
    public function test_create() {
    }
    /**
     * Test module enrolment
     * @group api
     */
    public function test_enrol() {
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        // Test module enrolment - SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 2,
            "error" => null,
            "node" => 'enrol',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "userid" => 1000,
            "moduleid" => 1,
            "session" => 2016,
            "attempt" => 1);
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
        // Test module enrolment - ERROR module does not exist.
        $responsearray = array(
            "statuscode" => 508,
            "status" => 'User not enrolled',
            "id" => null,
            "error" => null,
            "node" => 'enrol',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "userid" => 1000,
            "moduleid" => 99,
            "session" => 2016,
            "attempt" => 1);
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
        // Test module enrolment - ERROR invalid user.
        $responsearray = array(
            "statuscode" => 507,
            "status" => 'User does not exist',
            "id" => null,
            "error" => null,
            "node" => 'enrol',
            "nodeid" => 3);
        $params = array(
            "nodeid" => 3,
            "userid" => 999,
            "moduleid" => 1,
            "session" => 2016,
            "attempt" => 1);
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
    }
    /**
     * Test module un-enrolment
     * @group api
     */
    public function test_unenrol() {
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        // Test module enrolment - SUCCESS.
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1,
            "error" => null,
            "node" => 'unenrol',
            "nodeid" => 1);
        $params = array(
            "nodeid" => 1,
            "userid" => 1000,
            "moduleid" => 3,
            "session" => 2016);
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
        // Test module enrolment - ERROR no enrolment to unenrol.
        $responsearray = array(
            "statuscode" => 509,
            "status" => 'User not un-enrolled',
            "id" => null,
            "error" => null,
            "node" => 'unenrol',
            "nodeid" => 2);
        $params = array(
            "nodeid" => 2,
            "userid" => 1000,
            "moduleid" => 3,
            "session" => 2016);
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
        // Test module enrolment - ERROR invalid user.
        $responsearray = array(
            "statuscode" => 507,
            "status" => 'User does not exist',
            "id" => null,
            "error" => null,
            "node" => 'unenrol',
            "nodeid" => 3);
        $params = array(
            "nodeid" => 3,
            "userid" => 999,
            "moduleid" => 1,
            "session" => 2016);
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
    }
    /**
     * Test module deletion
     * @group api
     */
    public function test_delete() {
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        // Test module deletion - SUCCESS.
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
        $this->assertEquals($responsearray, $module->delete($params, $userid));
        // Check that the remaining modules are correct, when we delete a module we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('modules', 'SELECT id, moduleid, fullname, active, schoolid, academic_year_start FROM modules WHERE mod_deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletemodule')->getTable("modules");  
        $this->assertTablesEqual($expectedtable, $querytable);
        // Test deleting a non existance module.
        $responsearray['statuscode'] = 501;
        $responsearray['status'] = 'Module does not exist';
        $responsearray['id'] = null;
        $responsearray['nodeid'] = 2;
        $params['nodeid'] = 2;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $module->delete($params, $userid));
        // Test deleting a module in use - first check module has a paper.
        $responsearray['statuscode'] = 502;
        $responsearray['status'] = 'Module not deleted, as linked to a paper or enrolement';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $module->delete($params, $userid)); 
        // Test deleting a module in use - second check module has a user.
        $responsearray['statuscode'] = 502;
        $responsearray['status'] = 'Module not deleted, as linked to a paper or enrolement';
        $responsearray['nodeid'] = 3;
        $params['nodeid'] = 3;
        $params['id'] = 3;
        $this->assertEquals($responsearray, $module->delete($params, $userid)); 
    }
}
