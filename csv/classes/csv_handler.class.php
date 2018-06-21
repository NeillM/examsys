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
class csv_handler
{
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
   * Initialise the handler.
   *
   * @param string $file The path to the file to be used
   * @throws csv_load_exception
   */
  public function __construct($file)
  {
    $filename = basename($file);
    $directory = dirname($file);
    $fullpath = realpath($directory);
    if ($fullpath === false) {
      throw new csv_load_exception($file . ' has an invalid path');
    }
    $this->file = $fullpath . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Set a header required by the file for it to validate.
   *
   * @param array $headers
   */
  public function required_header(array $headers)
  {
    $this->required_header = $headers;
  }

  /**
   * Opens the file for reading, and verifies the header.
   *
   * @throws csv_load_exception
   */
  protected function load()
  {
    // Check the file exists and is readable.
    if (!file_exists($this->file)) {
      throw new csv_load_exception($this->file . ' does not exist');
    }
    if (!is_readable($this->file)) {
      throw new csv_load_exception($this->file . ' cannot be read');
    }
    // Open the file.
    $this->read_file_handle = fopen($this->file, 'r');
    // The first line should be the header of the file.
    $this->header = fgetcsv($this->read_file_handle);
    if (!$this->verify_header()) {
      throw new csv_load_exception($this->file . ' has invalid headers');
    }
  }

  /**
   * Gets a line of data from the csv file as an associative
   * array where the values are keyed by the headers.
   *
   * @return array
   * @throws csv_load_exception
   */
  public function get_line()
  {
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
  public function delete($file){
    unlink( $file);
  }

  /**
   * Check that the csv file has a the required headers.
   *
   * @return boolean true if the header validates
   */
  protected function verify_header()
  {
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
