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
    $render = questionrender::get_render('likert');
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaymedia'));
    $render->qmedia = 'test';
    $render->set_question_head();
    $this->assertTrue($render->get('displaymedia'));
  }

    /**
    * Test question setter
    * @group question
    */
  public function test_set_question() {
    $render = questionrender::get_render('likert');
    $render->displaymethod =  '0|1|2|3|4|true';
    $render->set_question(0, '', '');
    $this->assertFalse($render->get('displaynotes'));
    $this->assertFalse($render->get('displayscenario'));
    $this->assertTrue($render->get('displayna'));
    $this->assertEquals(array(0, 1, 2, 3, 4), $render->get('scale'));
    $render->displaymethod = '0|1|2|3|4|false';
    $render->notes = 'note';
    $render->scenario = 'scenario';
    $render->set_question(0, '', '');
    $this->assertFalse($render->get('displayna'));
    $this->assertEquals(array(0, 1, 2, 3, 4), $render->get('scale'));
    $this->assertTrue($render->get('displaynotes'));
    $this->assertTrue($render->get('displayscenario'));
    $this->assertEquals(6, $render->get('likertnotescolspan'));
    $this->assertEquals(7, $render->get('likertscenariocolspan'));
    
    }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $render = questionrender::get_render('likert');
    $render->displaymethod =  '0|1|2|3|4|true';
    $render->questionno =  '1';
    $render->set_option(0, '4', '', 1);
    $this->assertFalse($render->get('unanswered'));
    $this->assertFalse($render->get('na'));
    $this->assertEquals('1_0', $render->get('id'));
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => true, 5 => false), $render->get('scaleopt'));
    $render->set_option(0, 'n/a', '', 1);
    $this->assertTrue($render->get('na'));
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => false, 5 => false), $render->get('scaleopt'));
    $render->displaymethod = '0|1|2|3|4|false';
    $render->set_option(0, 'u', '', 1);
    $this->assertFalse($render->get('na'));
    $this->assertTrue($render->get('unanswered'));
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => false, 5 => false), $render->get('scaleopt'));
  }

}