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
    $data = questiondata::get_datastore('mrq');
    $data->set_question_head();
    $this->assertTrue($data->get('displaydefault'));
    $this->assertFalse($data->get('displaynotes'));
    $this->assertFalse($data->get('displayscenario'));
    $this->assertTrue($data->get('displayleadin'));
    $this->assertFalse($data->get('displaymedia'));
    $data->notes = 'test';
    $data->scenario = 'test';
    $data->qmedia = 'test';
    $data->set_question_head();
    $this->assertTrue($data->get('displaynotes'));
    $this->assertTrue($data->get('displayscenario'));
    $this->assertTrue($data->get('displaymedia'));
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('mrq');
    $useranswerid = 'nnnnn';
    $data->set_question(1, $useranswerid, '', 2);
    $this->assertTrue($data->get('unanswered'));
    $this->assertEquals(2, $data->get('allowedresponses'));
    $useranswerid = 'nnnyn';
    $data->set_question(1, $useranswerid, '', 2);
    $this->assertFalse($data->get('unanswered'));
    $useranswerid = 'nynyn';
    $data->set_question(1, $useranswerid, '', 2);
    $this->assertFalse($data->get('unanswered'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $data = questiondata::get_datastore('mrq');
    $option['tmppartid'] = 1;
    $option['optiontext'] = '';
    $option['omedia'] = '';
    $option['correct'] = 'n';
    $option['markscorrect'] = 1;
    $data->set_opt(1, $option);
    $data->marks = 0;
    $data->scoremethod = 'Mark per Option';
    $useranswerid = 'nnny';
    $data->set_option(1, $useranswerid, '1000', 1);
    $option = $data->get_opt(1);
    $this->assertFalse($option['selected']);
    $this->assertTrue($option['inact']);
    $this->assertFalse($option['optiontextdisplay']);
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($data->get('negativemarking'));
    $this->assertFalse($data->get('abstainselected'));
    $this->assertEquals(0, $data->get('marks'));
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('mrq');
    // Test other.
    $useranswerid = 'nnnyn';
    $data->optionnumber = 5;
    $option['marksincorrect'] = 0;
    $option['tmppartid'] = 4;
    $option['correct'] = 'y';
    $data->set_opt(4, $option);
    $data->displaymethod = 'other';
    $data->partid = 4;
    $useranswerid = 'nnnnytest';
    $data->process_options(4, $useranswerid, '00000', 1);
    $this->assertEquals(5, $data->get('partid'));
    $this->assertTrue($data->get('otherselected'));
    $this->assertEquals('test', $data->get('other'));
    // Test dismiss.
    $option['marksincorrect'] = -1;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswerid, '01000', 1);
    $this->assertEquals('01000', $data->get('dismiss'));
    $data->process_options(1, $useranswerid, '', 1);
    $this->assertEquals('00000', $data->get('dismiss'));
    // Test abstain.
    $useranswerid = 'a';
    $option['markscorrect'] = -1;
    $data->process_options(1, $useranswerid, '1000', 1);
    $option = $data->get_opt(1);
    $this->assertTrue($data->get('negativemarking'));
    $this->assertTrue($data->get('abstainselected'));
  }
}