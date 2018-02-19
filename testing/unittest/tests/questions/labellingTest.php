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
 * Test labelling question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class labellingtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $render = questionrender::get_render('labelling');
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertFalse($render->get('displayscenario'));
    $this->assertTrue($render->get('displayleadin'));
    $render->notes = 'test';
    $render->scenario = 'test';
    $render->set_question_head();
    $this->assertTrue($render->get('displaynotes'));
    $this->assertTrue($render->get('displayscenario'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $render = questionrender::get_render('labelling');
    $option['correct'] = '4144959;1;16777215;10;0;100;19;100;92;single;label;0$0$370$173$beetle3.png~80~75|1$0$371$300$earwig3.png~80~92|2$0$5$54$snail3.png~100~47|3$0$537$103$spider|4$0$5$78$ant|5$0$110$78$fruit|6$0$539$472$plants|;';
    $option['markscorrect'] = 1;
    $option['marksincorrect'] = -1;
    $render->set_opt(0, $option);
    $render->marks =  0;
    $render->scoremethod = 'Mark per Question';
    $render->mediaheight = 480;
    $render->mediawidth = 580;
    $useranswerid = '4$4;370$148$beetle3.png$t$371$275$earwig3.png$t$537$78$spider$t$539$447$plants$t$';
    $render->set_option(0, $useranswerid, '', 0);
    $this->assertEquals(1, $render->get('marks'));
    $this->assertFalse($render->get('unanswered'));
    $this->assertEquals(480, $render->get('mediaheight'));
    $this->assertEquals(800, $render->get('mediawidth'));
    $this->assertEquals(1, $render->get('markscorrect'));
    $this->assertEquals(-1, $render->get('marksincorrect'));
    $this->assertEquals($useranswerid, $render->get('useranswer'));
    $this->assertEquals($option['correct'], $render->get('tmpcorrect'));
    $useranswerid = '0$4;';
    $render->marks =  0;
    $render->scoremethod =  'Mark per Option';
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertEquals($useranswerid, $render->get('useranswer'));
    $this->assertTrue($render->get('unanswered'));
    $this->assertEquals(4, $render->get('marks'));
    
  }

}