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
 * Test SCT question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class scttest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('sct');
    $lang = new \langpack();
    $strings = $lang->get_all_strings($data->langcomponent);
    $data->leadin = "hyp~info";
    $data->displaymethod = 1;
    $data->set_question(1, 0, '');
    $this->assertTrue($data->get('displayscenario'));
    $this->assertFalse($data->get('displaynotes'));
    $this->assertFalse($data->get('displaymedia'));
    $this->assertEquals('hyp', $data->scthyp);
    $this->assertEquals('info', $data->sctinfo);
    $this->assertEquals($strings['hypothesis'], $data->scttitle);
    $lowertitle = $strings['thenthis'] . " " . mb_strtolower($strings['hypothesis'], 'UTF-8') . " " . $strings['is'] . ":";
    $this->assertEquals($lowertitle, $data->scttitlelower);
    $this->assertTrue($data->get('unanswered'));
    $data->notes = 'test';
    $data->qmedia = 'test';
    $data->set_question(1, 1, '');
    $this->assertFalse($data->get('unanswered'));
    $this->assertTrue($data->get('displaynotes'));
    $this->assertTrue($data->get('displaymedia'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $data = questiondata::get_datastore('sct');
    $option['optiontext'] = '';
    $option['tmppartid'] = 1;
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $data->set_opt(1, $option);
    $useranswerid = 3;
    $data->set_option(1, $useranswerid, '10000', 1);
    $option = $data->get_opt(1);
    $this->assertFalse($option['selected']);
    $this->assertTrue($option['inact']);
    $this->assertFalse($option['optiontextdisplay']);
    $option['optiontext'] = 'meh';
    $option['tmppartid'] = 3;
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $data->set_opt(2, $option);
    $useranswerid = 3;
    $data->set_option(2, $useranswerid, '10000', 1);
    $option = $data->get_opt(2);
    $this->assertTrue($option['selected']);
    $this->assertFalse($option['inact']);
    $this->assertTrue($option['optiontextdisplay']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('sct');
    // Test dismiss and negative marking.
    $useranswerid = 5;
    $data->optionnumber = 5;
    $option['marksincorrect'] = -1;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswerid, '01000', 1);
    $this->assertEquals('01000', $data->get('dismiss'));
    $this->assertTrue($data->get('negativemarking'));
    $option['marksincorrect'] = 0;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswerid, '', 1);
    $this->assertEquals('00000', $data->get('dismiss'));
    $this->assertFalse($data->get('negativemarking'));
  }
}