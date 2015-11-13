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
 * Test langpack class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 * @package tests
 */
class langpacktest extends PHPUnit_Framework_TestCase {

    /**
     * Test get_string  
     */
    public function testgetstring() {
        $lang = new langpack();
        $component = 'api/usermanagement';
        $name = '404';
        $string = $lang->get_string($component, $name);
        $this->assertEquals('404 Page Not Found', $string);
    }

    /**
     * Test get_strings  
     */
    public function testgetstrings() {
        $lang = new langpack();
        $component = 'api/usermanagement';
        $names = array('user_invalid_role', 'user_does_not_exist');
        $strings = $lang->get_strings($component, $names);
        $this->assertEquals('User has invalid role', $strings['user_invalid_role']);
        $this->assertEquals('User does not exist', $strings['user_does_not_exist']);
    }
}