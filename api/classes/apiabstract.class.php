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
 * Abstract API class.
 * 
 * Use this class when defining supported media types such as 'xml/text'.
 */
abstract class apiabstract {

    /**
     * Abstract constructor
     * @param string $request api request 
     */
    abstract public function __construct($request);
    
    /**
     * Abstract validate request
     * 
     * Validate the request body against a schema/dtd.
     * @param string $folder location of schema
     * @param string $type file type 
     * @return array - list of errors in the request body
     */
    abstract protected function validate($folder, $type);
    
    /**
     * Abstract get response
     * 
     * Get the response body for the request.
     * @return array - the response data
     */
    abstract protected function getdata();
    
    /**
     * Abstract parse request
     * 
     * Carry out the operations in the request.
     * @param object $tasktype task object
     * @param array $fields expected fields
     * @param array $actions possible actions
     * @param object $body request body
     * @param string $task the task to be carried out
     * @return string - successful operation response or error response
     */
    abstract protected function parse($tasktype, $fields, $actions, $body, $task);
    
}