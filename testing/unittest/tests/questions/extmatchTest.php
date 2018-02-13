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
 * Test fill in the extmatch question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class extmatchtest extends unittest{

  /**
    * Test area question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\extmatch\render';
    $render = new $pluginns();
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $this->assertTrue($render->get('displayleadin'));
    $render->set('notes', 'test');
    $render->set_question_head();
    $this->assertTrue($render->get('displaynotes'));
  }

  /**
    * Test area question question setter
    * @group question
    */
  public function test_set_question() {
    $pluginns = 'plugins\questions\extmatch\render';
    $render = new $pluginns();
    $render->set('scenario', "<div>It is now known as;</div>|<div>It's football team is called</div>|<div>It's most famous landmark is the</div>|||||||");
    $render->set('qmedia', "1516973089.jpg|1516973089.png|1516975621.jpg||||||||");
    $render->set('qmediawidth', "1480|480|951||||||||");
    $render->set('qmediaheight', "776|105|121||||||||");
    $scenarios = array("<div>It is now known as;</div>",
        "<div>It's football team is called</div>",
        "<div>It's most famous landmark is the</div>", "", "", "", "", "", "", "");
    $media = array("1516973089.jpg",
        "1516973089.png",
        "1516975621.jpg", "", "", "", "", "", "", "", "");
    $width = array(1480, 480, 951, "", "", "", "", "", "", "", "");
    $height= array(776, 105, 121, "", "", "", "", "", "", "", "");
    $useranswerid = '1|3|2';
    $render->set_question(1, $useranswerid, '');
    $this->assertEquals($scenarios, $render->get('scenarios'));
    $this->assertEquals($media, $render->get('media'));
    $this->assertEquals($width, $render->get('mediawidth'));
    $this->assertEquals($height, $render->get('mediaheight'));
    $this->assertEquals(array('1', '3', '2'), $render->get('usersanswers'));
    $useranswerid = null;
    $render->set_question(0, $useranswerid, '');
    $this->assertEquals(array(), $render->get('usersanswers'));
  }

  /**
    * Test area question option setter
    * @group question
    */
  public function test_set_option() {
    $pluginns = 'plugins\questions\extmatch\render';
    $render = new $pluginns();
    $render->set('matchoptions', array('Paris'));
    $render->set_opt(1, array('optiontext' => 'Paris'));
    $render->set_opt(2, array('optiontext' => 'Eifel Tower'));
    $render->set_opt(3, array('optiontext' => 'Paris Saint-Germain F.C.'));
    $render->set_opt(4, array('optiontext' => 'Derby'));
    $render->set_opt(5, array('optiontext' => 'Intu Centre'));
    $render->set_opt(6, array('optiontext' => 'Derby County F.C.'));
    $render->set_option(2, '1|3|2', '', 1);
    $this->assertEquals(array('Paris', 'Eifel Tower'), $render->get('matchoptions'));
  }

  /**
    * Test area question additional option setter
    * @group question
    */
  public function test_set_additional_option() {
    $pluginns = 'plugins\questions\extmatch\render';
    $render = new $pluginns();
    $render->set_opt(1, array('optiontext' => 'Paris', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $render->set_opt(2, array('optiontext' => 'Eifel Tower', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $render->set_opt(3, array('optiontext' => 'Paris Saint-Germain F.C.', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $render->set_opt(4, array('optiontext' => 'Derby', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $render->set_opt(5, array('optiontext' => 'Intu Centre', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $render->set_opt(6, array('optiontext' => 'Derby County F.C.', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $render->set('matchoptions', array('Paris', 'Eifel Tower', 'Paris Saint-Germain F.C.', 'Derby', 'Intu Centre', 'Derby County F.C.'));
    $render->set('optionorder', "0,1,2,3,4,5");
    $render->set('scenarios', array("<div>It is now known as;</div>",
        "<div>It's football team is called</div>",
        "<div>It's most famous landmark is the</div>", "", "", "", "", "", "", ""));
    $render->set('media', array("1516973089.jpg",
        "1516973089.png",
        "1516975621.jpg", "", "", "", "", "", "", "", ""));
    $render->set('mediawidth', array(1480, 480, 951, "", "", "", "", "", "", "", ""));
    $render->set('mediaheight', array(776, 105, 121, "", "", "", "", "", "", "", ""));
    $render->set('usersanswers', array('1', '3', '2'));
    $marks = $render->set('marks', 0);
    $cfg_root_path = $this->config->get('cfg_root_path');
    $matchstem = array(
      array('answerno' => 2,
          'scenario' => "<div>It is now known as;</div>",
          'display' => true,
          'media' => array('mediaid' => -1,
            'mediafile' => '1516973089.png',
            'mediawidth' => 480,
            'mediaheight' => 105,
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=1516973089.png',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 2,
            'mediaborder' => false,
            'mediabordercolour' => ''),
          'unanswered' => false,
          'listsize' => 6,
          'subanswers' => 2,
          'matchingoptions' => 6,
          'matchingoption' => array(
            array (
              'selected' => true,
              'value' => 1,
              'option' => 'A. Paris'
            ),
            array (
              'selected' => false,
              'value' => 2,
              'option' => 'B. Eifel Tower'
            ),
            array (
              'selected' => false,
              'value' => 3,
              'option' => 'C. Paris Saint-Germain F.C.'
            ),
            array (
              'selected' => false,
              'value' => 4,
              'option' => 'D. Derby'
            ),
            array (
              'selected' => false,
              'value' => 5,
              'option' => 'E. Intu Centre'
            ),
            array (
              'selected' => false,
              'value' => 6,
              'option' => 'F. Derby County F.C.'
            ),
          )
      ),
      array('answerno' => 1,
          'scenario' => "<div>It's football team is called</div>",
          'display' => true,
          'media' => array('mediaid' => -1,
            'mediafile' => '1516975621.jpg',
            'mediawidth' => 951,
            'mediaheight' => 121,
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=1516975621.jpg',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 2,
            'mediaborder' => false,
            'mediabordercolour' => ''),
          'unanswered' => false,
          'matchingoption' => array(
            array (
              'selected' => false,
              'value' => 1,
              'option' => 'A. Paris'
            ),
            array (
              'selected' => false,
              'value' => 2,
              'option' => 'B. Eifel Tower'
            ),
            array (
              'selected' => true,
              'value' => 3,
              'option' => 'C. Paris Saint-Germain F.C.'
            ),
            array (
              'selected' => false,
              'value' => 4,
              'option' => 'D. Derby'
            ),
            array (
              'selected' => false,
              'value' => 5,
              'option' => 'E. Intu Centre'
            ),
            array (
              'selected' => false,
              'value' => 6,
              'option' => 'F. Derby County F.C.'
            ),
          )
      ),
      array('answerno' => 1,
        'scenario' => "<div>It's most famous landmark is the</div>",
        'display' => false,
        'unanswered' => false,
        'matchingoption' => array(
          array (
            'selected' => false,
            'value' => 1,
            'option' => 'A. Paris'
          ),
          array (
            'selected' => true,
            'value' => 2,
            'option' => 'B. Eifel Tower'
          ),
          array (
            'selected' => false,
            'value' => 3,
            'option' => 'C. Paris Saint-Germain F.C.'
          ),
          array (
            'selected' => false,
            'value' => 4,
            'option' => 'D. Derby'
          ),
          array (
            'selected' => false,
            'value' => 5,
            'option' => 'E. Intu Centre'
          ),
          array (
            'selected' => false,
            'value' => 6,
            'option' => 'F. Derby County F.C.'
          ),
        )
      )
    );
    $render->set_additional_option(6, '1|3|2', '', 1);
    $this->assertTrue($render->get('extmatchdisplaymedia'));
    $this->assertEquals(2, $render->get('split'));
    $this->assertEquals(6, $render->get('matchoptionsno'));
    $this->assertEquals(4, $render->get('marks'));
    $this->assertFalse($render->get('unanswered'));
    $this->assertEquals($matchstem, $render->get('matchstem'));
  }
}