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
    $pluginns = 'plugins\questions\matrix\render';
    $render = new $pluginns();
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertTrue($render->get('displayleadin'));
    $this->assertTrue($render->get('displaymedia'));
    $render->set('notes', 'test');
    $render->set_question_head();
    $this->assertTrue($render->get('displaynotes'));
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $pluginns = 'plugins\questions\matrix\render';
    $render = new $pluginns();
    $render->set('scenario', "Word|Excel|PowerPoint|Access5|Publisher|Data File||||");
    $useranswerid = '3|4|2|5|1|6';
    $scenarios = array('Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', '');
    $render->set_question(1, $useranswerid, '');
    $this->assertEquals($scenarios, $render->get('scenarios'));
    $this->assertEquals(array('3', '4', '2', '5', '1', '6'), $render->get('usersanswers'));
    $useranswerid = null;
    $render->set_question(0, $useranswerid, '');
    $this->assertEquals(array(), $render->get('usersanswers'));
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    $pluginns = 'plugins\questions\matrix\render';
    $render = new $pluginns();
    $render->set('matchoptions', array('.PUB'));
    $render->set_opt(1, array('optiontext' => '.PUB'));
    $render->set_opt(2, array('optiontext' => '.PPT'));
    $render->set_opt(3, array('optiontext' => '.DOC'));
    $render->set_opt(4, array('optiontext' => '.XLS'));
    $render->set_opt(5, array('optiontext' => '.MDB'));
    $render->set_opt(6, array('optiontext' => '.DAT'));
    $render->set_option(2, '3|4|2|5|1|6', '', 1);
    $this->assertEquals(array('.PUB', '.PPT'), $render->get('matchoptions'));
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $pluginns = 'plugins\questions\matrix\render';
    $render = new $pluginns();
    $render->set('matchoptions', array('.PUB', '.PPT', '.DOC', '.XLS', '.MDB','.DAT'));
    $render->set('usersanswers', array('3', '4', '2', '5', '1', '6'));
    $render->set('optionorder', '5,2,4,1,0,3');
    $render->set('scenarios', array('Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', ''));
    $useranswerid = '3|4|2|5|1|6';
    $render->set_additional_option(1, $useranswerid, '', 1);
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
    $this->assertEquals($matchoptions, $render->get('matchoptions'));
    $this->assertEquals($matchscenarios, $render->get('matchscenarios'));
  }
}