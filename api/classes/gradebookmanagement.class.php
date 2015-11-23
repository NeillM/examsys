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
* Gradebook api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Gradebook class
 */
class gradebookmanagement {
    
    // The database connection.
    private $db;

    
    /**
     * @brief Constructor
     * @param mysqli $mysqli the database connection
     * @param ojbect $configObject configuration items
     * @return  
     */
    function __construct($mysqli) {
        $this->db = $mysqli;
    }
    
    /**
     * @brief Get data.
     * @param integer $filtername 
     * @param integer $filterid
     * @return array
     */
    public function get($filtername, $filterid) {
        $gradebook = new \gradebook($this->db);
        if ($filtername == 'paper') {
            $grades = $gradebook->get_paper_gradebook($filterid);
        } elseif ($filtername == 'module') {
            $grades = $gradebook->get_module_gradebook($filterid);
        }
        if ($grades) {
            return array('OK', $grades);
        } else {
            return array('BAD', array('Gradebook not found for ' . $filtername . ' ' . $filterid));
        }
    }
    
}