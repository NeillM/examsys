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
 * Test matrix question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class matrixtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('matrix');
    $data->set_question_head();
    $this->assertTrue($data->get('displaydefault'));
    $this->assertFalse($data->get('displaynotes'));
    $this->assertTrue($data->get('displayleadin'));
    $this->assertTrue($data->get('displaymedia'));
    $data->notes = 'test';
    $data->set_question_head();
    $this->assertTrue($data->get('displaynotes'));
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('matrix');
    $data->scenario = "Word|Excel|PowerPoint|Access5|Publisher|Data File||||";
    $useranswerid = '3|4|2|5|1|6';
    $scenarios = array('Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', '');
    $data->set_question(1, $useranswerid, '');
    $this->assertEquals($scenarios, $data->get('scenarios'));
    $this->assertEquals(array('3', '4', '2', '5', '1', '6'), $data->get('usersanswers'));
    $useranswerid = null;
    $data->set_question(0, $useranswerid, '');
    $this->assertEquals(array(), $data->get('usersanswers'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $data = questiondata::get_datastore('matrix');
    $data->matchoptions = array('.PUB');
    $data->set_opt(1, array('optiontext' => '.PUB'));
    $data->set_opt(2, array('optiontext' => '.PPT'));
    $data->set_opt(3, array('optiontext' => '.DOC'));
    $data->set_opt(4, array('optiontext' => '.XLS'));
    $data->set_opt(5, array('optiontext' => '.MDB'));
    $data->set_opt(6, array('optiontext' => '.DAT'));
    $data->set_option(2, '3|4|2|5|1|6', '', 1);
    $this->assertEquals(array('.PUB', '.PPT'), $data->get('matchoptions'));
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $data = questiondata::get_datastore('matrix');
    $data->matchoptions = array('.PUB', '.PPT', '.DOC', '.XLS', '.MDB','.DAT');
    $data->usersanswers = array('3', '4', '2', '5', '1', '6');
    $data->optionorder = '5,2,4,1,0,3';
    $data->scenarios = array('Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', '');
    $useranswerid = '3|4|2|5|1|6';
    $data->set_additional_option(1, $useranswerid, '', 1);
    $matchscenarios = array(
      array('unanswered' => false, 'id' => 'A', 'value' => 'Word'),
      array('unanswered' => false, 'id' => 'B', 'value' => 'Excel'),
      array('unanswered' => false, 'id' => 'C', 'value' => 'PowerPoint'),
      array('unanswered' => false, 'id' => 'D', 'value' => 'Access5'),
      array('unanswered' => false, 'id' => 'E', 'value' => 'Publisher'),
      array('unanswered' => false, 'id' => 'F', 'value' => 'Data File'),
    );
    $matchoptions = array(
      array('option' => '.PUB', 'value' => 6, 'selected' => array(1 => false,
          2 => false,
          3 => false,
          4 => false,
          5 => false,
          6 => true)),
      array('option' => '.PPT', 'value' => 3, 'selected' => array(1 => true,
          2 => false,
          3 => false,
          4 => false,
          5 => false,
          6 => false)),
      array('option' => '.DOC', 'value' => 5, 'selected' => array(1 => false,
          2 => false,
          3 => false,
          4 => true,
          5 => false,
          6 => false)),
      array('option' => '.XLS', 'value' => 2, 'selected' => array(1 => false,
          2 => false,
          3 => true,
          4 => false,
          5 => false,
          6 => false)),
      array('option' => '.MDB', 'value' => 1, 'selected' => array(1 => false,
          2 => false,
          3 => false,
          4 => false,
          5 => true,
          6 => false)),
      array('option' => '.DAT', 'value' => 4, 'selected' => array(1 => false,
          2 => true,
          3 => false,
          4 => false,
          5 => false,
          6 => false))
    );
    $this->assertEquals($matchoptions, $data->get('matchoptions'));
    $this->assertEquals($matchscenarios, $data->get('matchscenarios'));
  }
}