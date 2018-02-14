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
 * Test mrq question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class mrqtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\mrq\render';
    $render = new $pluginns();
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertFalse($render->get('displayscenario'));
    $this->assertTrue($render->get('displayleadin'));
    $this->assertFalse($render->get('displaymedia'));
    $render->set('notes', 'test');
    $render->set('scenario', 'test');
    $render->set('qmedia', 'test');
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
    $pluginns = 'plugins\questions\mrq\render';
    $render = new $pluginns();
    $useranswerid = 'nnnnn';
    $render->set_question(1, $useranswerid, '', 2);
    $this->assertTrue($render->get('unanswered'));
    $this->assertEquals(2, $render->get('allowedresponses'));
    $useranswerid = 'nnnyn';
    $render->set_question(1, $useranswerid, '', 2);
    $this->assertFalse($render->get('unanswered'));
    $useranswerid = 'nynyn';
    $render->set_question(1, $useranswerid, '', 2);
    $this->assertFalse($render->get('unanswered'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $pluginns = 'plugins\questions\mrq\render';
    $render = new $pluginns();
    $option['tmppartid'] = 1;
    $option['optiontext'] = '';
    $option['omedia'] = '';
    $option['correct'] = 'n';
    $option['markscorrect'] = 1;
    $render->set_opt(1, $option);
    $render->set('marks', 0);
    $render->set('scoremethod', 'Mark per Option');
    $useranswerid = 'nnny';
    $render->set_option(1, $useranswerid, '1000', 1);
    $option = $render->get_opt(1);
    $this->assertFalse($option['selected']);
    $this->assertTrue($option['inact']);
    $this->assertFalse($option['optiontextdisplay']);
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($render->get('negativemarking'));
    $this->assertFalse($render->get('abstainselected'));
    $this->assertEquals(0, $render->get('marks'));
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $pluginns = 'plugins\questions\mrq\render';
    $render = new $pluginns();
    // Test other.
    $useranswerid = 'nnnyn';
    $render->set('optionnumber', 5);
    $option['marksincorrect'] = 0;
    $option['tmppartid'] = 4;
    $option['correct'] = 'y';
    $render->set_opt(4, $option);
    $render->set('displaymethod', 'other');
    $render->set('partid', 4);
    $useranswerid = 'nnnnytest';
    $render->set_additional_option(4, $useranswerid, '00000', 1);
    $this->assertEquals(5, $render->get('partid'));
    $this->assertTrue($render->get('otherselected'));
    $this->assertEquals('test', $render->get('other'));
    // Test dismiss.
    $option['marksincorrect'] = -1;
    $render->set_opt(1, $option);
    $render->set_additional_option(1, $useranswerid, '01000', 1);
    $this->assertEquals('01000', $render->get('dismiss'));
    $render->set_additional_option(1, $useranswerid, '', 1);
    $this->assertEquals('00000', $render->get('dismiss'));
    // Test abstain.
    $useranswerid = 'a';
    $option['markscorrect'] = -1;
    $render->set_additional_option(1, $useranswerid, '1000', 1);
    $option = $render->get_opt(1);
    $this->assertTrue($render->get('negativemarking'));
    $this->assertTrue($render->get('abstainselected'));
  }
}