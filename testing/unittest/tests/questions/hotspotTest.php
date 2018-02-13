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
 * Test hotspot question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class hotspottest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\hotspot\render';
    $render = new $pluginns();
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertFalse($render->get('displayscenario'));
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
    $pluginns = 'plugins\questions\hotspot\render';
    $render = new $pluginns();
    $useranswerid = 'u';
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertTrue($render->get('unanswered'));
    $useranswerid = '1,325,995|1,825,965';
    $render->set('mediaheight', 1600);
    $render->set('mediawidth', 1600);
    $option['correct'] = 'Chocolate calculator~16711680~polygon~16a,399,152,3c7,1a9,3ed,106,407,f9,3a6~0~|Dictionary~16776960~ellipse~392,382,2d1,418~0~';
    $option['markscorrect'] = 1;
    $render->set_opt(0, $option);
    $render->set('scoremethod', 'Mark per Question');
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertFalse($render->get('unanswered'));
    $this->assertEquals($option['correct'], $render->get('tmpcorrect'));
    $this->assertEquals(1900, $render->get('mediawidth'));
    $this->assertEquals(1601, $render->get('mediaheight'));
    $this->assertEquals($useranswerid, $render->get('useranswer'));
    $this->assertEquals(1, $render->get('screensubmitted'));
    $this->assertEquals(1, $render->get('marks'));
    $render->set('scoremethod', 'Mark per Option');
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertEquals(2, $render->get('marks'));
  }

}