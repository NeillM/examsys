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


  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  protected $lti;

function init($object) {
    parent::init($object);
    $this->lti = UoN_LTI::get_instance();

    $this->lti->init_lti0($this->db);
    $this->savetodebug('Starting LTI');
    $this->lti->init_lti();

  }

  function register_callback_routines() {
    $callbackarray[]=array(array($this, 'auth'), 'auth', $this->number, $this->name);
    $callbackarray[]=array(array($this, 'registeruserwithlti'), 'postauthsuccess', $this->number, $this->name);
    $callbackarray[]=array(array($this, 'displaystdform'), 'displaystdform', $this->number, $this->name);

    return $callbackarray;
  }


  function auth($authobj) {
    if ($this->lti->valid !== TRUE) {
      $this->savetodebug('Not valid LTI Launch: ' . $this->lti->message);
      $authobj->fail();

      return $authobj;
    }

    $this->savetodebug('Starting to lookup user');
    $returned = $this->lti->lookup_lti_user();

    $this->savetodebug('Data returned from lti lookup was: ' . var_export($returned, TRUE));

    if ($returned !== FALSE) {
      $this->retdata->success = TRUE;
      $this->retdata->form = 'std';
      $this->rogoid = $returned[0];
      $this->retdata->rogoid = & $this->rogoid;
      $this->retdata->url = '';
      $authobj->retdata = & $this->retdata;
      $this->savetodebug('LTI lookup successful');

      $authobj->success($this->number,$this->rogoid);
      return $authobj;
    }


    //set session to be needing user lookup
    $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] = TRUE;

    // lti valid but no user id associated with it.
    // need to authenticate the user but ignore lti & already logged in etc

    return $authobj;


  }

  function registeruserwithlti($postauthsuccessobj) {
    if (!isset($_SESSION['authenticationobj']['ltilogin']['needsuserlookup']) or $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] === FALSE) {
      return;
    }
    $this->savetodebug('storing rogo userid against lti user');
    $rogoid = $this->calling_object->get_userid();
    $this->lti->add_lti_user($rogoid);
    $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] = FALSE;

    return $postauthsuccessobj;
  }

  function displaystdform(&$displaystdformobj) {
    if (isset($_SESSION['authenticationobj']['ltilogin']['needsuserlookup']) and  $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] === TRUE) {

      $message = new stdClass();
      $message->pretext = '';
      $message->posttext = '';

      $message->content = 'Please Login to authenticate the LTI Connection.'; //TODO need to convert this for language.
      $displaystdformobj->messages[] = $message;
      $displaystdformobj->replace = TRUE;
    }
    return $displaystdformobj;
  }

}
