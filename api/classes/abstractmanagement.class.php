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
* Abstract API functionality
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Abstract management class.
 */
abstract class abstractmanagement {
       
    /**
     * Abstract constructor
     * @param mysqli $request - db connection
     * @param object $configObject - rogo config object
     */
    abstract protected function __construct($mysqli, $configObject);
    
    /**
     * @brief Abtract response creator
     * @param array $data - Response data
     * @param string $action - Relevant action
     * @param integer $nodeid - Request Node id
     * @param array $error - array of errors generated
     */
    abstract protected function get_response($data, $action, $nodeid, $error);
   
    /**
     * @brief Abstract create function
     * @param array $params - parametes in request
     * @return  
     */
    abstract protected function create($params);
    
    /**
     * @brief Abstract delete function
     * @param array $params - parametes in request
     * @return  
     */
    abstract protected function delete($params);
    
}