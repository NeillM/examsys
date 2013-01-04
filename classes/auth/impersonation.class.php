<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 03/01/13
 * Time: 11:21
 * To change this template use File | Settings | File Templates.
 */
require_once 'outline_authentication.class.php';

class impersonation_auth extends outline_authentication {

  private $active = FALSE;
  private $demo = FALSE;
  private $newuserid;

  function register_callback_routines() {
    $this->calling_object->register_callback(array($this, 'checkwhattodo'), 'preauth', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'changewhoiam'), 'getauthobj', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'storedata'), 'sessionstore', $this->number, $this->name);
  }

  function changewhoiam(&$getauthobj) {
    if(!is_null($_SESSION['authenticationObj']['impersination']['newuserid'])) {
if(!$getauthobj->userObj->has_role('SysAdmin')) {
  $this->savetodebug('Cannot change user as not a SysAdmin');
}
      $getauthobj->userObj->store_original_user();
      $getauthobj->userObj->load($_SESSION['authenticationObj']['impersination']['newuserid']);
    }
    if ($_SESSION['authenticationObj']['impersination']['demo'] === TRUE) {
      $this->savetodebug('Changing user status to DEMO');
      $getauthobj->userObj->set_demo();
    }


  }

  function storedata(&$sessionstoreobj) {
    $this->savetodebug('session store');
    $_SESSION['authenticationObj']['impersination']['newuserid'] = $this->newuserid;
    $_SESSION['authenticationObj']['impersination']['demo'] = $this->demo;
  }

  function checkwhattodo(&$preauthobj) {
    $this->savetodebug('Starting up impersination checking');
    $this->savetodebug('Check sess var:' . var_export($_SESSION, TRUE));

    $continue = FALSE;
    if (isset($this->form['std']->username)) {
      if (strpos($this->form['std']->username, $this->settings['separator']) !== FALSE) {
        $usernameparts = explode($this->settings['separator'], $this->form['std']->username);
        if (isset($usernameparts[1])) {
          $continue = TRUE;

          $this->retdata->debug[] = 'found separator char';
        }
      }
    }

    if ($continue !== TRUE) {
      if (isset($_SESSION['authenticationObj']['impersination']['newuserid']) or isset($_SESSION['authenticationObj']['impersination']['demo'])) {
        $this->savetodebug('Found store data in session for impersination');
        $this->newuserid = $_SESSION['authenticationObj']['impersination']['newuserid'];
        $this->demo = $_SESSION['authenticationObj']['impersination']['demo'];
      }

      return;
    }

    if ((strcasecmp($usernameparts[1], 'demo') == 0 ) or (isset($usernameparts[2]) and strcasecmp($usernameparts[2], 'demo') == 0 ) )  {
      $this->demo = TRUE;
      $this->retdata->debug[] = 'Demo mode detected';
      $this->active = TRUE;
      $this->form['std']->username = $usernameparts[0];

      return;
    }

    $lookupuserobj = new stdClass();
    $lookupuserobj->username = $usernameparts[1];
    $lookupuserobj->found = FALSE;
    list($callbacklist, $callbackregisterdatalist) = $this->calling_object->get_callback('lookupuser'); //  if (isset($this->calling_object->callbackregister['lookupuser'])) {

    if (is_array(($callbacklist))) {
      //foreach ($this->calling_object->callbackregister['lookupuser'] as $number => $callback) {
      foreach ($callbacklist as $number => $callback) {

        call_user_func_array($callback, array($lookupuserobj));
        $objid = key($callbackregisterdatalist[$number]);
        $new_messages = $this->returndata[$objid]->get_new_debug_messages();
        foreach ($new_messages as $key => $value) {
          $info1 = $this->calling_object->authinfo[$objid];
          $info = key($info1) . ':' . current($info1);
          $this->savetodebug("Lookup User:authObj($info)[$number:$key]: $value");
        }
      }
    }
    if ($lookupuserobj->found === TRUE) {
      $this->active = TRUE;
      //assuming first lookup is the one we want
      $this->newuserid = $lookupuserobj->results[0]->userid;
    }


    if ($this->active === TRUE) {
      $this->form['std']->username = $usernameparts[0];
    }

    return;
  }

}
