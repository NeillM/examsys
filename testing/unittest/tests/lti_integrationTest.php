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
     * Test sms spi - saturn sms
     * @group lti
     */
    public function test_sms_api_saturn() {
        // Saturn module.
        $this->config->set('lti_integration', 'UoN');
        $this->config->set('cfg_sms_api', 'uon_saturn');
        $data = array('SMS', 'B34ADD', 'UK', 'UNKNOWN School', 0, "SATURN MISSING:Advanced Drug Discovery");
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $expected = '/touchstone.ashx?campus=uk';
        $this->assertEquals($expected, $lti_i->sms_api($data));
        // Fake module.
        $data = array('Manual', 'FAKE_UNNC', 'CN', 'UNKNOWN School', 1, "Fake module");
        $expected = '';
        $this->assertEquals($expected, $lti_i->sms_api($data));
    }
    /**
     * Test sms spi - campus sms
     * @group lti
     */
    public function test_sms_api_cs() {
        // Campus solutions module.
        $this->config->set('lti_integration', 'UoN');
        $this->config->set('cfg_sms_api', '');
        $data = array('SMS', 'COMP1002', 'UNUK', 'UNKNOWN School', 0, "Mathematics for Computer Science");
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertFalse($lti_i->sms_api($data));
        // Fake module.
        $data = array('Manual', 'FAKE_UNNC', 'UNNC', 'UNKNOWN School', 1, "Fake module");
        $expected = '';
        $this->assertEquals($expected, $lti_i->sms_api($data));
    }
    /**
     * Test sms spi - generic sms
     * @group lti
     */
    public function test_sms_api_default() {
        // Generic module.
        $this->config->set('lti_integration', 'default');
        $this->config->set('cfg_sms_api', 'generic_sms');
        // Fake module.
        $data = array('Manual', 'FAKE_UNNC', 'CN', 'UNKNOWN School', 1, "Fake module");
        $expected = '';
        $this->assertEquals($expected, $lti_i->sms_api($data));
    }
    /**
     * Test module code translate - saturn sms
     * @group lti
     */
    public function test_module_code_translate_saturn() {
        // Saturn module.
        $this->config->set('lti_integration', 'UoN');
        $this->config->set('cfg_sms_api', 'uon_saturn');
        $moduleshortcode = 'B34ADD-UK-AUT1516';
        $moduletitle = 'Advanced Drug Discovery';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('SMS', $exploded[0], 'UK', 'UNKNOWN School', 0, "SATURN MISSING:$moduletitle"));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
        // Meta module.
        $moduleshortcode = 'CS11JA-CN-AUT-CS11JB-CN-SPR-1213';
        $moduletitle = '10 Credits Stage 1 Japanese (CS11JA CN AUT) (CS11JB CN SPR) (12-13) [p]';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('SMS', 'CS11JA_UNNC', 'CN', 'UNKNOWN School', 0, "SATURN MISSING:10 Credits Stage 1 Japanese"),
            array('SMS', 'CS11JB_UNNC', 'CN', 'UNKNOWN School', 0, "SATURN MISSING:10 Credits Stage 1 Japanese"));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        // Fake module.
        $moduleshortcode = 'ZZ-FAKE-CN';
        $moduletitle = 'Fake module';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('Manual', 'FAKE_UNNC', 'CN', 'UNKNOWN School', 1, $moduletitle));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
        // Invalid module.
        $moduleshortcode = 'INVALID';
        $moduletitle = 'Invalid module';
        $lti_i = $lti->load();
        $this->assertFalse($lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
    }
    /**
     * Test module code translate - campus solutions sms
     * @group lti
     */
    public function test_module_code_translate_cs() {
        // Campus solutions module.
        $this->config->set('lti_integration', 'UoN');
        $this->config->set('cfg_sms_api', '');
        $moduleshortcode = 'COMP1112-3-UNUK-SPR-1617';
        $moduletitle = 'Mathematics for Computer Science';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('SMS', $exploded[0], 'UNUK', 'UNKNOWN School', 0, $moduletitle));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
        // Meta module.
        $moduleshortcode = 'COMP1111-2-UNNC-SPR-COMP1112-3-UNUK-SPR-1617';
        $moduletitle = 'Mathematics for Computer Science (COMP1111 CN SPR) (COMP1112 UK SPR) (16-17) [p]';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('SMS', 'COMP1111_UNNC', 'UNNC', 'UNKNOWN School', 0, "Mathematics for Computer Science"),
            array('SMS', 'COMP1112', 'UNUK', 'UNKNOWN School', 0, "Mathematics for Computer Science"));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
        // Fake module.
        $moduleshortcode = 'ZZ-FAKE-UNNC';
        $moduletitle = 'Fake module';
        $exploded = explode('-', $moduleshortcode);
        $expected = array(array('Manual', 'FAKE_UNNC', 'UNNC', 'UNKNOWN School', 1, $moduletitle));
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertEquals($expected, $lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
        // Invalid module.
        $moduleshortcode = 'INVALID';
        $moduletitle = 'Invalid module';
        $lti = UoN_LTI::get_instance();
        $lti_i = $lti->load();
        $this->assertFalse($lti_i->module_code_translate($this->db, $moduleshortcode, $moduletitle));
    }
    /**
     * Test module code translate - default sms
     * @group lti
     */
    public function test_module_code_translate_default() {
        // Default.
        $this->config->set('cfg_sms_api', 'generic_sms');
        $this->config->set('lti_integration', 'default');
        $moduleshortcode = 'PHAR4018';
        $moduletitle = 'Advanced Drug Discovery';
        $expected = array(array('Manual', $moduleshortcode, 'CampusTODO', 'SchoolTODO', 0, "MISSING:$moduletitle"));
        $lti = UoN_LTI::get_instance();
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
