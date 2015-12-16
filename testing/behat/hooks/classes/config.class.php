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

namespace testing\behat\hooks;

use Config as RogoConfig,
    Exception;

/**
 * This class should define all the pre and post hooks for Rogo backend behat tests.
 *
 * This includes:
 * - cleaning up the database
 * - cleaning up the user data directories
 *
 * These hooks assume that all tested database calls during tests will be within the scope of the
 * transactions these hooks set and that the tested code will not use transactions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
trait config {
  /** @var Config A copy of the Rogo configuration object. */
  private static $rogo_config;
  /** @var Config A copy of the Rogo configuration object that is not setup for behat. */
  private static $default_config;

  /**
   * Throws an exception if behat is not configured correctly.
   *
   * @return void
   * @throws Exception
   */
  public static function check_config() {
    $config = RogoConfig::get_instance();
    if (!isset(self::$default_config)) {
      if (!$config->is_behat_configured()) {
        // Behat has not been configured, we should stop!
        throw new Exception('Behat is not configured');
      }
      // Checking the initial config of the site.
      return;
    }
    // Has the behat access url been configured?
    $behatwebsite = $config->get('cfg_behat_website');
    if (empty($behatwebsite)) {
      throw new Exception('Behat website is not configured');
    }
    // Has the behat database been configured, and is it different to the live database?
    $behatdatabase = $config->get('cfg_db_database');
    if (empty($behatdatabase) or $behatdatabase === self::$default_config->get('cfg_db_database')) {
      throw new Exception('Behat database is not configured');
    }
    // Has a behat data directory been configured?
    $behatdatadir = $config->get('cfg_rogo_data');
    if (empty($behatdatadir) or $behatdatadir === self::$default_config->get('cfg_rogo_data')) {
      throw new Exception('Behat user data directory is not configured');
    }
    // We got this far everything is good.
  }
}
