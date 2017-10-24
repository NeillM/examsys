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
 * Testcase for class Url.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 * @group page
 */
class pageTest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . "page.yml");
  }

  /**
   * Test title generation
   * @group page
   */
  public function test_title() {
    $this->userobject->load(1);
    $this->assertEquals('title unittest', \page::title('title'));
  }

  /**
   * Test title generation in demo mode
   * @group page
   */
  public function test_title_demo() {
    $this->userobject->load(1);
    $this->userobject->set_demo();
    $this->assertEquals('title unittest (DEMO mode)', \page::title('title'));
  }

  /**
   * Test title generation in impersonation mode
   * @group page
   */
  public function test_title_impersonate() {
    $this->userobject->load(1);
    $this->userobject->impersonate(2);
    $this->assertEquals('title unittest as Mr Tester', \page::title('title'));
  }
}