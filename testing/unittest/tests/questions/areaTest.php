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
    $data = questiondata::get_datastore('area');
    $data->set_question_head();
    $this->assertTrue($data->get('displaydefault'));
    $this->assertFalse($data->get('displaynotes'));
    $this->assertFalse($data->get('displayscenario'));
    $this->assertTrue($data->get('displayleadin'));
    $data->notes = 'test';
    $data->scenario = 'test';
    $data->set_question_head();
    $this->assertTrue($data->get('displaynotes'));
    $this->assertTrue($data->get('displayscenario'));
  }
 
  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $data = questiondata::get_datastore('area');
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->marks = 1;
    $data->mediawidth =  10;
    $data->mediaheight = 10;
    $useranswerid = '100,0,0,0,0,7397;d5,69,df,64,d5,69, ';
    $data->set_option(0, $useranswerid, '', 0);
    $this->assertFalse($data->get('unanswered'));
    $this->assertEquals(12, $data->get('mediawidth'));
    $this->assertEquals(37, $data->get('mediaheight'));
    $this->assertEquals(1, $data->get('areadisplay'));
    $this->assertEquals('d5,69,df,64,d5,69', $data->get('areauseranswer'));
    $this->assertEquals($useranswerid, $data->get('areafulluseranswer'));
    $this->assertEquals(2, $data->get('marks'));
  }

}