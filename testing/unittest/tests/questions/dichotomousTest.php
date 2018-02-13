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
 * Test fill in the dichotomous question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class dichotomoustest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\dichotomous\render';
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
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $pluginns = 'plugins\questions\dichotomous\render';
    $render = new $pluginns();
    $option['markscorrect'] = 1;
    $option['tmppartid'] = 1;
    $option['omedia'] = '';
    $render->set_opt(0, $option);
    $render->set('displaymethod', 'TF_Positive');
    $render->set('marks', 0);
    $useranswerid = 'uuu';
    $render->set_option(0, $useranswerid, '', 1);
    $option = $render->get_opt(0);
    $this->assertTrue($render->get('unanswered'));
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($option['abstain']);
    $this->assertEquals(1, $render->get('marks'));
    $option['omedia'] = 'test';
    $render->set_opt(0, $option);
    $render->set('displaymethod', 'TF_NegativeAbstain');
    $render->set_option(0, $useranswerid, '', 1);
    $option = $render->get_opt(0);
    $this->assertTrue($option['abstain']);
    $this->assertTrue($option['displayoptionmedia']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $pluginns = 'plugins\questions\dichotomous\render';
    $render = new $pluginns();
    $useranswerid = 'uuu';
    $option['marksincorrect'] = -1;
    $render->set_opt(0, $option);
    $render->set_additional_option(0, $useranswerid, '', 1);
    $this->assertTrue($render->get('negativemarking'));
    $option['marksincorrect'] = 0;
    $render->set_opt(0, $option);
    $render->set_additional_option(0, $useranswerid, '', 1);
    $this->assertFalse($render->get('negativemarking'));
  }
  
}