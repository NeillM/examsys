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
 * Utility class for languages and translations. 
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 * @package core
 */

class langpack {
       
    private $langdir;
    
    /**
     * @brief Constructor
     * @return  
     */
    function __construct() {
        $configObject = Config::get_instance();
        $cfg_web_root = $configObject->get('cfg_web_root');
        $this->langdir = LangUtils::getLang($cfg_web_root);
    }
    
    /**
     * @brief Get the string value.
     * @param string $component 
     * @param string $name 
     * @return  
     */
    public function get_string($component, $name) {
        $componentparts = explode('/', $component);
        $subdir = $componentparts[0];
        $file = $componentparts[1];
        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR
            . 'lang' . DIRECTORY_SEPARATOR . $this->langdir . DIRECTORY_SEPARATOR . $file . '.lang.php';
        include $filename;
        return $string[$name];
    }
    
    /**
     * @brief Get the value of X strings.
     * @param string $component 
     * @param array $names
     * @return  
     */
    public function get_strings($component, $names) {
        $componentparts = explode('/', $component);
        $subdir = $componentparts[0];
        $file = $componentparts[1];
        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR
            . 'lang' . DIRECTORY_SEPARATOR . $this->langdir . DIRECTORY_SEPARATOR . $file . '.lang.php';
        include $filename;
        $strings = array();
        foreach ($names as $name) {
            $strings[$name] = $string[$name];
        }
        return $strings;
    }
}