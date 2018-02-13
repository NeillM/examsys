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
 * Test area question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class areatest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\area\render';
    $render = new $pluginns();
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertFalse($render->get('displayscenario'));
    $this->assertTrue($render->get('displayleadin'));
    $render->set('notes', 'test');
    $render->set('scenario', 'test');
    $render->set_question_head();
    $this->assertTrue($render->get('displaynotes'));
    $this->assertTrue($render->get('displayscenario'));
  }
 
  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $pluginns = 'plugins\questions\area\render';
    $render = new $pluginns();
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $render->set_opt(0, $option);
    $render->set('marks', 1);
    $render->set('mediawidth', 10);
    $render->set('mediaheight', 10);
    $useranswerid = '100,0,0,0,0,7397;d5,69,df,64,d5,69, ';
    $render->set_option(0, $useranswerid, '', 0);
    $this->assertFalse($render->get('unanswered'));
    $this->assertEquals(12, $render->get('mediawidth'));
    $this->assertEquals(37, $render->get('mediaheight'));
    $this->assertEquals(1, $render->get('areadisplay'));
    $this->assertEquals('d5,69,df,64,d5,69', $render->get('areauseranswer'));
    $this->assertEquals($useranswerid, $render->get('areafulluseranswer'));
    $this->assertEquals(2, $render->get('marks'));
  }

}