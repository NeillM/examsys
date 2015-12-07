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

use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\FeatureEvent;
use testing\behat\rogo_test,
    testing\behat\environment,
    testing\behat\help,
    testing\behat\selectors;
use testing\datagenerator\loader;

/**
 * This class should define all the pre and post hooks for Rogo behat tests.
 *
 * This includes:
 * - cleaning up the database
 * - cleaning up the user data directories
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class core_hooks extends rogo_test {
  /** @var mysqli A Rogo database connection. */
  private static $db;
  /** @var Config A copy of the Rogo configuration object. */
  private static $rogo_config;
  /** @var Config A copy of the Rogo configuration object that is not setup for behat. */
  private static $default_config;
  /** @var array Stores an array of tables per named transaction that can be used to detect changes if a transaction fails. */
  private static $tablestates = array();
  /** @var array Stores a list of temporary tables we have created to backup Rogo data in. */
  private static $temptables = array();
  /** @var string Stores the database schema we connect to. */
  private static $schema;
  /** Stores if the setup function for the first scenario to be run has been completed. */
  private static $firstscenariosetup = false;

  /** The name of the transaction used for a scenario. */
  const TRANSACTION_SCENARIO = 'behatscenario';
  /** The name of the transaction used for the suite. */
  const TRANSACTION_SUITE = 'behatsuite';
  /** The name of the transaction used for the feature. */
  const TRANSACTION_FEATURE = 'behatfeature';
  
  /**
   * Actions to perform before the suite is run.
   *
   * @BeforeSuite
   */
  public static function setup(SuiteEvent $event) {
    self::check_config();
    // Setup the config for behat and store a cloned instance of it.
    $config = Config::get_instance();
    self::$default_config = clone($config);
    $config->use_behat_site();
    self::$rogo_config = clone($config);

    // Create a database connection.
    $host = $config->get('cfg_db_host');
    $username = $config->get('cfg_behat_db_user');
    $password = $config->get('cfg_behat_db_password');
    $port = $config->get('cfg_db_port');
    self::$schema = $config->get('cfg_db_database');
    self::$db = new mysqli($host, $username, $password, self::$schema, $port);
    // Let the data generators have the database connection.
    loader::set_database(self::$db);

    // Ensure the directories are empty.
    self::reset_directories();

    // Test that the website is running.
    if (!environment::is_server_running()) {
      $message = environment::get_behat_website() . ' is not available. '
          . 'Please ensure that the correct url is configured and the server is running.'
          . PHP_EOL . 'See ' . help::DOCUMENTATION . ' for mor information.';
      throw new Exception($message);
    }

    self::save_database_state(self::TRANSACTION_SUITE);
  }

  /**
   * Actions to perform before every Feature.
   *
   * @BeforeFeature
   */
  public static function setup_feature(FeatureEvent $event) {
    self::check_config();
    self::save_database_state(self::TRANSACTION_FEATURE);
  }

  /**
   * Actions to perform before every scenario.
   *
   * @BeforeScenario
   */
  public function setup_scenario($event) {
    self::check_config();
    self::save_database_state(self::TRANSACTION_SCENARIO);

    $session = $this->getSession();

    if (self::is_first_scenario()) {
      selectors::register_rogo_selectors($session);
    }

    // Reset the session.
    $session->reset();

    if (self::is_first_scenario()) {
      // This should be the last thing done in this method.
      self::$firstscenariosetup = true;
    }
  }

  /**
   * Cleanup up Rogo after a scenario has been run.
   *
   * @AfterScenario
   */
  public function teardown_scenario($event) {
    // Reset the config object.
    Config::set_mock_instance(clone(self::$rogo_config));
    // Rollback any database changes.
    self::rollback_database_state(self::TRANSACTION_SCENARIO);
    // Ensure the directories are empty.
    self::reset_directories();
  }

  /**
   * Clean up Rogo after a feature file has been run.
   *
   * @AfterFeature
   */
  public static function teardown_feature(FeatureEvent $event) {
    // Reset the config object.
    Config::set_mock_instance(clone(self::$rogo_config));
    // Rollback any database changes.
    self::rollback_database_state(self::TRANSACTION_FEATURE);
    // Ensure the directories are empty.
    self::reset_directories();
  }
  
  /**
   * Clean up Rogo after the suite has finished running.
   *
   * @AfterSuite
   */
  public static function teardown(SuiteEvent $event) {
    // Reset the config object.
    Config::set_mock_instance(clone(self::$rogo_config));
    // Rollback any database changes.
    self::rollback_database_state(self::TRANSACTION_SUITE);
    // Ensure the directories are empty.
    self::reset_directories();
    // Close the database connection.
    self::$db->close();
  }

  /**
   * Returns whether the first scenario of the suite is running
   * @return bool
   */
  protected static function is_first_scenario() {
    return !(self::$firstscenariosetup);
  }

  /**
   * Clear the contents of the Rogo directories.
   */
  public static function reset_directories() {
    $mediadirectory = rogo_directory::get_directory('media');
    $mediadirectory->clear();
    $qtiimportdirectory = rogo_directory::get_directory('qti_import');
    $qtiimportdirectory->clear();
    $qtiexportdirectory = rogo_directory::get_directory('qti_export');
    $qtiexportdirectory->clear();
    $emailtemplatesdirectory = rogo_directory::get_directory('email_templates');
    $emailtemplatesdirectory->clear();
    $photodirectory = rogo_directory::get_directory('user_photo');
    $photodirectory->clear();
  }

  /**
   * Throws an exception if behat is not configured correctly.
   *
   * @return void
   * @throws Exception
   */
  public static function check_config() {
    $config = Config::get_instance();
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

  /**
   * Save the state of the Rogo database so we can rollback any changes.
   *
   * @param string $name
   * @throws Exception
   */
  private static function save_database_state($name) {
    // Check the transaction has not been started already. We would not want to overwrite the save state.
    if (isset(self::$tablestates[$name])) {
      throw new Exception("A state called $name has already been saved.");
    }
    // Get and store the state of all the tables in the database.
    // We can use this for comparisons to ensure that nothing has been changed later.
    self::$tablestates[$name] = self::get_table_statuses();
    foreach (self::$tablestates[$name] as $status) {
      self::save_table_state($name, $status);
    }
    // We cannot use database transactions because Rogo will be accessed by a browser during the tests.
  }

  /**
   * Backs up the data in a table so we can roll back any changes on it.
   *
   * @param string $statename the name of the state that we are saving the table for.
   * @param array $status a set of results from the self::get_table_statuses() function
   * @return void
   */
  private static function save_table_state($statename, array $status) {
    if ($status['rows'] == 0) {
      // The table has no data we need to save.
      return;
    }
    $temptablename = $statename . '_' . $status['name'];
    $originaltable = $status['name'];
    $sql = "CREATE TEMPORARY TABLE $temptablename AS SELECT * FROM $originaltable";
    if (!self::$db->query($sql)) {
      throw new Exception("Could not backup $originaltable in $statename state.");
    }
    self::$temptables[$statename][$originaltable] = $temptablename;
  }

  /**
   * Both parameters must be a row from an array generated by self::get_table_statuses()
   * The function will attempt to undo any changes made to the database between the
   * original state and the new state.
   *
   * @param string $statename the name of the state that we are saving the table for.
   * @param array $originalstate
   * @param array $newstate
   * @return void
   */
  private static function reset_table($statename, array $originalstate, array $newstate) {
    if (empty($originalstate['name']) or empty($newstate['name']) or $originalstate['name'] !== $newstate['name']) {
      // The states are not for the same table... something is really borked!
      throw new Exception('The states passed are not for the same table');
    }
    if ($originalstate['created'] !== $newstate['created']) {
      // The table was deleted and re-created!
    }
    
    if ($originalstate['rows'] == 0 and $newstate['rows'] == 0) {
      self::reset_autoincrement($originalstate['name'], $originalstate['auto_increment'], $newstate['auto_increment']);
      // The table should be reset now.
      return;
    }

    $cleandatasql = "TRUNCATE " . $originalstate['name'];
    self::$db->query($cleandatasql);

    if ($originalstate['rows'] == 0) {
      // The original table had no data, so the reset is done now.
      return;
    }
    // Put the stored data back into the the table.
    $repopulatesql = "INSERT INTO " . $originalstate['name'] .
        " SELECT * FROM " . self::$temptables[$statename][$originalstate['name']];
    self::$db->query($repopulatesql);
    self::reset_autoincrement($originalstate['name'], $originalstate['auto_increment'], $newstate['auto_increment']);
    // Drop the temporay table.
    $dropsql = "DROP TEMPORARY TABLE " . self::$temptables[$statename][$originalstate['name']];
    self::$db->query($dropsql);
    unset(self::$temptables[$statename][$originalstate['name']]);
  }

  /**
   *
   * @param string $table The name of a mysql
   * @param int $originalincrement
   * @param int $currentincrement
   */
  private static function reset_autoincrement($table, $originalincrement, $currentincrement) {
    if ($originalincrement != $currentincrement) {
      // Change the auto increment value, reset it.
      $incrementsql = "ALTER TABLE " . $table . " AUTO_INCREMENT = $originalincrement";
      $incrementquery = self::$db->prepare($incrementsql);
      $incrementquery->execute();
      $incrementquery->close();
    }
  }

  /**
   * Undo any database changes made since the state was saved.
   *
   * @param string $name
   * @throws Exception
   */
  private static function rollback_database_state($name) {
    if (!isset(self::$tablestates[$name])) {
      throw new Exception("State $name has not been saved.");
    }
    $currentstate = self::get_table_statuses();
    $deletedtables = 0;
    foreach (self::$tablestates[$name] as $table => $status) {
      if (!isset($currentstate[$table])) {
        // The table has been deleted.
        $deletedtables++;
        continue;
      }
      self::reset_table($name, $status, $currentstate[$table]);
    }

    // Check the sizes of both arrays match.
    if (count($currentstate) !== (count(self::$tablestates[$name]) - $deletedtables)) {
      // Tables got added, we should find and delete them.
      throw new Exception("Tables mismatch from $name");
    }
    if (!empty(self::$temptables[$name])) {
      // A table was not reset properly.
      throw new Exception("Tables not reset correctly in $name");
    }
    if (!empty($deletedtables)) {
      throw new Exception('Tables have been deleted from Rogo. You must reinitialise the Rogo database.');
    }
    unset(self::$tablestates[$name]);
  }
  
  

  /**
   * Gets the full table status information for all the tables in Rogo and returns them as an associative array.
   *
   * @return array
   */
  private static function get_table_statuses() {
    $sql = "SELECT TABLE_NAME, TABLE_ROWS, AUTO_INCREMENT, CREATE_TIME "
        . "FROM information_schema.tables WHERE TABLE_SCHEMA = ?  AND TABLE_TYPE = 'BASE TABLE'";
    $query = self::$db->prepare($sql);
    $query->bind_param('s', self::$schema);
    $query->execute();
    $query->bind_result($name, $rows, $increment, $created);
    $return = array();
    while ($query->fetch()) {
      $return[$name] = array(
        'name' => $name,
        'rows' => $rows,
        'auto_increment' => $increment,
        'created' => $created,
      );
    }
    $query->close();
    return $return;
  }
}
