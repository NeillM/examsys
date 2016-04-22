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

/**
 *
 * config file
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 *
 * Designed to hold the config options in a class for easier access.
 */
class Config extends RogoStaticSingleton {
  /**
   * @var array
   */
  public $data;
  /** @var array Array of component settings */
  public $settings;
  public $xmldata;
  protected static $inst;
  protected static $class_name = 'Config';
  /** @var mysqli The mysqli database object */
  public $db;
  
  /** @var bool Stores if the config object has been setup for behat. */
  protected $behatsetup = false;
  
  /** @var bool Stores if the config object has been setup for phpunit. */
  protected $phpunitsetup = false;

  /** The path to the behat config file relative to the root Rogo directory. */
  const BEHAT_CONFIG_FILE = '/config/behat.xml';
  
  /** The path to the phpunit config file relative to the root Rogo directory. */
  const PHP_UNIT_CONFIG_FILE = '/config/phpunit.xml';

  function __toString() {
    return "ConfigObject!";
  }

  protected function __construct() {

    // Get out of the box config information.
    $file = __DIR__ . '/../config/rogo.xml';
    $this->xmldata = json_encode(simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA));
    // Get installed system config information.
    $conf_file = __DIR__ . '/../config/config.inc.php';
    if (file_exists($conf_file)) {
      include $conf_file;
    }
    $this->data = get_defined_vars();

    $this->load_behat_config();

    if ($this->is_behat_configured() && $this->is_behat_site()) {
      $this->use_behat_site();
    } else {
        $this->load_phpunit_config();
        if ($this->is_phpunit_configured() && $this->is_phpunit_site()) {
          $this->use_phpunit_site();
        } elseif ($this->is_phpunit_site()) {
           // Stop if phpunit is not configured correctly.
           echo 'Phpunit not configured correctly.';
           exit();
        }
    }
  }

  /**
   * Loads the behat configuration for Rogo.
   *
   * @return void
   */
  protected function load_behat_config() {
    $file = __DIR__ . '/..' . self::BEHAT_CONFIG_FILE;
    if (!file_exists($file)) {
      return;
    }
    $data = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
    foreach($data as $setting) {
      $this->data['cfg_behat_' . $setting->getName()] = (string)$setting;
    }
  }
  
  /**
   * Loads the phpunit configuration for Rogo.
   *
   * @return void
   */
  protected function load_phpunit_config() {
    $file = __DIR__ . '/..' . self::PHP_UNIT_CONFIG_FILE;
    if (!file_exists($file)) {
      return;
    }
    $data = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
    foreach($data as $setting) {
      $this->data['cfg_phpunit_' . $setting->getName()] = (string)$setting;
    }
  }

  /**
   * Checks if all the required behat configuration settings are present.
   * 
   * @return boolean
   */
  public function is_behat_configured() {
    // Has the behat access url been configured?
    $behatwebsite = $this->get('cfg_behat_website');
    if (empty($behatwebsite)) {
      return false;
    }
    // Has the behat database been configured, and is it different to the live database?
    $behatdatabase = $this->get('cfg_behat_db_database');
    if (empty($behatdatabase) or $behatdatabase === $this->get('cfg_db_database')) {
      return false;
    }
    // Has a behat data directory been configured?
    $behatdatadir = $this->get('cfg_behat_data');
    if (empty($behatdatadir) or $behatdatadir === $this->get('cfg_rogo_data')) {
      return false;
    }
    // We got this far everything is good.
    return true;
  }
  
  /**
   * Checks if all the required phpunit configuration settings are present.
   * 
   * @return boolean
   */
  public function is_phpunit_configured() {
    // Has the phpunit database been configured, and is it different to the live database?
    $phpunitdatabase = $this->get('cfg_phpunit_db_database');
    if (empty($phpunitdatabase) or $phpunitdatabase === $this->get('cfg_db_database')) {
      return false;
    }
    // Has a phpunit data directory been configured?
    $phpunitdatadir = $this->get('cfg_phpunit_data');
    if (empty($phpunitdatadir) or $phpunitdatadir === $this->get('cfg_rogo_data')) {
      return false;
    }
    // We got this far everything is good.
    return true;
  }

  /**
   * Check url passed in argument matches that of site being accessed.
   * @param $parsedurl url of test site
   * @return bool true on match
   */
  private function checkurl($parsedurl) {
    $parsedurl['port'] = isset($parsedurl['port']) ? $parsedurl['port'] : 80;
    $parsedurl['path'] = rtrim($parsedurl['path'], '/');

    $pos = strpos($_SERVER['HTTP_HOST'], ':');
    if ($pos !== false) {
      $requestedhost = substr($_SERVER['HTTP_HOST'], 0, $pos);
    } else {
      $requestedhost = $_SERVER['HTTP_HOST'];
    }

    // The path should also match.
    if (empty($parsedurl['path'])) {
      $matchespath = true;
    } else if (strpos($_SERVER['SCRIPT_NAME'], $parsedurl['path']) === 0) {
      $matchespath = true;
    }

    // The host and the port should match
    if ($parsedurl['host'] == $requestedhost && $parsedurl['port'] == $_SERVER['SERVER_PORT'] && !empty($matchespath)) {
      return true;
    }

    return false;
  }
  
  /**
   * Test if Rogo is being accessed as a behat website.
   *
   * @return boolean
   */
  protected function is_behat_site() {
    $behaturl = $this->get('cfg_behat_website');
    $parsedurl = parse_url($behaturl . '/');
    return $this->checkurl($parsedurl);
  }

  /**
   * Test if Rogo is being accessed as a phpunit suite.
   *
   * @return boolean
   */
  protected function is_phpunit_site() {
    // Check if unittest constant has been defined.
    return defined('PHPUNIT_ROGO_TESTSUITE');
  }
  
  /**
   * Setup Rogo site to use the the behat database.
   *
   * @return void
   */
  public function use_behat_site() {
    if ($this->behatsetup) {
      // We do not want to run this code twice.
      return;
    }
    // Store the original database name, it is used during behat site installs.
    $this->set('base_database', $this->get('cfg_db_database'));
    $this->set('cfg_db_database', $this->get('cfg_behat_db_database'));
    // Behat may not be able to use a secure connection.
    $this->set('cfg_secure_connection', false);
    // Do not share sessions with the live site.
    if ($this->get('cfg_session_name') !== 'ROGOBEHAT') {
      $this->set('cfg_session_name', 'ROGOBEHAT');
    } else {
      $this->set('cfg_session_name', 'ROGOBEHATactual');
    }
    // Use the correct user data directory.
    $this->set('cfg_rogo_data', $this->get('cfg_behat_data'));
    // Fix the password salt for behat tests.
    $authentication = $this->get('authentication');
    foreach($authentication as &$authmethod) {
      if ($authmethod[0] === 'internaldb') {
        $authmethod[1]['encrypt_salt'] = 'F1rIPkEU8HV7HFnp';
      }
    }
    $this->set('authentication', $authentication);
    $this->behatsetup = true;
  }
  
  /**
   * Setup Rogo site to use the the phpunit database.
   *
   * @return void
   */
  public function use_phpunit_site() {
    if ($this->phpunitsetup) {
      // We do not want to run this code twice.
      return;
    }
    // Store the original database name, it is used during behat site installs.
    $this->set('base_database', $this->get('cfg_db_database'));
    $this->set('cfg_db_database', $this->get('cfg_phpunit_db_database'));
    // Use the correct user data directory.
    $this->set('cfg_rogo_data', $this->get('cfg_phpunit_data'));
    $this->phpunitsetup = true;
  }


  /**
   * Store the db object to prevent having to pass it as a parameter in methods
   * @param mysqli $db
   */
  public function set_db_object($db) {
    $this->db = $db;
  }

  function error_handling($context = null) {
 //   print "<br>confobj:errorfuncrun<br>";
    return "config Object: hidden for security";
  }

  function export_all() {
    return $this->data;
  }

  /**
   * Set a particular config setting's value
   * @param string $var The name of the config setting
   * @param string $value
   */
  function set($var, $value) {
    $this->data[$var] = $value;
  }

  /**
   * Cache a component setting in the config object's "settings" property
   * @param string $setting
   * @param string $value
   * @param string $component
   */
  protected function cache_setting($setting, $value, $component = 'core') {
    $this->settings[$component][$setting] = $value;
  }

  /**
   * Set a particular config setting's value for a particular component
   * @param string $setting The name of the config setting
   * @param string|array $value
   * @param string $component (Optional) The component to which this setting belongs
   */
  public function set_setting($setting, $value, $component = 'core') {
    $currentsetting = $this->get_setting($component, $setting);
    $this->cache_setting($setting, $value, $component);

    if ($currentsetting) {
      $this->update_setting($setting, $value, $component);
    } else {
      $this->insert_setting($setting, $value, $component);
    }
  }

  /**
   * Update a config setting for a particular component
   * @param string $setting The name of the config setting
   * @param string|array $value
   * @param string $component (Optional) The component to which this setting belongs
   */
  protected function update_setting($setting, $value, $component = 'core') {
    // Update Settings.
    $result = $this->db->prepare("UPDATE `config` SET `value`= ? WHERE component = ? AND setting = ?");
    $result->bind_param("sss", $value, $component, $setting);

    if ($result->execute()) {
      $result->close();
    }
  }

  /**
   * Insert a config setting for a particular component
   * @param string $setting The name of the config setting
   * @param string $value The value of the config setting
   * @param string $component The component to which this config setting belongs
   */
  protected function insert_setting($setting, $value, $component = 'core') {
    // Insert Settings.
    $result = $this->db->prepare("INSERT INTO `config` (`component`, `setting`, `value`) VALUES (?, ?, ?)");
    $result->bind_param("sss", $component, $setting, $value);

    if ($result->execute()) {
      $result->close();
    }
  }

  function append($var, $value) {
    $this->data[$var]=$this->data[$var] . $value;
  }

  /**
   * Get a config setting for a particular component
   * @param string $component The component to which this config setting belongs
   * @param string $setting The name of the config setting (Optional)
   */
  public function get_setting($component, $setting = null) {
    $cachedsetting = $this->get_setting_from_cache($component, $setting);
    if ($cachedsetting) {
      return $cachedsetting;
    }

    $this->load_settings($component);
    $cachedsetting = $this->get_setting_from_cache($component, $setting);
    if ($cachedsetting) {
      return $cachedsetting;
    }
    return null;
  }

  /**
   * Get setting from cache
   * @param string $component
   * @param string $setting
   * @return string|array
   */
  protected function get_setting_from_cache($component, $setting = null) {
    if (is_string($component)) {
      if (is_string($setting) && isset($this->settings[$component]) && isset($this->settings[$component][$setting])) {
        return $this->settings[$component][$setting];
      } else if (isset($this->settings[$component]) && empty($setting)) {
        return $this->settings[$component];
      }
    }
    return null;
  }

  /**
   * Load all settings for a particular component into the 'settings' property of the config object
   * @param string $component The component to which this config setting belongs
   */
  public function load_settings($component) {
    $setting = null;
    $value = null;
    $result = $this->db->prepare("SELECT setting, value FROM config WHERE component = ?");
    $result->bind_param('s', $component);
    $result->bind_result($setting, $value);
    $result->execute();
    while ($result->fetch()) {
      $this->cache_setting($setting, $value, $component);
    }
    $result->close();
  }

  /**
   * Get the value of a particular config setting
   * @param string $var
   * @return string||void Return setting as string if found.  Otherwise return null.
   */
  function get($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    } elseif (is_array($var)) {
      $dat = array();
      foreach ($var as $key) {
        if (isset($this->data[$key])) {
          $dat[$key]=$this->data[$key];
        }
      }
      return $dat;
    }
    return null;
  }

  /**
   * Get value of xml node.
   *
   * @param string $parent name of xml node
   * @param string $child xml child node name
   * @param string $grandchild xml grandchild node name
   * @return value of xml node
   */
  function getxml($parent, $child = '', $grandchild = '') {
    $xmldata = json_decode($this->xmldata);
    if (is_string($parent)) {
      if (isset($xmldata->$parent)) {
        if ($child == '' and $grandchild == '') {
          return $xmldata->$parent;
        } elseif ($child != '' and $grandchild == '') {
          if (isset($xmldata->$parent->$child)) {
             return $xmldata->$parent->$child;
          }
        } else {
          if (isset($xmldata->$parent->$child->$grandchild)) {
             return $xmldata->$parent->$child->$grandchild;
          }
        }
      }
    }
    return null;
  }

  function &getbyref($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    }

    $fake = null;
    return $fake;
  }
}
