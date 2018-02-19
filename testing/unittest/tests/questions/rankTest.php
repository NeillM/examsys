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
 * Test rank question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class ranktest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $render = questionrender::get_render('rank');
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertFalse($render->get('displayscenario'));
    $this->assertTrue($render->get('displayleadin'));
    $this->assertFalse($render->get('displaymedia'));
    $render->notes = 'test';
    $render->scenario = 'test';
    $render->qmedia = 'test';
    $render->set_question_head();
    $this->assertTrue($render->get('displaynotes'));
    $this->assertTrue($render->get('displayscenario'));
    $this->assertTrue($render->get('displaymedia'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $render = questionrender::get_render('rank');
    $render->papertype = '0'; 
    $render->scoremethod = 'Mark per Option';
    $render->optionnumber = 3;
    $render->marks = 0;
    $question['options'][0]['correct'] = 1;
    $question['options'][1]['correct'] = 2;
    $question['options'][2]['correct'] = 0;
    $render->question = $question;
    $option['tmppartid'] = 1;
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $render->set_opt(1, $option);
    $useranswerid = 'u,u,u';
    $render->set_option(1, $useranswerid, '100', 1);
    $option = $render->get_opt(1);
    $this->assertTrue($render->get('unanswered'));
    $this->assertTrue($option['unans']);
    $this->assertTrue($option['na']);
    $this->assertFalse($option['selected']);
    $this->assertTrue($option['inact']);
    $this->assertEquals(1, $render->get('marks'));
    $option['tmppartid'] = 1;
    $render->set_opt(1, $option);
    $useranswerid = '1,u,u';
    $render->set_option(1, $useranswerid, '000', 1);
    $this->assertFalse($render->get('unanswered'));
    $option = $render->get_opt(1);
    $this->assertFalse($option['unans']);
    $this->assertTrue($option['selected']);
    $this->assertFalse($option['inact']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $render = questionrender::get_render('rank');
  }
}