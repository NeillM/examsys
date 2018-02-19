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
 * Test fill in the flash question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class flashtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $render = questionrender::get_render('flash');
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
    $render = questionrender::get_render('flash');
    $option['markscorrect'] = 1;
    $render->set_opt(0, $option);
    $render->marks = 1;
    $render->set_option(0, '', '', 0);
    $this->assertEquals(2, $render->get('marks'));
  }

}