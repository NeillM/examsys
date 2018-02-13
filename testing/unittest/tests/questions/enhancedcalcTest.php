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

use testing\unittest\unittestdatabase;

/**
 * Test fill in the enhancedcalc question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class enhancedcalctest extends unittestdatabase{
  /**
    * Get init data set from yml
    * @return dataset
    */
   public function getDataSet() {
    return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory()
      . DIRECTORY_SEPARATOR. "questions"
      . DIRECTORY_SEPARATOR . "enchancedcalcTest"
      . DIRECTORY_SEPARATOR . "enhancedcalc.yml");
   }

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $pluginns = 'plugins\questions\enhancedcalc\render';
    $render = new $pluginns();
    $render->set_question_head();
    $this->assertTrue($render->get('displaydefault'));
    $this->assertFalse($render->get('displaynotes'));
    $render->set('notes', 'test');
    $render->set_question_head();
    $this->assertTrue($render->get('displaynotes'));
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $pluginns = 'plugins\questions\enhancedcalc\render';
    $render = new $pluginns();
    $cfg_web_root = $this->config->get('cfg_web_root');
    require_once $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
    $question['object'] = new \EnhancedCalc($this->config);
    $render->set('question', $question);
    $useranswerid = '{"vars":{"$A":2,"$B":8},"uans":""}';
    $render->set_question(1, $useranswerid, '');
    $this->assertEquals($render->get('useranswers'), $question['object']->alluseranswers);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option() {
    ob_start(); // Start output buffering
    $pluginns = 'plugins\questions\enhancedcalc\render';
    $render = new $pluginns();
    $render->set('marks', 0);
    $render->set('questionno', 1);
    $cfg_web_root = $this->config->get('cfg_web_root');
    require_once $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
    $propertyObj = \PaperProperties::get_paper_properties_by_id(1, $this->db, array(), true);
    $questions =  $propertyObj->build_paper(true, 1, 1);
    $questions[1]['object'] = new \EnhancedCalc($this->config);
    $questions[1]['object']->load($questions[1]);
    $render->set('question', $questions[1]);
    $useranswerid = '{"vars":{"$A":2,"$B":8},"uans":""}';
    $render->set_option(1, $useranswerid, '', 1);
    $this->assertEquals(3, $render->get('marks'));
    $output = ob_get_contents(); // Store buffer in variable
    ob_end_clean(); // End buffering and clean up
  }
}