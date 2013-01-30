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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package Rogō
 */
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('xdebug.remote_autostart', 1);
ini_set("display_errors", 1);
ini_set('xdebug.var_display_max_childrren', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

require_once __DIR__ . '/classes/configobject.class.php';
$configObj=Config::get_instance();
$configObject=$configObj;
$cfg_web_root=$configObj->get('cfg_web_root');

require_once __DIR__ . '/classes/lookup.class.php';

require_once $cfg_web_root . 'classes/lang.class.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/common.inc'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';
require_once $cfg_web_root . 'classes/dbutils.class.php';

require_once $cfg_web_root . 'classes/usernotices.class.php';

require_once $cfg_web_root . 'classes/userobject.class.php';

$notice=UserNotices::get_instance();

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));



$userlookupobject= Lookup::get_instance($configObj,$mysqli);


$lookupdata=new stdClass();
$lookupdata->username='cczsa1';
//$lookupdata->studentID=2234;
//$lookupdata->staffID=99802134;
//$lookupdata->email='sygygfu@nottingham.ac.uk';
//$lookupdata->surname='bhnjbh';
//$lookupdata->firstname='xfgh';

$data=new stdClass();
$data->lookupdata=$lookupdata;
$info=$userlookupobject->userlookup($data);

var_dump($info);

$userlookupobject->display_debug();

