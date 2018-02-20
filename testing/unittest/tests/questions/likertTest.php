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

use testing\unittest\unittest;

/**
 * Test likert question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class likerttest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('likert');
    $data->set_question_head();
    $this->assertTrue($data->get('displaydefault'));
    $this->assertFalse($data->get('displaymedia'));
    $data->qmedia = 'test';
    $data->set_question_head();
    $this->assertTrue($data->get('displaymedia'));
  }

    /**
    * Test question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('likert');
    $data->displaymethod =  '0|1|2|3|4|true';
    $data->set_question(0, '', '');
    $this->assertFalse($data->get('displaynotes'));
    $this->assertFalse($data->get('displayscenario'));
    $this->assertTrue($data->get('displayna'));
    $this->assertEquals(array(0, 1, 2, 3, 4), $data->get('scale'));
    $data->displaymethod = '0|1|2|3|4|false';
    $data->notes = 'note';
    $data->scenario = 'scenario';
    $data->set_question(0, '', '');
    $this->assertFalse($data->get('displayna'));
    $this->assertEquals(array(0, 1, 2, 3, 4), $data->get('scale'));
    $this->assertTrue($data->get('displaynotes'));
    $this->assertTrue($data->get('displayscenario'));
    $this->assertEquals(6, $data->get('likertnotescolspan'));
    $this->assertEquals(7, $data->get('likertscenariocolspan'));
    
    }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $data = questiondata::get_datastore('likert');
    $data->displaymethod =  '0|1|2|3|4|true';
    $data->questionno =  '1';
    $data->set_option(0, '4', '', 1);
    $this->assertFalse($data->get('unanswered'));
    $this->assertFalse($data->get('na'));
    $this->assertEquals('1_0', $data->get('id'));
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => true, 5 => false), $data->get('scaleopt'));
    $data->set_option(0, 'n/a', '', 1);
    $this->assertTrue($data->get('na'));
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => false, 5 => false), $data->get('scaleopt'));
    $data->displaymethod = '0|1|2|3|4|false';
    $data->set_option(0, 'u', '', 1);
    $this->assertFalse($data->get('na'));
    $this->assertTrue($data->get('unanswered'));
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => false, 5 => false), $data->get('scaleopt'));
  }

}