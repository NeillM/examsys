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
 * Enables class autoloading in Rogō.
 *
 * To autoload the following must be true:
 * - The name of the class must match the name of the file
 * - The file must have the extension of .class.php
 * - If no namespace is used the file must be in the classes directory
 *
 * @copyright 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 */
class autoloader {
  /**
   * Sets up autoloading.
   */
  public static function init() {
    if (function_exists('spl_autoload_register')) {
      spl_autoload_register(array('autoloader', 'load_class'));
    } else {
      // This is to allow autoloading in versions of PHP that do not support spl_autoload_register().
      function __autoload($class)
      {
        autoloader::load_class($class);
      }
    }
  }

  /**
   * The function that does the autoloading.
   * 
   * @param string $class
   */
  protected static function load_class($class) {
    $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . strtolower($class) . '.class.php';
    if (file_exists($filename)) {
      include_once $filename;
    }
  }
}
