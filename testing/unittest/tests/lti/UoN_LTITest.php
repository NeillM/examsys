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

// LTI classes not in the usual place so not in base namespace.
require_once 'LTI/ims-lti/UoN_LTI.php';

/**
 * Test uon lti class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class uonltitest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "lti" . DIRECTORY_SEPARATOR . "uonlti.yml");
    }

    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "lti" . DIRECTORY_SEPARATOR . $name . ".yml");
    }

    /**
     * Test lti context lookup
     * @group lti
     */
    public function test_lookup_lti_context() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        // Test context lookup for a student.
        $expected = array('TEST',"2016-02-11 16:29:11");
        $this->assertEquals($expected, $lti->lookup_lti_context('test:1'));
        // Test context lookup for a staff memeber.
        $expected = array('TEST',"2016-02-11 17:29:11");
        $this->assertEquals($expected, $lti->lookup_lti_context('test:2'));
    }

    /**
     * Test the get_user_by_external_id method.
     * @group lti
     */
    public function test_get_user_by_external_id() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            '1000-1' => array(
                'id' => 1000,
                'title' => 'Miss',
                'surname' => 'test',
                'firstnames' => 'one',
                'initials' => 'o',
                'username' => 'unit',
                'externalid' => '1',
            ),
        );
        $this->assertEquals($expected, $lti->get_user_by_external_id('1', 'test'));
    }

    /**
     * Test the get_links_by_username method
     * @group lti
     */
    public function test_get_links_by_username() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            '1001-2' => array(
                'id' => 1001,
                'title' => 'Mx',
                'surname' => 'staff',
                'firstnames' => 'two',
                'initials' => 't',
                'username' => 'staff',
                'externalid' => '2',
            ),
        );
        $this->assertEquals($expected, $lti->get_links_by_username('staff', 1));
    }

    /**
     * Test the get_lti_key method
     * @group lti
     */
    public function test_get_lti_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            'id' => 1,
            'oauth_consumer_key' => 'test',
            'secret' => 'testsecret',
            'name' => 'test lti',
            'context_id' => '',
        );
        $this->assertEquals($expected, $lti->get_lti_key(1));
    }

    /**
     * Test the delete_user_link method
     * @group lti
     */
    public function test_delete_user_link() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->delete_user_link(1000, 'test', '1');
        $querytable = $this->getConnection()->createQueryTable('lti_user', 'SELECT * FROM lti_user');
        $expectedtable = $this->get_expected_data_set('deleteuserlink')->getTable("lti_user");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test the generate_user_key method
     * @group lti
     */
    public function test_generate_user_key() {
        $lti = new UoN_LTI();
        $this->assertEquals('test:1', $lti->generate_user_key('test', '1'));
        $this->assertEquals('test:1', $lti->generate_user_key('test', 1));
        $this->assertEquals('myspecialkey:username', $lti->generate_user_key('myspecialkey', 'username'));
    }
}
