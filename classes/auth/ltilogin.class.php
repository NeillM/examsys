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
require_once 'outline_authentication.class.php';


class ltilogin_auth extends outline_authentication {


  private $lti;

  function __construct($calling_object, $settings, $number, $name, $db, &$returndata, &$form) {

    parent::__construct($calling_object, $settings, $number, $name, $db, $returndata, $form);
    if (session_id() == '') {
      $this->savetodebug('SESSION NOT FOUND');
      session_name('RogoAuthentication');
      $return = session_start();
      if ($return === FALSE) {
        $this->savetodebug('session failed to initialise');

        return;
        //session start failure
      }
    }


    $this->lti = new UoN_LTI($this->db);

    $this->savetodebug('Starting LTI');
    $this->lti->init_lti();

  }

  function register_callback_routines() {
    $this->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
//    $this->calling_object->register_callback(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
//    $this->calling_object->register_callback(array($this, 'update_password'), 'postauthsuccess', $this->number, $this->name);
  }


  function auth($authobj) {

    if (!$this->lti->valid) {
      $this->savetodebug('Not valid LTI Launch: ' .  $this->lti->message);
      $this->set_fail();

      return FALSE;
    }

    $this->savetodebug('Starting to lookup user');
    $returned = $this->lti->lookup_lti_user();

    $this->savetodebug('Data returned from lti lookup was: ' . var_export($returned, TRUE));

    if ($returned !== FALSE) {
      $this->retdata->success = TRUE;
      $this->retdata->form = 'std';
      $this->retdata->rogoid = $returned[0];
      $this->rogoid = $returned[0];
      $this->retdata->url = '';
      $authobj->retdata = $this->retdata;
      $this->savetodebug('LTI lookup successful');

      return TRUE;
    }

    var_dump($returned);

    var_dump($this);
    exit();

    // lti valid but no user id associated with it.
    // need to authenticate the user but ignore lti & already logged in etc

    if(!isset($_SESSION['authenticationObj']['ltilogin']['lookupstage'])) {
      //display message
      //      UserNotices::display_notice($string['ltifirstlogin'], $string['ltifirstlogindesc'], '/artwork/user_info_48.png', $title_color = '#C00000');
    }
  }


}
