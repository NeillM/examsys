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
        $moduleshortcode = 'B34ADD-UK-AUT1516';
        $moduletitle = 'Advanced Drug Discovery';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('SMS', $exploded[0], 'UK', 'UNKNOWN School', 0, "SATURN MISSING:$moduletitle"));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
        // Default.
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $moduleshortcode = 'PHAR4018';
        $expected = array(array('Manual', $moduleshortcode, 'CampusTODO', 'SchoolTODO', 0, "MISSING:$moduletitle"));
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
    }
    /**
     * Test allow staff edit  
     * @group lti 
     */
    public function test_allow_staff_edit_link() {
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertFalse($lti_i->allow_staff_edit_link());
    }
    /**
     * Test allow self reg  
     * @group lti 
     */
    public function test_allow_module_self_reg() {
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $this->config->set('cfg_lti_allow_module_self_reg', true);
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertTrue($lti_i->allow_module_self_reg());
        $this->config->set('cfg_lti_allow_module_self_reg', false);
        $this->assertFalse($lti_i->allow_module_self_reg());
    }
    /**
     * Test allow staff self reg  
     * @group lti 
     */
    public function test_allow_staff_module_register() {
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $this->config->set('cfg_lti_allow_staff_module_register', true);
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertTrue($lti_i->allow_staff_module_register());
        $this->config->set('cfg_lti_allow_staff_module_register', false);
        $this->assertFalse($lti_i->allow_staff_module_register());
    }
    /**
     * Test allow module creation 
     * @group lti 
     */
    public function test_allow_module_create() {
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $this->config->set('cfg_lti_allow_module_create', true);
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertTrue($lti_i->allow_module_create());
        $this->config->set('cfg_lti_allow_module_create', false);
        $this->assertFalse($lti_i->allow_module_create());
    }
    /**
     * Test user time check
     * @group lti 
     */
    public function test_user_time_check() {
        // Default.
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertFalse($lti_i->user_time_check('now'));
        $this->assertFalse($lti_i->user_time_check('2015-02-15 15:28:37'));
        // UoN.
        $this->config->set('lti_integration', 'UoN');
        $this->config->set('cfg_sms_api', 'uon_saturn');
        $lti_i = $lti->load();
        $this->assertFalse($lti_i->user_time_check('now'));
        $this->assertTrue($lti_i->user_time_check('2015-02-15 15:28:37'));
    }
}
