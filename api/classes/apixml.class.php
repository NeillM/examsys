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
* API XML functionality
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * API XML class
 */
class apixml extends \api\apiabstract {
   
    // XML data.
    private $data;
    
    // Language pack component.
    private $langcomponent = 'api/apixml';
    
    /**
     * @brief Constructor
     * @param string $request - the xml request
     * @return  
     */
    function __construct($request) {
        // Enable user error handling
        libxml_use_internal_errors(true);
        $this->data = new \DOMDocument();
        $this->data->loadXML($request);
    }
    
    /**
     * @brief Validate the xml request againt an XSD
     * @param string $folder - sub dir where xsd is located
     * @param string $type - the filename
     * @return array - errors
     */
    public function validate($folder, $type) {
        $schema = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . $folder
        . DIRECTORY_SEPARATOR . $type . '.xsd';
        $errorresp = array();
        if (!$this->data->schemaValidate($schema)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $errorresp[] = sprintf("[%s] Line %s - %s", $error->line, $error->code, trim($error->message));
            }
            libxml_clear_errors();
        }
        return $errorresp;
    }
    
    /**
     * @brief Get the response data
     * @return array
     */
    public function getdata() {
        return $this->data;
    }
    
    /**
     * @brief Parse the request and process it.
     * @param object $tasktype task object
     * @param array $fields expected fields
     * @param array $actions possible actions
     * @param object $xml xml data
     * @param array $task user permissions
     * @return  
     */
    public function parse($tasktype, $fields, $actions, $xml, $perms) {
        $langpack = new \langpack();
        $response = array();
        foreach ($actions as $action) {
            $parentnode = $xml->getElementsByTagName($action);
            $error = false;
            foreach ($parentnode as $node) {
                if ($perms[$action]) {
                    if ($node->childNodes->length) {
                        foreach ($node->childNodes as $i) {
                            foreach ($fields as $field) {
                                if ($i->nodeName == $field) {
                                     if ($i->childNodes->length > 1) {
                                        $params[$field] = $i;
                                     } else {
                                        $params[$field] = $i->nodeValue;
                                     }
                                }
                            }   
                        }
                        $params['nodeid'] = $node->getAttribute('id');
                    } 
                } else {
                    $error = true;
                    $data = array('status' => $langpack->get_string($this->langcomponent, 'nopermission'), 'id' => null);
                }
                if ($error) {
                    $response[] = &$tasktype->get_response($data, $action, $node->getAttribute('id'));
                } else {
                    $response[] = &$tasktype->$action($params);
                }
            }
        }
        return $response;
    }
}