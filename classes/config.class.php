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

  /**
   *
   */
/*
  public static function get_instance()
  {
    if (!is_object(self::$inst)) {
      self::$inst = new Config();
    }
    return self::$inst;
  }
*/

  function __Clone() {
 //   print "conf cloned";

  }

  function __toString() {
    return "ConfigObject!";
  }

  protected function __construct() {

    // Get out of the box config information.
    $file = __DIR__ . '/../config/rogo.xml';
    $this->xmldata = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
    // Get installed system config information.
    $conf_file = __DIR__ . '/../config/config.inc.php';
    if (file_exists($conf_file)) {
      include $conf_file;
    }
    $this->data = get_defined_vars();
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

    if ($setting) {
      $value = null;
      $result = $this->db->prepare("SELECT `value` FROM `config` WHERE component = ? AND setting = ?");
      $result->bind_param('ss', $component, $setting);
      $result->bind_result($value);
      $result->execute();
      while ($result->fetch()) {
        $result->close();
        return $value;
      }
    } else {
      $this->load_settings($component);
      $cachedsetting = $this->get_setting_from_cache($component);
      if ($cachedsetting) {
        return $cachedsetting;
      }
    }
    return null;
  }

  /**
   * Get setting from cache
   * @param string $component
   * @param string $setting
   * @return string|array
   */
  private function get_setting_from_cache($component, $setting = null) {
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
    if (is_string($parent)) {
      if (isset($this->xmldata->$parent)) {
        if ($child == '' and $grandchild == '') {
          return $this->xmldata->$parent;
        } elseif ($child != '' and $grandchild == '') {
          if (isset($this->xmldata->$parent->$child)) {
             return $this->xmldata->$parent->$child;
          }
        } else {
          if (isset($this->xmldata->$parent->$child->$grandchild)) {
             return $this->xmldata->$parent->$child->$grandchild;
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
