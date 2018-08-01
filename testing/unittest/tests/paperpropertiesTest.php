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
use PHPUnit\DbUnit\DataSet\YamlDataSet;

/**
 * Test paperproperties class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperpropertiestest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new YamlDataSet($this->get_base_fixture_directory() . "paperpropertiesTest" . DIRECTORY_SEPARATOR . "paperproperties.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new YamlDataSet($this->get_base_fixture_directory() . "paperpropertiesTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test setting paper password
     * @group paper
     */
    public function test_set_password() {
        // Load user id 1.
        $this->userobject->load(1);
        // Set new password.
        $newpassword = 'newpassword';
        $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
        $properties->set_password($newpassword);
        $properties->save();
        // Check password updating.
        $paperproperty = PaperProperties::get_paper_properties_by_id(45, $this->db, ''); // Get a fresh property object to check if password saved
        $savedencryptedpass = $paperproperty->get_password();
        $this->assertNotEquals($newpassword, $savedencryptedpass);
        $savedpass = $paperproperty->get_decrypted_password();
        $this->assertEquals($newpassword, $savedpass);
        $actual = $this->getConnection()->createQueryTable('track_changes', 'SELECT id, type, typeID, editor, new, old, part FROM track_changes');
        $expected = $this->get_expected_data_set('paperproperties_updated')->getTable("track_changes");
        // Check track changes masks password.
        $this->assertTablesEqual($expected, $actual);
    }

    /**
     * Test calculation question getter - all users
     * @group paper
     */
    public function test_get_enhancedcalc_questions_all() {
        // Load user id 1.
        $this->userobject->load(1);
        $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
        $expected = array(1, 2, 3);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(0));
    }

    /**
     * Test calculation question getter - all users after unmarked_enhancedcalc called for student
     * @group paper
     */
    public function test_get_enhancedcalc_questions_all_2() {
        // Load user id 1.
        $this->userobject->load(1);
        $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
        $properties->unmarked_enhancedcalc(0);
        $properties->unmarked_enhancedcalc(1);
        $expected = array(1, 2, 3);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(0));
    }

    /**
     * Test calculation question getter - student users
     * @group paper
     */
    public function test_get_enhancedcalc_questions_student() {
        // Load user id 1.
        $this->userobject->load(1);
        $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
        $expected = array(1, 2);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(1));
    }

    /**
     * Test calculation question getter - student users after unmarked_enhancedcalc called for all
     * @group paper
     */
    public function test_get_enhancedcalc_questions_student_2() {
        // Load user id 1.
        $this->userobject->load(1);
        $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
        $properties->unmarked_enhancedcalc(1);
        $properties->unmarked_enhancedcalc(0);
        $expected = array(1, 2);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(1));
    }

    /**
     * Test building a paper
     * @group paper
     */
    public function test_build_paper() {
      $properties = PaperProperties::get_paper_properties_by_id(1, $this->db, '');
      $expected = array(1 => array(
        'assigned_number' => 1,
        'no_on_screen' => 1,
        'screen' => 1,
        'theme' => 'test theme',
        'scenario' => 'test scenario',
        'leadin' => 'test leadin',
        'notes' => 'test notes',
        'q_type' => 'enhancedcalc',
        'q_id' => 2,
        'display_pos' => 1,
        'score_method' => 'Allow partial Marks',
        'display_method' => null,
        'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":0,"show_units":true,"answers":[{"formula":"$A*$B","units":"cm"}],"vars":{"$A":{"min":"2","max":"10","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}',
        'q_media' => null,
        'q_media_width' => 0,
        'q_media_height' => 0,
        'q_option_order' => 'display order',
        'dismiss' => '',
        'options' => array(0 => array(
            'correct' => null,
            'option_text' => null,
            'o_media' => null,
            'o_media_width' => null,
            'o_media_height' => null,
            'marks_correct' => null,
            'marks_incorrect' => null,
            'marks_partial' => null
          )),
        ),
        2 => array(
        'assigned_number' => 2,
        'no_on_screen' => 2,
        'screen' => 1,
        'theme' => 'test theme 2',
        'scenario' => 'test scenario 2',
        'leadin' => 'test leadin 2',
        'notes' => 'test notes 2',
        'q_type' => 'mcq',
        'q_id' => 6,
        'display_pos' => 2,
        'score_method' => 'Mark per Option',
        'display_method' => 'vertical',
        'settings' => '[]',
        'q_media' => '1517406311.png',
        'q_media_width' => '480',
        'q_media_height' => '105',
        'q_option_order' => 'random',
        'dismiss' => '',
        'options' => array(0 => array(
            'correct' => 1,
            'option_text' => 'true',
            'o_media' => '1517409282.jpg',
            'o_media_width' => 951,
            'o_media_height' => 121,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0
          ),
          1 => array(
            'correct' => 1,
            'option_text' => 'false',
            'o_media' => '',
            'o_media_width' => 0,
            'o_media_height' => 0,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0
          ),
          2 => array(
            'correct' => 1,
            'option_text' => 'maybe',
            'o_media' => '',
            'o_media_width' => 0,
            'o_media_height' => 0,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0
          ),
        )
      ));
      $this->assertEquals($expected, $properties->build_paper(false, null, null));
    }

    /**
     * Test building a paper = question preview
     * @group paper
     */
    public function test_build_paper_question_preview() {
      $properties = PaperProperties::get_paper_properties_by_id(1, $this->db, '');
      $expected = array(1 => array(
        'assigned_number' => 1,
        'no_on_screen' => 1,
        'screen' => 1,
        'theme' => 'test theme',
        'scenario' => 'test scenario',
        'leadin' => 'test leadin',
        'notes' => 'test notes',
        'q_type' => 'enhancedcalc',
        'q_id' => 2,
        'display_pos' => 1,
        'score_method' => 'Allow partial Marks',
        'display_method' => null,
        'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":0,"show_units":true,"answers":[{"formula":"$A*$B","units":"cm"}],"vars":{"$A":{"min":"2","max":"10","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}',
        'q_media' => null,
        'q_media_width' => 0,
        'q_media_height' => 0,
        'q_option_order' => 'display order',
        'dismiss' => '',
        'options' => array(0 => array(
            'correct' => null,
            'option_text' => null,
            'o_media' => null,
            'o_media_width' => null,
            'o_media_height' => null,
            'marks_correct' => null,
            'marks_incorrect' => null,
            'marks_partial' => null
          )),
        ));
      $this->assertEquals($expected, $properties->build_paper(true, 2, 1));
    }

    /**
     * Test display timer
     * @group paper
     */
    public function test_display_timer() {
      // Summative - no timed modules
      $properties = PaperProperties::get_paper_properties_by_id(1, $this->db, '');
      $this->assertFalse($properties->display_timer());
      // Summative - timed modules
      $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
      $this->assertTrue($properties->display_timer());
      // Progressive - timed
      $properties = PaperProperties::get_paper_properties_by_id(2, $this->db, '');
      $this->assertTrue($properties->display_timer());
      // Formative - not timed
      $properties = PaperProperties::get_paper_properties_by_id(3, $this->db, '');
      $this->assertFalse($properties->display_timer());
    }

}