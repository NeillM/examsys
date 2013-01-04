<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 11/12/12
 * Time: 13:19
 * To change this template use File | Settings | File Templates.
 */
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


  function __construct($calling_object, $settings, $number, $name, $db, &$returndata, &$form) {

    parent::__construct($calling_object, $settings, $number, $name, $db, $returndata, $form);
    if (session_id() == '') {
      $this->debug[] = 'SESSION NOT FOUND';
      session_name('RogoAuthentication');
      $return = session_start();
      if ($return === FALSE) {
        $this->debug[] = 'session failed to initialise';

        return;
        //session start failure
      }
    }

  }

  function register_callback_routines() {
    $this->calling_object->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'store_user'), 'sessionstore', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'update_time'), 'postauthsuccess', $this->number, $this->name);
  }

  function auth($authobj) {
    $this->savetodebug('Authing');
    $this->savetodebug(str_replace("\n", '', trim(rtrim(var_export($_SESSION, TRUE)))));
    if (isset($_SESSION['authenticationObj']['loggedin']['userid']) and $_SESSION['authenticationObj']['loggedin']['userid'] > 0 and $_SESSION['authenticationObj']['loggedin']['userid'] != '' and $_SESSION['authenticationObj']['loggedin']['userid'] != 'null' and is_int($_SESSION['authenticationObj']['loggedin']['userid'])) {
      $this->retdata->debug[] = 'userid found in session';
      if (isset($this->settings['timeout']) and $this->settings['timeout'] != 0 and (($_SESSION['authenticationObj']['loggedin']['time'] + $this->settings['timeout']) > time())) {
        $this->retdata->debug[] = 'Timeout is set and run out';
        $this->retdata->success = FALSE;
        $this->retdata->rogoid = 0;

        return FALSE;
      } else {
        $this->savetodebug('Successfully authenticated');
        $this->retdata->success = TRUE;
        $this->retdata->rogoid = $_SESSION['authenticationObj']['loggedin']['userid'];
        $this->rogoid = $_SESSION['authenticationObj']['loggedin']['userid'];

        $authobj->rogoid = &$this->retdata->rogoid;

        return TRUE;
      }

    }

    $this->retdata->debug[] = 'No valid userid found in session';
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
    list($callbacklist, $callbackregisterdatalist) = $this->calling_object->get_callback('sessionstore'); //  run this when needing to store auth data to session

    if (is_array(($callbacklist))) {
      //foreach ($this->calling_object->callbackregister['lookupuser'] as $number => $callback) {
      foreach ($callbacklist as $number => $callback) {

        call_user_func_array($callback, array(&$lookupuserobj));
        $objid = key($callbackregisterdatalist[$number]);
        $new_messages = $this->returndata[$objid]->get_new_debug_messages();
        foreach ($new_messages as $key => $value) {
          $info1 = $this->calling_object->authinfo[$objid];
          $info = key($info1) . ':' . current($info1);
          $this->savetodebug("Session Store:authObj($info)[$number:$key]: $value");
        }
      }
    }
  }

}
