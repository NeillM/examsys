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
* Requirements package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * Requirements helper class.
 */
class requirements {
  /**
   * Check php version meets minimum requirements.
   * @return boolean
   */
  public static function check_php_version() {
    $configObject = Config::get_instance();
    $php_min_ver = $configObject->getxml('php', 'min_version');
    $phpversion = phpversion();
    if (version_compare($phpversion, $php_min_ver , '<')) {
      return false;
    }
    return true;
  }
  /**
   * Check required php extensions are enabled.
   * @return boolean
   */
  public static function check_php_extensions() {
    $ext = array();
    $configObject = Config::get_instance();
    $phpModules = get_loaded_extensions();
    $extensions = $configObject->getxml('php', 'extensions');
    foreach ($extensions->extension as $extension) {
      if (!in_array($extension, $phpModules)) {
         $ext[$extension] = false;
      } else {
        $ext[$extension] = true;
      }
    }
    return $ext;
  }
  /**
   * Install composer and update libraries to required versions.
   * @return mixed
   */
  public static function check_composer() {
    try {
      ob_start();
      composer_utils::setup(composer_utils::INSTALL_NODEV);
      ob_end_clean();
    } catch (Exception $e) {
      return $e->getMessage();
    }
    return true;
  }
  /**
   * Update NPM libraries to required versions.
   * @return mixed
   */
  public static function check_npm() {
    try {
      ob_start();
      npm_utils::setup(npm_utils::INSTALL_NODEV);
      ob_end_clean();
    } catch (Exception $e) {
      return $e->getMessage();
    }
    return true;
  }
  /**
   * Check db version meets minimum requirements.
   * @return boolean
   */
  public static function check_db() {
    $return = true;
    $configObject = Config::get_instance();
    $check = mysqli_connect($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'));
    if (mysqli_connect_error()) {
      $return = false;
    }
    $mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
    $mysql_version = mysqli_get_server_version($check);
    if($mysql_version < $mysql_min_ver) {
      $return = false;
    }
    $check->close();
    return $return;
  }
}