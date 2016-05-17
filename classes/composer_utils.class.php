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

/* 
 * This class is used to install and update composer.
 */
class composer_utils {
  /** Composer should install dependancies respecting the lock file. */
  const INSTALL = 1;

  /** Composer should get the laest versions of the depedencies and update the lock file. */
  const UPDATE = 2;

  /**
   * Ensures that composer is installed, uptodate and has installed all the projects dependancies.
   *
   * @return void
   */
  public static function setup($method = self::INSTALL) {
    // We are going to chage the working directory and want to reset it later.
    $workingdir = getcwd();
    // Change to the root Rogo directory.
    chdir(__DIR__ . '/..');
    self::install_update();
    if ($method === self::UPDATE) {
      self::update_dependancies();
    } else {
      self::fetch_dependancies();
    }
    chdir($workingdir);
  }

  /**
   * Ensures composer is installed and upto date.
   *
   * @return void
   */
  protected static function install_update() {
    if (!file_exists(__DIR__ . '/../composer.phar')) {
      // Composer needs to be installed.
      passthru("curl http://getcomposer.org/installer | php", $statuscode);
      if ($statuscode != 0) {
        throw new Exception('Cannot install composer, on Windows you will need to manually download it.');
      }
    } else {
      // Composer needs to be updated.
      passthru("php composer.phar self-update", $statuscode);
      if ($statuscode != 0) {
        throw new Exception('Cannot update composer.');
      }
    }
  }

  /**
   * Downloads and installs all the files required by the composer.lock file for the project.
   *
   * @return void
   */
  protected static function fetch_dependancies() {
    passthru("php composer.phar install", $statuscode);
    if ($statuscode != 0) {
      throw new Exception('Could not install components.');
    }
  }

  /**
   * Downloads and installs all the files required by the composer.json file for the project.
   *
   * @return void
   */
  protected static function update_dependancies() {
    passthru("php composer.phar update", $statuscode);
    if ($statuscode != 0) {
      throw new Exception('Could not update components.');
    }
  }
}
