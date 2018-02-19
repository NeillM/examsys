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
 * Test mcq question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class mcqtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $render = questionrender::get_render('mcq');
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
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $render = questionrender::get_render('mcq');
    $render->displaymethod = 'vertical_other';
    $render->set_question(1, '0', '');
    $this->assertEquals('vertical', $render->get('displaymethod'));
    $this->assertTrue($render->get('unanswered'));
    $render->displaymethod = 'dropdown';
    $render->set_question(1, '4', '');
    $this->assertEquals('dropdown', $render->get('displaymethod'));
    $this->assertFalse($render->get('unanswered'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $render = questionrender::get_render('mcq');
    $option['tmppartid'] = 4;
    $option['optiontext'] = '';
    $option['omedia'] = '';
    $option['correct'] = '1';
    $option['markscorrect'] = 2;
    $render->set_opt(1, $option);
    $render->marks = 0;
    $render->displaymethod = 'vertical';
    $render->set_option(1, '4', '0000', 1);
    $option = $render->get_opt(1);
    $this->assertFalse($option['optiontextdisplay']);
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($option['inact']);
    $this->assertTrue($option['selected']);
    $this->assertEquals(2, $render->get('marks'));
    $option['tmppartid'] = 1;
    $render->set_opt(1, $option);
    $render->set_option(1, '2', '1000', 1);
    $option = $render->get_opt(1);
    $this->assertTrue($option['inact']);
    $this->assertFalse($option['selected']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $render = questionrender::get_render('mcq');
    // Test dismiss.
    $useranswerid = 'u';
    $render->optionnumber = 4;
    $option['marksincorrect'] = -1;
    $render->set_opt(1, $option);
    $render->set_additional_option(1, $useranswerid, '0100', 1);
    $this->assertTrue($render->get('negativemarking'));
    $this->assertFalse($render->get('abstainselected'));
    $this->assertEquals('0100', $render->get('dismiss'));
    $option['marksincorrect'] = 0;
    $render->set_opt(1, $option);
    $render->set_additional_option(1, $useranswerid, '', 1);
    $this->assertFalse($render->get('negativemarking'));
    $this->assertFalse($render->get('abstainselected'));
    $this->assertEquals('0000', $render->get('dismiss'));
    // Test abstain.
    $useranswerid = 'a';
    $option['marksincorrect'] = -1;
    $render->set_opt(1, $option);
    $render->set_additional_option(1, $useranswerid, '', 1);
    $this->assertTrue($render->get('negativemarking'));
    $this->assertTrue($render->get('abstainselected'));
    $this->assertEquals('0000', $render->get('dismiss'));
    // Test other.
    $render->displaymethod =  'vertical';
    $render->papertype = '3';
    $useranswerid = 'other:test';
    $render->set_additional_option(1, $useranswerid, '', 1);
    $this->assertEquals('other', $render->get('displaymethod'));
    $this->assertTrue($render->get('otherselected'));
    $this->assertEquals('test', $render->get('other'));
  }
}