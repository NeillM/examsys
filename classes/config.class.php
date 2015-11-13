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
 * config file
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 *
 * Designed to hold the config options in a class for easier access.
 */
class Config extends RogoStaticSingleton {
  /**
   * @var array
   */
  public $data;
  public $xmldata;
  protected static $inst;
  protected static $class_name = 'Config';

  /**
   *
   */
/*
  public static function get_instance()
  {
    if (!is_object(self::$inst)) {
      self::$inst = new Config();
    }
    return self::$inst;
  }
*/

  function __Clone() {
 //   print "conf cloned";

  }

  function __toString() {
    return "ConfigObject!";
  }

  protected function __construct() {
 
    // Get out of the box config information.
    $file = __DIR__ . '/../config/rogo.xml';
    $this->xmldata = json_encode(simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA));
    // Get installed system config information.
    $conf_file = __DIR__ . '/../config/config.inc.php';
    if (file_exists($conf_file)) {
      include $conf_file;
    }
    $this->data = get_defined_vars();
  }

  function error_handling($context = null) {
 //   print "<br>confobj:errorfuncrun<br>";
    return "config Object: hidden for security";
  }

  function export_all() {
    return $this->data;
  }

  function set($var, $value) {
    $this->data[$var]=$value;
  }

  function append($var, $value) {
    $this->data[$var]=$this->data[$var] . $value;
  }

  function get($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    } elseif (is_array($var)) {
      $dat = array();
      foreach ($var as $key) {
        if (isset($this->data[$key])) {
          $dat[$key]=$this->data[$key];
        }
      }
      return $dat;
    }
    return null;
  }
  
  /**
   * Get value of xml node.
   * 
   * @param string $parent name of xml node
   * @param string $child xml child node name
   * @param string $grandchild xml grandchild node name
   * @return value of xml node
   */
  function getxml($parent, $child = '', $grandchild = '') {
    $xmldata = json_decode($this->xmldata);
    if (is_string($parent)) {
      if (isset($xmldata->$parent)) {
        if ($child == '' and $grandchild == '') {
          return $xmldata->$parent;
        } elseif ($child != '' and $grandchild == '') {
          if (isset($xmldata->$parent->$child)) {
             return $xmldata->$parent->$child;
          }
        } else {
          if (isset($xmldata->$parent->$child->$grandchild)) {
             return $xmldata->$parent->$child->$grandchild;
          }
        }
      }
    } 
    return null;
  }

  function &getbyref($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    }

    $fake = null;
    return $fake;
  }
}
