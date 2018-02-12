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
 * Test fill in the blank question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class blanktest extends unittest{

  /**
    * Test area question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\blank\render';
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
    * Test area question option setter - textbox responses
    * @group question
    */
  public function test_set_option_textbox() {
    $pluginns = 'plugins\questions\blank\render';
    $render = new $pluginns();
    $option['optiontext'] = '<div>London is the capital of [blank]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $render->set_opt(0, $option);
    $render->set('displaymethod', 'textboxes');
    $render->set('scoremethod', 'Mark per Option');
    $render->set('marks', 0);
    $useranswerid = '["Wales","u"]';
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertTrue($render->get('unanswered'));
    $blankoptions[1] = array('itemtype' => 'blurb', 'itemvalue' => '<div>London is the capital of ');
    $blankoptions[2] = array('itemtype' => 'blank', 'itemcount' => 1, 'size' => 15, 'unans' => false, 'encoded_ans' => 'Wales');
    $blankoptions[3] = array('itemtype' => 'blurb', 'itemvalue' => ' and is in the ');
    $blankoptions[4] = array('itemtype' => 'blank', 'itemcount' => 2, 'size' => 15, 'unans' => true);
    $blankoptions[5] = array('itemtype' => 'blurb', 'itemvalue' => '</div>');
    $this->assertEquals($blankoptions, $render->get('blankoptions'));
    $this->assertEquals(2, $render->get('marks'));
    $render->set('scoremethod', 'Mark per Question');
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertEquals(1, $render->get('marks'));
  }

  /**
    * Test area question option setter - dropdown responses
    * @group question
    */
  public function test_set_option_dropdown() {
    $pluginns = 'plugins\questions\blank\render';
    $render = new $pluginns();
    $option['optiontext'] = '<div>London is the capital of [blank]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $render->set_opt(0, $option);
    $render->set('displaymethod', 'dropdown');
    $render->set('scoremethod', 'Mark per Option');
    $render->set('marks', 0);
    $useranswerid = '["Wales","u"]';
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertTrue($render->get('unanswered'));
    $blankoptions[1] = array('itemtype' => 'blurb', 'itemvalue' => '<div>London is the capital of ');
    $blankoptions[2] = array('itemtype' => 'blank', 'itemcount' => 1, 'unans' => false, 'itemvalue' => array (
        0 => array('answer' => 'England', 'selected' => false),
        1 => array('answer' => 'Northern Ireland', 'selected' => false),
        2 => array('answer' => 'Scotland', 'selected' => false),
        3 => array('answer' => 'Wales', 'selected' => true)));
    $blankoptions[3] = array('itemtype' => 'blurb', 'itemvalue' => ' and is in the ');
    $blankoptions[4] = array('itemtype' => 'blank', 'itemcount' => 2, 'unans' => true, 'itemvalue' => array (
         0 => array('answer' => 'United Kingdom', 'selected' => false),
         1 => array('answer' => 'United States of America', 'selected' => false)));
    $blankoptions[5] = array('itemtype' => 'blurb', 'itemvalue' => '</div>');
    // Need to split test as itemvalue randomly shuffled so need to sort before test.
    $options = $render->get('blankoptions');
    $this->assertEquals($blankoptions[1], $options[1]);
    sort($options[2]['itemvalue']);
    $this->assertEquals($blankoptions[2], $options[2]);
    $this->assertEquals($blankoptions[3], $options[3]);
    sort($options[4]['itemvalue']);
    $this->assertEquals($blankoptions[4], $options[4]);
    $this->assertEquals($blankoptions[5], $options[5]);
    $this->assertEquals(2, $render->get('marks'));
    $render->set('scoremethod', 'Mark per Question');
    $render->set_option(0, $useranswerid, '', 1);
    $this->assertEquals(1, $render->get('marks'));
  }

}