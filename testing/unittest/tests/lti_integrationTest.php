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
 * Test lti integration class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class lti_integrationtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "lti_integrationTest" . DIRECTORY_SEPARATOR . "lti_integration.yml");
    }
    /**
     * Test module code translate
     * @group lti
     */
    public function test_module_code_translate() {
        // UoN.
        $this->config->set('lti_integration', 'UoN');
        $this->config->set('cfg_sms_api', 'uon_saturn');
        // 1. Saturn.
        $c_internal_id = 'B34ADD-UK-AUT1516';
        $course_title = 'Advanced Drug Discovery';
        $exploded = explode('-', $c_internal_id);
        $expected = array(array('SMS', $exploded[0], 'UK', 'UNKNOWN School', 0, "SATURN MISSING:$course_title"));
        $lti = lti_integration::load();
        $this->assertEquals($expected, $lti::module_code_translate($this->db, $c_internal_id, $course_title));
        // Default.
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $c_internal_id = 'PHAR4018';
        $expected = array(array('Manual', $c_internal_id, 'CampusTODO', 'SchoolTODO', 0, "MISSING:$course_title"));
        $lti = lti_integration::load();
        $this->assertEquals($expected, $lti::module_code_translate($this->db, $c_internal_id, $course_title));
    }
}
