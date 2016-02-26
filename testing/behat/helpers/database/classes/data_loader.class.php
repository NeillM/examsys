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

namespace testing\behat\helpers\database;

/**
 * Base class used to load data into the Rogo database for behat tests.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
abstract class Data_Loader {
  /** @var PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection Database connector used by the PUP Unit database extension. */
  protected $phpunit_db;

  /** @var string The location of the base fixtures directory. */
  protected $fixture_base = __DIR__ . '/../../../fixtures/';

  /**
   * Required by PHPUnit_Extensions_Database_TestCase_Trait
   */
  protected function setUp() {
    // Intentionally blank.
  }
}
