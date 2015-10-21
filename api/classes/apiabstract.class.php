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
 */
abstract class apiabstract {

    /**
     * Abstract constructor
     * @param string $request api request 
     */
    abstract protected function __construct($request);
    /**
     * Abstract validate request
     * @param string $folder location of schema
     * @param string $type file type 
     */
    abstract protected function validate($folder, $type);
    /**
     * Abstract get response
     */
    abstract protected function getdata();
    /**
     * Abstract parse request
     * @param object $tasktype task object
     * @param array $fields expected fields
     * @param array $actions possible actions
     * @param object $data xml data
     * @param string $task the task to be carried out
     */
    abstract protected function parse($tasktype, $fields, $actions, $data, $task);
    
}