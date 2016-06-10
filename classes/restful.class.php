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
* Restful API package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Restful API helper class.
 */
class restful {
    /**
     * @var DB connection
     */
    private $db;
    /**
     * Constuctor
     * @param mysqli $db
     */
    function __construct($db) {
        $this->db = $db;
    }
    /**
     * Perform a restful get request
     * @param string $url api url
     * @param array $requestoptions curl options from the requestor
     * @return string response from api
     */
    public function get($url, $requestoptions = array()) {
        $curl = curl_init();
        // Curl options.
        $options = array(CURLOPT_URL => $url,
                 CURLOPT_RETURNTRANSFER => 1,
                 CURLOPT_FAILONERROR => true
                );
        $options += $requestoptions;
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $log = new Logger($this->db);
            $userObj = $userObject = \UserObject::get_instance();
            $userid = $userObj->get_user_ID();
            $username = $userObj->get_username();
            $errorfile = $_SERVER['PHP_SELF'];
            $errorline = __LINE__ - 7;
            $log->record_application_warning($userid, $username, 'Connection error: ' . curl_errno($curl) . ' - ' . curl_error($curl), $errorfile, $errorline);
            $response = '';
        }
        curl_close($curl);
        return $response;
    }
}