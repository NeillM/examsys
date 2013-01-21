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
 */

require_once 'outline_authentication.class.php';

class impersonation_auth extends outline_authentication {

  private $active = FALSE;
  private $demo = FALSE;
  private $newuserid;
  private $lookupuserobj;

  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $this->register_callback(array($this, 'checkwhattodo'), 'preauth', $this->number, $this->name);
    $this->register_callback(array($this, 'changewhoiam'), 'getauthobj', $this->number, $this->name);
    $this->register_callback(array($this, 'storedata'), 'sessionstore', $this->number, $this->name);

    return $this->callbackarray;
  }

  function changewhoiam(&$getauthobj) {
    if (isset($this->session['authenticationObj']['impersonation']['newuserid']) and !is_null($this->session['authenticationObj']['impersonation']['newuserid'])) {
      if (!$getauthobj->userObj->has_role('SysAdmin')) {
        $this->savetodebug('Cannot change user as not a SysAdmin');
      }
      $getauthobj->userObj->impersonate($this->session['authenticationObj']['impersonation']['newuserid']);
    }
    if (isset($this->session['authenticationObj']['impersonation']['demo']) and $this->session['authenticationObj']['impersonation']['demo'] === TRUE) {
      $this->savetodebug('Changing user status to DEMO');
      $getauthobj->userObj->set_demo();
    }


  }

  function storedata(&$sessionstoreobj) {
    $this->savetodebug('session store');
    $this->session['authenticationObj']['impersonation']['newuserid'] = $this->newuserid;
    $this->session['authenticationObj']['impersonation']['demo'] = $this->demo;
  }

  function checkwhattodo(&$preauthobj) {
    $this->savetodebug('Starting up impersination checking');
//    $this->savetodebug('Check sess var:' . var_export($this->session, TRUE));

    $continue = FALSE;
    if (isset($this->form['std']->username)) {
      if (strpos($this->form['std']->username, $this->settings['separator']) !== FALSE) {
        $usernameparts = explode($this->settings['separator'], $this->form['std']->username);
        if (isset($usernameparts[1])) {
          $continue = TRUE;

          $this->savetodebug('found separator char');
        }
      }
    }

    if ($continue !== TRUE) {
      if (isset($this->session['authenticationObj']['impersonation']['newuserid']) or isset($this->session['authenticationObj']['impersonation']['demo'])) {
        $this->savetodebug('Found store data in session for impersonation');
        $this->newuserid = $this->session['authenticationObj']['impersonation']['newuserid'];
        $this->demo = $this->session['authenticationObj']['impersonation']['demo'];
      }

      return;
    }

    if ((strcasecmp($usernameparts[1], 'demo') == 0) or (isset($usernameparts[2]) and strcasecmp($usernameparts[2], 'demo') == 0)) {
      $this->demo = TRUE;
      $this->savetodebug('Demo mode detected');
      $this->active = TRUE;
      $this->form['std']->username = $usernameparts[0];

      return;
    }
if(!isset($this->lookupuserobj)) {
    $this->lookupuserobj = new stdClass();
    $this->lookupuserobj->username = $usernameparts[1];
    $this->lookupuserobj->found = FALSE;
}
    list($callbacklist, $callbackregisterdatalist) = $this->get_callback('lookupuser'); //  if (isset($this->calling_object->callbackregister['lookupuser'])) {

    if (is_array(($callbacklist))) {
      //foreach ($this->calling_object->callbackregister['lookupuser'] as $number => $callback) {
      foreach ($callbacklist as $number => $callback) {

        call_user_func_array($callback, array($this->lookupuserobj));
        $objid = key($callbackregisterdatalist[$number]);
        $new_messages = $this->get_module_debug($objid);
        foreach ($new_messages as $key => $value) {
          $info1 = $this->get_module_authinfo($objid);
          $info = key($info1) . ':' . current($info1);
          $this->savetodebug("Lookup User:authObj($info)[$number:$key]: $value");
        }
      }
    }

    if ($this->lookupuserobj->found === TRUE) {
      $this->active = TRUE;
      //assuming first lookup is the one we want
      $this->newuserid = $this->lookupuserobj->results[0]->userid;
    }

    if ($this->active === TRUE) {
      $this->form['std']->username = $usernameparts[0];
    }

    return;
  }

}
