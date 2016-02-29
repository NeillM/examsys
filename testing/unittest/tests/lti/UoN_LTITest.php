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
}
