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
* API functionality
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * API Class
 */
class api {

    // The slim application.
    private $app;
    
    // The media type.
    private $mediatype;
    
    // Language pack component.
    private $langcomponent = 'api/api';
    
    /**
     * @brief Constructor
     * @param object $app the slim application
     * @return  
     */
    function __construct($app) {
        $this->app = $app;
    }
       
    /**
     * @brief Set the header for the response.
     * @param string $type - header type
     * @return  
     */
    public function set_header($type = 'text/xml') {
        $this->app->response()->header("Content-Type", $type);
    }
    
    /**
     * @brief Get the body of the request.
     * @return  
     */
    public function get_body() {
        return $this->app->request->getBody();
    }

    /**
     * @brief Get the media type of the request.
     * @return string - media type
     */
    public function get_mediatype() {
        $mediatype = $this->app->request()->getMediaType();
        if ($mediatype == 'text/xml') {
            $this->mediatype = $mediatype;
            return $mediatype;
        } else {
            return false;
        }
    }
    
    /**
     * @brief Process the request
     * @param string $folder - location of validation schema
     * @param string $type - filename of validation schema
     * @return array - status and response
     */
    public function process($folder, $type) {
        $langpack = new \langpack();
        // Set response header
        $this->set_header($this->get_mediatype());
        // Get body of request.
        $body = $this->get_body();
        $api = new \api\apixml($body);  
        // Valdate request
        $errorresp = $api->validate($folder, $type);
        if (count($errorresp) > 0) {
            return array('BAD', $errorresp);
        } else {
            return array('OK', $api->getdata());
        }
    }
    
    /**
     * @brief Parse the request and process it.
     * @param object $tasktype task object
     * @param array $fields expected fields
     * @param array $actions possible actions
     * @param object $xml xml data
     * @param array $perms user permissions
     * @return  
     */
    public function parse($tasktype, $fields, $actions, $xml, $perms) {
        $api = new \api\apixml($body);
        return $api->parse($tasktype, $fields, $actions, $xml, $perms);
    }
    
}