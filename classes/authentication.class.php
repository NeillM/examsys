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
 * Authentication routine which permits staff and student access to a page.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class Authentication {

  private $userid;
  private $password;
  private $db, $configObj;
  private $config;
  public $returndata;
  public $debug;
  public $success;
  public $form;

  public $successfullauthmodule;

  private $callbackregister;
  private $callbackregisterdata;

  public $authinfo;

  public $session;
  public $request;

  public $impliments_api_auth_version = 1;

  public $callbacktypes= array('init', 'lookupuser', 'preauth', 'auth', 'postauth', 'postauthsuccess', 'postauthfail', 'displaystdform', 'displayerrform', 'getauthobj', 'sessionstore');

  function __construct(&$configObj, & $db, &$request, &$session) {
    $this->db = & $db;
    $this->configObj = & $configObj;

    $this->request = & $request;
    $this->session = & $session;

    $this->load_config();

    $notfound = TRUE;
    foreach ($this->config as $opt) {
      if ($opt[0] === 'alreadyloggedin') {
        $notfound = FALSE;
      }
    }

    if ($notfound === TRUE) {
      array_unshift($this->config, array('alreadyloggedin', array('timeout' => 0), 'Internal Authentication'));
    }

    // get form data here?
    $this->form['std'] = new stdClass();
    if (isset($_REQUEST['rogo-login-form-std'])) {

      $this->form['std']->username = $_REQUEST['ROGO_USER'];
      $this->form['std']->password = $_REQUEST['ROGO_PW'];
      $this->debug[] = 'Standard form data found - Storing in object ' . var_export($this->form, TRUE);

    }
    /*
        //make sure session is started
        $this->debug[] = 'DEBUG1:' . session_id();
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
    */
    if (!isset($_SESSION['authenticationObj']['attempt'])) {
      $_SESSION['authenticationObj']['attempt'] = 0;
      $this->debug[] = 'Creating SESSION attempt data';
    }


    foreach ($this->config as $number => $auth) {


      $authtype = $auth[0];
      $authtype1 = $authtype . '_auth';
      $settings = $auth[1];
      $name = $auth[2];
      $this->debug[] = "Loading auth #$number with Type:$authtype Settings:" . str_replace("\n", "\n", var_export($settings, TRUE));
      $this->returndata[$number] = new authtypereturn();
      $this->authinfo[$number] = array($name => $authtype);

      $object = new stdClass();
      $object->db =& $this->db;
      $object->calling_object =& $this;
      $object->returndata =& $this->returndata;
      $object->form =& $this->form;
      $object->settings = $settings;

      if (!isset($settings['mockclass'])) {
        require_once $this->configObj->get('cfg_web_root') . 'plugins/auth/' . $authtype . '.class.php';
        $this->authObj[$number] = new $authtype1($number, $name, $this->impliments_api_auth_version);
      } else {
        $this->authObj[$number] = & $settings['mockclass'];
        $this->authObj->mock($number, $name, $this->impliments_api_auth_version, $object);
      }
//      $this->append_auth_object_debug($number);
      $error = $this->authObj[$number]->apicheck();

      //   $this->append_auth_object_debug($number);
      if ($error !== FALSE) {
        $this->debug[] = '********* Unloading module #' . $number . ':' . $name . ' as it implements an old a version of the api.  The returned error #: ' . $error . ' *********';
 //       unset($this->authObj[$number]);
      } else {
        $this->authObj[$number]->init($object);

        $this->debug[] = "Running Registering callback routines for #$number";

        $callbacks = $this->authObj[$number]->register_callback_routines();
        foreach ($callbacks as $callbackitem) {
          $this->register_callback($callbackitem[0], $callbackitem[1], $callbackitem[2], $callbackitem[3], $callbackitem[4]);
        }
        $this->append_auth_object_debug($number);
      }
    }
    $initobj = new stdClass();
    if (isset($this->callbackregister['init'])) {
      foreach ($this->callbackregister['init'] as $number => $callback) {
        call_user_func_array($callback, array(&$initobj));
        $objid = key($this->callbackregisterdata['init'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }


  }

  function load_config() {
    $notice = UserNotices::get_instance();

    $this->config = $this->configObj->getbyref('authentication');

    if (!isset($this->config)) {
      $notice->display_notice('No Authentication configured', 'No Authentication configured has been set in the config file. Please contact your local system administrator.', '../artwork/software_64.png', $title_color = '#C00000');
      exit();
    }

    $this->debug[] = 'Loaded Config for authentication';
  }

  function register_callback($callback, $section, $number, $name, $insert = FALSE) {
    if (!in_array($section, $this->callbacktypes) or !is_callable($callback)) {
      //attempting to register callback to invalid section
      //maybe log name of function as well?
      $this->debug[] = 'register_callback FAILED ' . $section . ' from ' . get_class($callback[0]) . ' id:' . $number . ' with name:' . $name; // . var_export($callback,TRUE);
      $this->authObj[$number]->set_error("Failed to register callback for section ($section) with function ($callback[1])");
      return FALSE;
    }
    $this->debug[] = 'register_callback success ' . $section . ' from ' . get_class($callback[0]) . ' id:' . $number . ' with name:' . $name; // . var_export($callback,TRUE);
    if ($insert == TRUE) {
      array_unshift($this->callbackregister[$section], $callback);
      array_unshift($this->callbackregisterdata[$section], array($number => $name));
    } else {
      $this->callbackregister[$section][] = $callback;
      $this->callbackregisterdata[$section][] = array($number => $name);

    }

    return TRUE;
  }

  function get_callback($section) {
    return array(&$this->callbackregister[$section], &$this->callbackregisterdata[$section]);
  }


  function display_std_form() {

    $displaystdformobj = new stdClass();
    if (isset($this->callbackregister['displaystdform'])) {
      foreach ($this->callbackregister['displaystdform'] as $number => $callback) {
        call_user_func_array($callback, array(&$displaystdformobj));
        $objid = key($this->callbackregisterdata['displaystdform'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    $override = $this->configObj->get('cfg_web_root') . '/config/login_form.php';
    $this->debug[] = 'Display form';
    if (file_exists($override)) {
      require $override;
    } else {
      require $this->configObj->get('cfg_web_root') . '/include/login_form.php';
    }
  }

  function display_error_form() {
    $override = $this->configObj->get('cfg_web_root') . '/config/login_error_form.php';
    $displayerrformobj = new stdClass();
    $displayerrformobj->override =& $override;

    if (isset($this->callbackregister['displayerrform'])) {
      foreach ($this->callbackregister['displayerrform'] as $number => $callback) {
        call_user_func_array($callback, array(&$displayerrformobj));
        $objid = key($this->callbackregisterdata['displayerrform'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    $this->debug[] = 'Display error form & reset attempt count';
    $_SESSION['authenticationObj']['attempt'] = 0;
    if (file_exists($override)) {
      require $override;
    } else {
      require $this->configObj->get('cfg_web_root') . '/include/login_error_form.php';
    }
  }

  /**
   * @return bool if authentication was successful
   */
  function do_authentication() {
    $this->success = FALSE;
    $this->debug[] = 'Starting authentication';

    $preauthobj = new stdClass();
    if (isset($this->callbackregister['preauth'])) {
      foreach ($this->callbackregister['preauth'] as $number => $callback) {
        call_user_func_array($callback, array(&$preauthobj));
        $objid = key($this->callbackregisterdata['preauth'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    $authobj = new stdClass();
    if (isset($this->callbackregister['auth'])) {
      foreach ($this->callbackregister['auth'] as $number => $callback) {
        $returned = call_user_func_array($callback, array(&$authobj));
        $objid = key($this->callbackregisterdata['auth'][$number]);
        $this->append_auth_object_debug($objid);
        if ($returned !== FALSE) {
          $this->success = TRUE;
          $this->userid = $this->authObj[$objid]->rogoid;
          $this->debug[] = '******* Rogo ID is:: ' . $this->userid . " from object $objid:" . $this->callbackregisterdata['auth'][$number][$objid] . ' *******';
          //         $this->debug[]=var_dump($this->authObj[$objid],TRUE);
          $this->successfullauthmodule[] = $objid;

        }


        if (($this->success and (($this->authObj[$objid]->get_settings('dont_break_on_success') === FALSE) or (($this->authObj[$objid]->get_settings('dont_break_on_success') !== FALSE) and !$this->authObj[$objid]->get_settings('dont_break_on_success'))))) {
          break;
        }
      }
    }

    $postauthobj = new stdClass();
    if (isset($this->callbackregister['postauth'])) {
      foreach ($this->callbackregister['postauth'] as $number => $callback) {
        call_user_func_array($callback, array($postauthobj));
        $objid = key($this->callbackregisterdata['postauth'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    if ($this->success === FALSE) {
      //failed

      $postauthfailobj = new postauthfailreturn();
      $_SESSION['authenticationObj']['attempt']++;
      if (isset($this->callbackregister['postauthfail'])) {
        foreach ($this->callbackregister['postauthfail'] as $number => $callback) {
          call_user_func_array($callback, array(&$postauthfailobj));
          $objid = key($this->callbackregisterdata['postauthfail'][$number]);
          $this->append_auth_object_debug($objid);
          $this->debug[] = 'parameters after running ' . var_export($postauthfailobj, TRUE);
          if (isset($postauthfailobj->callback)) {
            call_user_func_array($postauthfailobj->callback, $postauthfailobj);
            if ($postauthfailobj->exit === TRUE) {
              var_dump($this->debug);
              exit();
            }
          }

          if ($postauthfailobj->form == 'err') {

            $this->display_error_form();

            if ($postauthfailobj->exit === TRUE) {
              var_dump($this->debug);
              exit();
            }
          }

          if ($postauthfailobj->form == 'std') {

            $this->display_std_form();

            if ($postauthfailobj->exit === TRUE) {
              //  var_dump($this->debug);
              exit();
            }
          }

          if (isset($postauthfailobj->url)) {
            header("Location: {$postauthfailobj->url}");
            if ($postauthfailobj->exit === TRUE) {
              // var_dump($this->debug);
              exit();
            }
          }


          if ($postauthfailobj->stop === TRUE) {
            break;
          }
        }
      }
      //failed but no callbacks or callbacks finished
      $notice = UserNotices::get_instance();
      $notice->display_notice('Authentication Issue', 'The authentication plugins couldnt log you in and, they the plugins didnt provide any further form or redirect.   Press F5 to refresh if this is still unsuccessful please contact support:  <a href="mailto:' . $this->configObj->get('support_email') . '">' . $this->configObj->get('support_email') . '</a>.', '/artwork/user_info_48.png', $title_color = '#C00000', TRUE, FALSE);
      echo <<<HTML
<p>Please Include the following debug in your email:</p>
<div style="margin-left:100px;  ">
HTML;

      $this->display_debug();
      echo <<<HTML
</div>
HTML;

      exit();
    }

    if ($this->success !== TRUE) {
      $this->debug[] = 'Success is not TRUE or FALSE';

      //something went very wrong;
      return FALSE;

    }
    // the auth has succeeded as above will stop it if its not true

    $postauthsuccessobj = new stdClass();

    $postauthsuccessobj->userid =& $this->userid;

    if (isset($this->callbackregister['postauthsuccess'])) {
      foreach ($this->callbackregister['postauthsuccess'] as $number => $callback) {
        $this->debug[] = 'run authsuccess callback ' . get_class($callback[0]) . ':' . $callback[1];
        call_user_func_array($callback, array(&$postauthsuccessobj));
        $objid = key($this->callbackregisterdata['postauthsuccess'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }


    // need to save some data for allready logged in authentication
    $this->store_data_in_session();

    /*
        // old bitz
          $returned = $this->authObj[$number]->auth();

          foreach ($this->returndata[$number]->debug as $value) {
            $this->debug[] = "authObj[$number]::" . $value;

          }

          if ($returned !== FALSE) {
            $this->success = TRUE;
          }
          $this->debug[]='loop debug: ' . var_export(!isset($settings['dont_break_on_success']),TRUE) . ' ' . var_export($settings['dont_break_on_success'],true) .' ' . var_export($returned,TRUE);

          if ((!isset($settings['dont_break_on_success']) and $settings['dont_break_on_success'] !== TRUE ) and $returned !== FALSE) {
    $this->debug[]='Breaking out of loop ' . var_export(isset($settings['dont_break_on_success']),TRUE) . ' ' . var_export($settings['dont_break_on_sucess'],true) .' ' . var_export($returned,TRUE);
            $this->debug[]=var_export($settings,TRUE);
            $this->debug[]=var_export($auth,TRUE);
            break;
          }
        }
        $this->debug[]='end do auth loop';
        var_dump($this->returndata);
        print "done dump";*/

  }

  function store_data_in_session() {
    /*   if (!isset($_SESSION['authenticationObj']['loggedin']['password'])) {
         $_SESSION['authenticationObj']['loggedin']['password'] = $this->get_password();
       }*/
    $_SESSION['authenticationObj']['loggedin']['userid'] = $this->get_userid();
    $_SESSION['authenticationObj']['loggedin']['time'] = time();
    $_SESSION['authenticationObj']['attempt'] = 0;
  }


  function get_userid() {
    return $this->userid;
  }

  function get_password() {
    return $this->form['std']->password;
  }

  function append_auth_object_debug($number, $desc = '') {
    $new_messages = $this->returndata[$number]->get_new_debug_messages();
    foreach ($new_messages as $key => $value) {
      $info1 = $this->authinfo[$number];
      $info = key($info1) . ':' . current($info1);
      $this->debug[] = "authObj($info)[$number:$key]:$desc: $value";
    }
  }

  function display_debug() {
    var_dump($this->debug);
  }

  function get_auth_obj(&$getauth) {
    if (!is_object($getauth)) {
      $getauthobj->userid = $getauth;
      $getauthobj->userObj = new UserObject($this->configObj, $this->db);
      $getauthobj->userObj->load($getauth);
    } else {
      $getauthobj = & $getauth;
      if (!isset($getauthobj->userObj)) {
        //serious error
        $getauthobj->userObj = new UserObject($this->configObj, $this->db);
      }

      if ($this->get_userid() < 1) {
        $notice = UserNotices::get_instance();
        $notice->display_notice('Authentication Issue', 'You are not logged in.   Press F5 to refresh if this is still unsuccessful please contact support:  <a href="mailto:' . $this->configObj->get('support_email') . '">' . $this->configObj->get('support_email') . '</a>.', '/artwork/user_info_48.png', $title_color = '#C00000', TRUE, FALSE);
        echo <<<HTML
<p>Please Include the following debug in your email:</p>
<div style="margin-left:100px;  ">
HTML;

        $this->display_debug();
        echo <<<HTML
</div>
HTML;

        exit();
      }
      $getauthobj->userObj->load($this->get_userid());
    }
    //$uID = $this->get_userID();


    if (isset($this->callbackregister['getauthobj'])) {
      foreach ($this->callbackregister['getauthobj'] as $number => $callback) {
        $this->debug[] = 'run getauthobj callback ' . get_class($callback[0]) . ':' . $callback[1];
        call_user_func_array($callback, array(&$getauthobj));
        $objid = key($this->callbackregisterdata['getauthobj'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    return $getauthobj->userObj;
  }


  function version_info($formatted = FALSE, $advanced = FALSE) {
    $data=new stdClass();
    $data->plugins = array();
    foreach ($this->authObj as $authobj) {
      $data->plugins[] = $authobj->get_info();
    }
$data->callbacks=array();
    foreach($this->callbacktypes as $value) {

      if(isset($this->callbackregister[$value])){
        foreach($this->callbackregister[$value] as $order=>$callitem) {
          $dat=new stdClass();
        $dat->functionname=$callitem[1];
          $callbackdat=$this->callbackregisterdata[$value][$order];
        $dat->plugindescname=current($callbackdat);
          $dat->pluginconfigid=key($callbackdat);
$data->callbacks[$value][]=$dat;
        }

      } else {
        $data->callbacks[$value]=array();
      }

    }


    if ($formatted == FALSE) {
      return $data;
    }
    if ($advanced == FALSE) {
      //basic view

      $return_data = '';
      $error=false;
      foreach ($data->plugins as $number => $item) {
        if(count($item->error) >0) {
          $error=true;
        }
        if ($number != 0) {
          $return_data .= ',  <b>' . $number . '</b> ' . $item->name . ' <i>(' . $item->classname . ')</i>';
        }

      }
      $return_data = substr($return_data, 3);
      if($error) {
        $return_data = '<div style="background-color: #cc0000;">' . $return_data . '</div>';
      }

    } else {
      //advanced view


    }

    return $return_data;
  }


}

class authtypereturn {
  public $debug, $debugpointer, $success, $rogoid, $url, $message;

  function __construct() {
    $this->debug = array();
    $this->debugpointer = 0;
    $this->success = FALSE;
    $this->rogoid = 0;
    $this->url = '';
    $this->message = '';
  }

  function get_new_debug_messages() {
    $returnarray = array();
    while (isset($this->debug[$this->debugpointer])) {
      $returnarray[$this->debugpointer] = $this->debug[$this->debugpointer++];
    }

    return $returnarray;
  }
}

class postauthfailreturn extends stdClass {
  public $attempt;
  public $form;
  public $url;
  public $callback;
  public $stop;
  public $exit;

  function __construct() {
    $this->attempt = $_SESSION['authenticationObj']['attempt'];
    $this->stop = FALSE;
    $this->exit = FALSE;
  }
}

class displaystdformobjbutton extends stdClass {
  public $pretext;
  public $posttext;
  public $type;
  public $name;
  public $value;
  public $style;

  function __construct() {
    $this->pretext = '';
    $this->posttext = '';
    $this->type = '';
    $this->name = '';
    $this->value = '';
    $this->style = '';
  }

}