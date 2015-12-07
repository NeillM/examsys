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

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

// Start Rogo autoloading.
require_once dirname(dirname(dirname(__DIR__))) . '/include/autoload.inc.php';
autoloader::init();

use testing\behat\selectors;

/**
 * This is the base definitions file for Rogo.
 *
 * Please do not add setps to it directly. It will load all definition files steps directory.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
class rogo_behat_base extends BehatContext
{
  /**
   * Initializes context.
   * Every scenario gets its own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters)
  {
    foreach ($this->get_contexts() as $name => $context) {
      $this->useContext($name, $context);
    }
  }

  /**
   * Searches for all steps definition files. It creates an instance of the class
   * and sends it back in an array that will be used to add them as sub-contexts.
   *
   * This function should realy only used directly in the constructor with no parameters
   * and recursively by itself.
   *
   * @param string $relativepath A path relative to the steps directory.
   * @return BehatContext[]
   */
  protected function get_contexts($relativepath = '') {
    $fullpath = dirname(__DIR__) .DIRECTORY_SEPARATOR . 'steps' . DIRECTORY_SEPARATOR . $relativepath;
    list($directories, $files) = $this->get_directories_and_files($fullpath);
    $contexts = array();
    $contextprefix = implode('_', explode(DIRECTORY_SEPARATOR, $relativepath));
    // If we are not in the base directory then all the files found should be loaded.
    if (!empty($relativepath)) {
      foreach ($files as $file) {
        // Remove .php from the filename then prepend the relative path seprated by underscores.
        $contextname = $contextprefix . '_' . substr($file, 0, -4);
        require_once $fullpath . DIRECTORY_SEPARATOR . $file;
        $contexts[$contextname] = new $contextname();
      }
      // Add the correct seperator to the relative path.
      $relativepath .= DIRECTORY_SEPARATOR;
    }
    // Recurse through any sub-directories.
    foreach ($directories as $directory) {
      $contexts = array_merge($contexts, $this->get_contexts($relativepath . $directory));
    }
    return $contexts;
  }

  /**
   * Returns an array containing an array of directories
   * and an array of files of the directory passed.
   *
   * @param string $directory
   * @return array
   */
  protected function get_directories_and_files($directory) {
    $contents = scandir($directory, SCANDIR_SORT_ASCENDING);
    $directories = array();
    $files = array();
    foreach ($contents as $item) {
      $fullpath = $directory . DIRECTORY_SEPARATOR . $item;
      if (is_dir($fullpath) && $item !== '.' && $item !== '..') {
        // We only want sub-directories, not the self and parent directory markers.
        $directories[] = $item;
      } else if (is_file($fullpath) && substr($item, -4) === '.php') {
        // Only find files with a .php extension.
        $files[] = $item;
      }
    }
    return array($directories, $files);
  }

  /**
   * Returns whether the scenario is running in a browser that can run Javascript or not.
   *
   * @return boolean
   */
  protected function running_javascript() {
    return get_class($this->getSession()->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
  }
}
