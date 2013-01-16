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
 * The already logged in authentication class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
require_once 'outline_authentication.class.php';

class alreadyloggedin_auth extends outline_authentication {

  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $this->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
    $this->register_callback(array($this, 'store_user'), 'sessionstore', $this->number, $this->name);
    $this->register_callback(array($this, 'update_time'), 'postauthsuccess', $this->number, $this->name);

    return $this->callbackarray;
  }

  function auth($authobj) {
    $this->savetodebug('Authing');
    $this->savetodebug(str_replace("\n", '', trim(rtrim(var_export($_SESSION, TRUE)))));
    if (isset($_SESSION['authenticationObj']['loggedin']['userid']) and $_SESSION['authenticationObj']['loggedin']['userid'] > 0 and $_SESSION['authenticationObj']['loggedin']['userid'] != '' and $_SESSION['authenticationObj']['loggedin']['userid'] != 'null' and is_int($_SESSION['authenticationObj']['loggedin']['userid'])) {
      $this->savetodebug('userid found in session');
      if (isset($this->settings['timeout']) and $this->settings['timeout'] != 0 and (($_SESSION['authenticationObj']['loggedin']['time'] + $this->settings['timeout']) > time())) {
        $this->savetodebug('Timeout is set and run out');
        $this->retdata->success = FALSE;
        $this->retdata->rogoid = 0;

        return FALSE;
      } else {
        $this->savetodebug('Successfully authenticated');
        $this->retdata->success = TRUE;
        $this->retdata->rogoid = $_SESSION['authenticationObj']['loggedin']['userid'];
        $this->rogoid = $_SESSION['authenticationObj']['loggedin']['userid'];

        $authobj->rogoid = & $this->retdata->rogoid;

        return TRUE;
      }

    }

    $this->savetodebug('No valid userid found in session');
    $this->retdata->success = FALSE;
    $this->retdata->rogoid = 0;

    return FALSE;

  }

  function store_user(&$sessionstoreobj) {
    $this->savetodebug('session store');
    $_SESSION['authenticationObj']['loggedin']['userid'] = $this->calling_object->get_userid();
    $_SESSION['authenticationObj']['loggedin']['time'] = time();
    $_SESSION['authenticationObj']['attempt'] = 0;
  }

  function update_time($postauthsuccessobj = '') {
    $this->savetodebug('Updated stored time in session');
    $_SESSION['authenticationObj']['loggedin']['time'] = time();

    $lookupuserobj = new stdClass();
    list($callbacklist, $callbackregisterdatalist) = $this->get_callback('sessionstore'); //  run this when needing to store auth data to session

    if (is_array(($callbacklist))) {
      //foreach ($this->calling_object->callbackregister['lookupuser'] as $number => $callback) {
      foreach ($callbacklist as $number => $callback) {

        call_user_func_array($callback, array(&$lookupuserobj));
        $objid = key($callbackregisterdatalist[$number]);
        $new_messages = $this->get_module_debug($objid);
        foreach ($new_messages as $key => $value) {
          $info1 = $this->get_module_authinfo($objid);
          $info = key($info1) . ':' . current($info1);
          $this->savetodebug("Session Store:authObj($info)[$number:$key]: $value");
        }
      }
    }
  }

}
