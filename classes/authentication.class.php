<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 27/11/12
 * Time: 10:34
 * To change this template use File | Settings | File Templates.
 */
class Authentication {

  private $userid;
  private $db, $configObj;
  private $config;
  public $returndata;
  public $debug;
  public $success;

  private $callbackregister;
  private $callbackregisterdata;

  function __construct($configObj, $db) {
    $this->db = $db;
    $this->configObj = $configObj;

    $this->load_config();

  }

  function load_config() {

    $this->config = $this->configObj->get('authentication');

    $this->debug[] = 'Loaded Config for authentication';
  }

  function register_callback($callback, $section, $number, $name, $insert = false) {
    if (!in_array($section, array('preauth', 'auth', 'postauth', 'postauthsucess', 'postauthfail'))) {
      //attempting to register callback to invalid section
      $this->debug[] = 'register_callback failed ' . $section . ' '; // . var_export($callback,TRUE);
      return false;
    }
    $this->debug[] = 'register_callback success ' . $section . ' '; // . var_export($callback,TRUE);
    if ($insert == true) {
      array_unshift($this->callbackregister[$section], $callback);
      array_unshift($this->callbackregisterdata[$section], array($number => $name));
    } else {
      $this->callbackregister[$section][] = $callback;
      $this->callbackregisterdata[$section][] = array($number => $name);
    }
    return true;
  }

  function do_authentication() {
    $this->success = FALSE;
    $this->debug[] = 'Starting authentication';
    $form = array();
    if (isset($_REQUEST['rogo-login-form-std'])) {
      $form['std'] = new stdClass();
      $form['std']->username = $_REQUEST['ROGO_USER'];
      $form['std']->password = $_REQUEST['ROGO_PW'];
      $this->debug[] = 'Standard form data found - Storing in object';

    }

    //make sure session is started
    if (session_id() == '') {
      session_name('RogoAuthentication');
      $return = session_start();
      if ($return === FALSE) {
        $this->debug[]='session failed to initialise';
        return;
        //session start failure
      }
      if(!isset($_SESSION['authenticationObj']['attempt'])) {
        $_SESSION['authenticationObj']['attempt']=0;
      }
    }

    foreach ($this->config as $number => $auth) {
      $authtype = $auth[0];
      $settings = $auth[1];
      $this->debug[] = "Starting auth #$number with Type:$authtype Settings:" . var_export($settings, TRUE);
      require_once $this->configObj->get('cfg_web_root') . 'classes/auth/' . $authtype . '.class.php';


      $this->returndata[$number] = new authtypereturn();
      /*      $this->returndata[$number]->success = FALSE;
            $this->returndata[$number]->rogoid = 0;
            $this->returndata[$number]->url = '';
            $this->returndata[$number]->message = '';*/
      $this->authObj[$number] = new $authtype($this, $settings, $this->db, $this->returndata, $number, $form);

      $this->debug[] = 'Running Registering callback routines';
      $this->authObj[$number]->register_callback_routines();

    }

    $preauthobj = new stdClass();
    if (isset($this->callbackregister['preauth'])) {
      foreach ($this->callbackregister['preauth'] as $number => $callback) {
        call_user_func_array($callback, array($preauthobj));
        foreach ($this->returndata[$number]->debug as $value) {
          $this->debug[] = "authObj[$number]::" . $value;

        }
      }
    }

    $authobj = new stdClass();
    if (isset($this->callbackregister['auth'])) {
      foreach ($this->callbackregister['auth'] as $number => $callback) {
        $returned = call_user_func_array($callback, array($authobj));
        if ($returned !== FALSE) {
          $this->success = TRUE;
        }
        foreach ($this->returndata[$number]->debug as $value) {
          $this->debug[] = "authObj[$number]::" . $value;

        }
        if (($this->success and (!isset($settings['dont_break_on_success']) or (isset($settings['dont_break_on_success']) and !$settings['dont_break_on_success'])))) {
          break;
        }
      }
    }

    $postauthobj = new stdClass();
    if (isset($this->callbackregister['postauth'])) {
      foreach ($this->callbackregister['postauth'] as $number => $callback) {
        call_user_func_array($callback, array($postauthobj));
        foreach ($this->returndata[$number]->debug as $value) {
          $this->debug[] = "authObj[$number]::" . $value;

        }
      }
    }

    if($this->success === FALSE) {
      //failed
      $_SESSION['authenticationObj']['attempt']++;
      $postauthfailobj = new stdClass();
      $postauthfailobj->attempt=$_SESSION['authenticationObj']['attempt'];
      if (isset($this->callbackregister['postauthfail'])) {
        foreach ($this->callbackregister['postauthfail'] as $number => $callback) {
          call_user_func_array($callback, array($postauthfailobj));
          foreach ($this->returndata[$number]->debug as $value) {
            $this->debug[] = "authObj[$number]::" . $value;

          }
        }
      }
      //failed actions
      if($_SESSION['authenticationObj']['attempt']==1) {
        foreach ($this->config as $number => $auth) {
          $action=$this->authObj[$number]->form();
        }
      }
    }

    if($this->success !== TRUE ) {
      $this->debug[]='Success is not TRUE or FALSE';
      //something went very wrong;
      return false;

    }

    $postauthsuccessobj = new stdClass();
    if (isset($this->callbackregister['postauthsuccess'])) {
      foreach ($this->callbackregister['postauthsuccess'] as $number => $callback) {
        call_user_func_array($callback, array($postauthsuccessobj));
        foreach ($this->returndata[$number]->debug as $value) {
          $this->debug[] = "authObj[$number]::" . $value;

        }
      }
    }

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

  function get_userid() {
    return $this->userid;
  }


}

class authtypereturn {
  public $debug, $success, $rogoid, $url, $message;

  function __construct() {
    $this->debug = array();
    $this->success = FALSE;
    $this->rogoid = 0;
    $this->url = '';
    $this->message = '';
  }
}
