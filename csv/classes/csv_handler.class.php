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
 * CSV file package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */

namespace csv;

/**
 * CSV handler helper class.
 */
class csv_handler {
  /**
   * The path to the csv file.
   * @var string
   */
  protected $file;

  /**
   * Reading pointer to the file from fopen()
   * @var resource
   */
  protected $read_file_handle;

  /**
   * The header that is required by the file.
   * @var array
   */
  protected $required_header;

  /**
   * The header of the file.
   * @var array
   */
  protected $header;

  /**
   * Language pack component.
   */
  const langcomponent = 'csv/csv_handler';

  /**
   * Initialise the handler.
   * @param string $file The path to the file to be used
   * @throws csv_load_exception
   */
  public function __construct($file) {
    $filename = basename($file);
    $directory = dirname($file);
    $fullpath = realpath($directory);
    $langpack = new \langpack();
    $error = $langpack->get_string(self::langcomponent, "invalidpath");
    if ($fullpath === false) {
      throw new csv_load_exception($file . $error);
    }
    $this->file = $fullpath . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Move upload file to tmp dir
   * @param $from upload file
   * @param $to temp file location
   * @throws csv_load_exception
   * @return string
   */
  public static function move_upload_to_temp($from, $to) {
    $langpack = new \langpack();
    $string = $langpack->get_strings(self::langcomponent, array('nofilename', 'csvonly', 'maxfilesize', 'partialupload', 'nofileuploaded', 'notempdir', 'unknownissue'));
    if ($from['name'] == 'none' and $from['name'] == '') {
      throw new csv_load_exception($string['nofilename']);
    }
    if (pathinfo($from['name'], PATHINFO_EXTENSION) != 'csv') {
      throw new csv_load_exception($string['csvonly']);
    }
    if (!move_uploaded_file($from['tmp_name'], $to)) {
      switch ($from['error']) {
        case 2:
        case 3:
          $err = string['maxfilesize'];
          break;
        case 4:
          $err = $string['partialupload'];
          break;
        case 5:
          $err = $string['nofileuploaded'];
          break;
        case 6:
          $err = $string['notempdir'];
          break;
        default:
          $err = $string['unknownissue'];
          break;
      }
      throw new csv_load_exception($err);
    }
    return $to;
  }

  /**
   * Set a header required by the file for it to validate.
   *
   * @param array $headers
   */
  public function required_header(array $headers) {
    $this->required_header = $headers;
  }

  /**
   * Opens the file for reading, and verifies the header.
   *
   * @throws csv_load_exception
   */
  protected function load() {
    $langpack = new \langpack();
    $string = $langpack->get_strings(self::langcomponent, array('doesnotexist', 'cannotberead', 'invalidheaders'));
    // Check the file exists and is readable.
    if (!file_exists($this->file)) {
      throw new csv_load_exception($this->file . $string['doesnotexist']);
    }
    if (!is_readable($this->file)) {
      throw new csv_load_exception($this->file . $string['cannotberead']);
    }
    // Open the file.
    $this->read_file_handle = fopen($this->file, 'r');
    // The first line should be the header of the file.
    $this->header = fgetcsv($this->read_file_handle);
    if (!$this->verify_header()) {
      throw new csv_load_exception($this->file . $string['invalidheaders']);
    }
  }

  /**
   * Gets a line of data from the csv file as an associative
   * array where the values are keyed by the headers.
   *
   * @return array
   * @throws csv_load_exception
   */
  public function get_line() {
    if (!isset($this->read_file_handle)) {
      // The file has not been opened for reading.
      $this->load();
    }
    $line = fgetcsv($this->read_file_handle);
    $return = array();
    if (!empty($line)) {
      // Create an associative array of the csv line, where the
      // keys match the header of their row.
      foreach ($this->header as $key => $value) {
        $return[$value] = $line[$key];
      }
    }
    return $return;
  }

  /**
   * Delete csv file
   * @param $file file to delete
   */
  public function delete($file) {
    unlink( $file);
  }

  /**
   * Check that the csv file has a the required headers.
   *
   * @return boolean true if the header validates
   */
  protected function verify_header() {
    if (empty($this->header)) {
      // No header is set.
      $valid = false;
    } elseif (!isset($this->required_header)) {
      // A specific header is not required, so any values are great.
      $valid = true;
    } else {
      $found = 0;
      foreach ($this->header as $header) {
        if (in_array($header, $this->required_header)) {
          $found++;
        }
      }
      if ($found === count($this->required_header)) {
        // The header is as required.
        $valid = true;
      } else {
        // The header is not as required.
        $valid = false;
      }
    }
    return $valid;
  }
}
