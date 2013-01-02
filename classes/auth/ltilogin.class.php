<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 20/12/12
 * Time: 14:51
 * To change this template use File | Settings | File Templates.
 */
/**
 *
 * The lti login authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $configObj->get('cfg_web_root') . 'LTI/ims-lti/UoN_LTI.php';


class ltilogin_auth {

  private $name;
  private $number;
  private $returndata;
  private $retdata;
  private $form;
  private $settings;
  private $db;
  private $calling_object;
  private $updatable = FALSE;
  public $rogoid = FALSE;

  private $lti;

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


    $this->lti = new UoN_LTI($this->db);

    $this->retdata->debug[] = 'Starting LTI';
    $this->lti->init_lti();

  }

  function register_callback_routines() {
    $this->calling_object->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
//    $this->calling_object->register_callback(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
//    $this->calling_object->register_callback(array($this, 'update_password'), 'postauthsuccess', $this->number, $this->name);
  }

  function set_fail() {
    $this->retdata->success = FALSE;
    $this->retdata->form = 'std';
    $this->retdata->rogoid = 0;
    $this->retdata->url = '';
  }

  function auth($authobj) {

    if (!$this->lti->valid) {
      $this->retdata->debug[] = 'Not valid LTI Launch';
      $this->set_fail();

      return FALSE;
    }

    // code similar to staff_student auth line 105



  }


}
