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
* Implement VLE API for NLE
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once 'CMAPI.if.php';
require_once dirname(dirname(__DIR__)) . '/webServices/RestRequest.class.php';

class CM_NLE implements iCMAPI {
  private $_mapping_level = self::LEVEL_SESSION;

  /**
   * Return objectives from the University of Nottingham Medical School Networked Learning Environment
   * @param string $moduleID The module code to be looked up
   * @param int $session The year that the academic year starts in.
   * @param mysqli $db database connection
   * @return mixed Array of session and objective data in format required by Rogō
   */
  public function getObjectives($moduleID, $session, $db) {
    $configObject = Config::get_instance();
    $originalmodid = $moduleID;
    // To create nle year paramerter. End year must be 2 digit.
    $endyear = $session + 1;
    $endyear = substr((string)$endyear, -2);
    $nle_year = (string)$session . '/' . $endyear;
    // Map module code if necessary.
    $moduleID = \plugins\plugins_mapping::do_mapping($db, $moduleID);
    $objectives = new RestRequest($configObject->get('cfg_nle_url') . "/webServices/RogoRestAPI.php?url=getObjectives/$moduleID/$nle_year");
    $objectives->execute();
    $response = $objectives->getResponseBody();
    $mappedresponse = $this->map_response($response, $moduleID, $originalmodid);
    return $mappedresponse;
  }

  /**
   * Get a friendly name for the source system, with the indefinite article if required
   * @param bool $a     Include the definite article?
   * @param bool $long  Return the long form of the name?
   * @return string     The name in the required format
   */
  public function getFriendlyName($a = false, $long = false) {
    $name = ($long) ? 'Networked Learning Environment' : 'NLE';
    $name = ($a) ? 'a ' . $name : $name;
    return $name;
  }

  /**
   * Get the levels of mapping that are supported by this class
   * @return array Array of mapping levels supported
   */
  public function getMappingLevels() {
    return array(self::LEVEL_SESSION);
  }

  /**
   * Set the mapping level at which the class should work
   * @param integer $level Mapping level
   */
  public function setMappingLevel($level) {
    // Ignore anything passed in, we only support session level mapping
    $this->_mapping_level = self::LEVEL_SESSION;
  }
  
  /**
   * Map new type modules codes to the old modules codes in NLE response
   * @param string $response data from NLE
   * @param string $newmodid new style module id
   * @param string $oldmodid old style module id
   * @return array NLE response mapped
   */
  private function map_response($response, $newmodid, $oldmodid) {
      $decode = json_decode($response, true);
      $keys = array_keys($decode);
      $index = array_search($oldmodid, $keys);
      if ($index !== false) {
          $keys[$index] = $newmodid;
          $mappedarray = array_combine($keys, $decode);
      }
      return json_encode($mappedarray);
  }
}
?>
