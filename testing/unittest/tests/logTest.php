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
 * Test assessment class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class logtest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
      return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "logTest" . DIRECTORY_SEPARATOR . "log.yml");
  }

  /**
   * Test retrieving previous answers - with restart
   * @group log
   */
  public function test_get_previous_answers() {
    $papertype = '0';
    $log = \log::get_paperlog($papertype);
    $metadataID = 2;
    $do_restart = true;
    $current_screen = 1;
    $previous = array('used_questions' => array(2 => 2),
        'user_answers' => array(2 => array(2 => '1')),
        'user_dismiss' => array(2 => array(2 => '0000')),
        'user_order' => array(2 => array(2 => '0,1,2,3')),
        'previous_duration' => 5,
        'screen_pre_submitted' => 1,
        'current_screen' => 2);
    $this->assertEquals($previous, $log->get_previous_answers($metadataID, $do_restart, $current_screen));
  }

  /**
   * Test retrieving previous answers from log late
   * @group log
   */
  public function test_get_previous_answers_late() {
    $papertype = '2';
    $log = \log::get_paperlog($papertype);
    $metadataID = 1;
    $do_restart = false;
    $current_screen = 1;
    $previous = array('used_questions' => array(2 => 2),
        'user_answers' => array(1 => array(2 => '1', 2 => '2')),
        'user_dismiss' => array(1 => array(2 => '0000', 2 => '1000')),
        'user_order' => array(1 => array(2 => '0,1,2,3')),
        'previous_duration' => 10,
        'screen_pre_submitted' => 1,
        'current_screen' => 1);
    $this->assertEquals($previous, $log->get_previous_answers($metadataID, $do_restart, $current_screen, true));
  }
}
