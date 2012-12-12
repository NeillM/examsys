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
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */
class alreadyloggedin {
  private $name;
  private $number;
  private $returndata;
  private $retdata;
  private $form;
  private $settings;
  private $db;
  private $calling_object;
  private $updatable = FALSE;
  public $rogoid;

  function __construct($calling_object, $settings, $number, $name, $db, &$returndata, $form) {
    $this->db = new mysqli();
    $this->db = $db;
    $this->calling_object = $calling_object;
    $this->returndata = $returndata;
    $this->number = $number;
    $this->retdata = $returndata[$number];
    $this->form = $form;
    $this->settings = $settings;
    $this->name = $name;
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

    $this->calling_object->register_callback(array($this, 'update_time'), 'postauthsuccess', $this->number, $this->name);


  }

  function auth($authobj) {
    $this->retdata->debug[] = 'Authing';
    $this->retdata->debug[] = str_replace("\n", '', trim(rtrim(var_export($_SESSION, TRUE))));
    if (isset($_SESSION['authenticationObj']['loggedin']['userid']) and $_SESSION['authenticationObj']['loggedin']['userid'] > 0 and $_SESSION['authenticationObj']['loggedin']['userid'] != '' and $_SESSION['authenticationObj']['loggedin']['userid'] != 'null' and is_int($_SESSION['authenticationObj']['loggedin']['userid'])) {
      $this->retdata->debug[] = 'userid found in session';
      if (isset($this->settings['timeout']) and $this->settings['timeout'] != 0 and (($_SESSION['authenticationObj']['loggedin']['time'] + $this->settings['timeout']) > time())) {
        $this->retdata->debug[] = 'Timeout is set and run out';
        $this->retdata->success = FALSE;
        $this->retdata->rogoid = 0;

        return FALSE;
      } else {
        $this->retdata->debug[] = 'Successfully authenticated';
        $this->retdata->success = TRUE;
        $this->retdata->rogoid = $_SESSION['authenticationObj']['loggedin']['userid'];
        $this->rogoid = $_SESSION['authenticationObj']['loggedin']['userid'];

        $authobj->rogoid =& $this->retdata->rogoid;

        return TRUE;
      }

    }

    return FALSE;

  }

  function update_time($postauthsuccessobj = '') {
    $this->retdata->debugp[] = 'Updated stored time in session';
    $_SESSION['authenticationObj']['loggedin']['time'] = time();
  }

}
