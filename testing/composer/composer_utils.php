<?php
/* 
 * This class is used to install and update composer.
 */
class composer_utils {
  /**
   * Ensures that composer is installed, uptodate and has installed all the projects dependancies.
   *
   * @return void
   */
  public static function setup() {
    // We are going to chage the working directory and want to reset it later.
    $workingdir = getcwd();
    // Change to the root Rogo directory.
    chdir(__DIR__ . '/../..');
    self::install_update();
    self::update_dependancies();
    chdir($workingdir);
  }

  /**
   * Ensures composer is installed and upto date.
   *
   * @return void
   */
  protected static function install_update() {
    if (!file_exists(__DIR__ . '/../../composer.phar')) {
      // Composer needs to be installed.
      passthru("curl http://getcomposer.org/installer | php", $statuscode);
      if ($statuscode != 0) {
        exit($statuscode);
      }
    } else {
      // Composer needs to be updated.
      passthru("php composer.phar self-update", $statuscode);
      if ($statuscode != 0) {
        exit($statuscode);
      }
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
      exit($statuscode);
    }
  }
}
