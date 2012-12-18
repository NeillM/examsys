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
* A Class to be used as a base class for Rogo Singleton utility classes
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

Class RogoStaticSingleton {
  
  /**
  * Create and return the Global instance of UserNotices for use in the Local 
  * scope
  */
  public static function get_instance()
  {
    if (!is_object(static::$inst)) {
      static::$inst = new static::$class_name;
    }
    return static::$inst;
  }

  /**
  * sets the Mock instance to return. ONLY used for testing 
  * 
  */
  public static function set_mock_instance($obj)
  {
    static::$inst = $obj;
  }

  /**
  *  
  * 
  */
  public static function  __callStatic($name, $args)
  {
  	//if(is_callable(array(static::$inst,$name))) {
		call_user_func_array(array(static::$inst,$name), $args);
	//} else {
	//	throw new Excption($name . " not implimented in " . static::$class_name); 
	//}
  }

}

?>