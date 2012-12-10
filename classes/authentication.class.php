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
  public $form;

  private $callbackregister;
  private $callbackregisterdata;

  function __construct($configObj, $db) {
    $this->db = $db;
    $this->configObj = $configObj;

    $this->load_config();


    // get form data here?
    $this->form['std'] = new stdClass();
    if (isset($_REQUEST['rogo-login-form-std'])) {

      $this->form['std']->username = $_REQUEST['ROGO_USER'];
      $this->form['std']->password = $_REQUEST['ROGO_PW'];
      $this->debug[] = 'Standard form data found - Storing in object ' . var_export($this->form,TRUE);

    }
    //make sure session is started
    $this->debug[]= 'DEBUG1:' . session_id();
    if (session_id() == '') {
      $this->debug[]='SESSION NOT FOUND';
      session_name('RogoAuthentication');
      $return = session_start();
      if ($return === FALSE) {
        $this->debug[] = 'session failed to initialise';
        return;
        //session start failure
      }
    }

      if (!isset($_SESSION['authenticationObj']['attempt'])) {
        $_SESSION['authenticationObj']['attempt'] = 0;
        $this->debug[]= 'Creating SESSION attempt data';
      }


    foreach ($this->config as $number => $auth) {
      $authtype = $auth[0];
      $settings = $auth[1];
      $this->debug[] = "Loading auth #$number with Type:$authtype Settings:" . str_replace("\n", "\n", var_export($settings, TRUE));
      require_once $this->configObj->get('cfg_web_root') . 'classes/auth/' . $authtype . '.class.php';
      $this->returndata[$number] = new authtypereturn();
      $this->authObj[$number] = new $authtype($this, $settings, $this->db, $this->returndata, $number, $this->form);
      $this->append_auth_object_debug($number);
      $this->debug[] = "Running Registering callback routines for #$number";
      $this->authObj[$number]->register_callback_routines();
      $this->append_auth_object_debug($number);
    }

  }

  function load_config() {

    $this->config = $this->configObj->get('authentication');

    $this->debug[] = 'Loaded Config for authentication';
  }

  function register_callback($callback, $section, $number, $name, $insert = false) {
    if (!in_array($section, array('preauth', 'auth', 'postauth', 'postauthsucess', 'postauthfail')) or !is_callable($callback)) {
      //attempting to register callback to invalid section
      $this->debug[] = 'register_callback failed ' . $section . ' from ' . get_class($callback[0]); // . var_export($callback,TRUE);
      return false;
    }
    $this->debug[] = 'register_callback success ' . $section . ' from ' . get_class($callback[0]); // . var_export($callback,TRUE);
    if ($insert == true) {
      array_unshift($this->callbackregister[$section], $callback);
      array_unshift($this->callbackregisterdata[$section], array($number => $name));
    } else {
      $this->callbackregister[$section][] = $callback;
      $this->callbackregisterdata[$section][] = array($number => $name);
    }
    return true;
  }

  function display_form() {
    $this->debug[]= 'Display form';
    echo <<<END
<div>
<form method="POST">
<p>Username:<input type="text" size="20" name="ROGO_USER"><br /></p>
<p>Password:<input type="password" size="20" name="ROGO_PW"><br /></p>
<p><input type="submit" name="rogo-login-form-std" value="Login"><br /></p>
</form>
</div>
END;

  }

  function do_authentication() {
    $this->success = FALSE;
    $this->debug[] = 'Starting authentication';




    foreach ($this->config as $number => $auth) {
      /*      $authtype = $auth[0];
            $settings = $auth[1];
            $this->debug[] = "Starting auth #$number with Type:$authtype Settings:" . var_export($settings, TRUE);
            require_once $this->configObj->get('cfg_web_root') . 'classes/auth/' . $authtype . '.class.php';


            $this->returndata[$number] = new authtypereturn();
            /*      $this->returndata[$number]->success = FALSE;
                  $this->returndata[$number]->rogoid = 0;
                  $this->returndata[$number]->url = '';
                  $this->returndata[$number]->message = '';
            $this->authObj[$number] = new $authtype($this, $settings, $this->db, $this->returndata, $number, $form);

            $this->debug[] = 'Running Registering callback routines';
            $this->authObj[$number]->register_callback_routines();
      */
    }

    $preauthobj = new stdClass();
    if (isset($this->callbackregister['preauth'])) {
      foreach ($this->callbackregister['preauth'] as $number => $callback) {
        call_user_func_array($callback, array($preauthobj));
        $this->append_auth_object_debug($number);
      }
    }

    $authobj = new stdClass();
    if (isset($this->callbackregister['auth'])) {
      foreach ($this->callbackregister['auth'] as $number => $callback) {
        $returned = call_user_func_array($callback, array($authobj));
        if ($returned !== FALSE) {
          $this->success = TRUE;
        }
        $this->append_auth_object_debug($number);
        if (($this->success and (!isset($settings['dont_break_on_success']) or (isset($settings['dont_break_on_success']) and !$settings['dont_break_on_success'])))) {
          break;
        }
      }
    }

    $postauthobj = new stdClass();
    if (isset($this->callbackregister['postauth'])) {
      foreach ($this->callbackregister['postauth'] as $number => $callback) {
        call_user_func_array($callback, array($postauthobj));
        $this->append_auth_object_debug($number);
      }
    }

    if ($this->success === FALSE) {
      //failed

      $postauthfailobj = new postauthfailreturn();
      $_SESSION['authenticationObj']['attempt']++;
      if (isset($this->callbackregister['postauthfail'])) {
        foreach ($this->callbackregister['postauthfail'] as $number => $callback) {
          call_user_func_array($callback, array(&$postauthfailobj));
          $this->append_auth_object_debug($number);

          if($postauthfailobj->form=='std') {

            $this->display_form();

            if($postauthfailobj->exit===TRUE)
            {
              var_dump($this->debug);
              exit();
            }
          }

          if($postauthfailobj->stop===TRUE) {
            break;
          }
        }
      }

      //failed actions
      /*
            if($_SESSION['authenticationObj']['attempt']==1) {
              foreach ($this->config as $number => $auth) {
                $action=$this->authObj[$number]->form();
              }
            }
      */
    }

    if ($this->success !== TRUE) {
      $this->debug[] = 'Success is not TRUE or FALSE';
      //something went very wrong;
      return false;

    }

    $postauthsuccessobj = new stdClass();
    if (isset($this->callbackregister['postauthsuccess'])) {
      foreach ($this->callbackregister['postauthsuccess'] as $number => $callback) {
        call_user_func_array($callback, array($postauthsuccessobj));
        $this->append_auth_object_debug($number);
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

  function append_auth_object_debug($number,$desc='') {
    $new_messages = $this->returndata[$number]->get_new_debug_messages();
    foreach ($new_messages as $value) {
      $this->debug[] = "authObj[$number]:$desc: $value";
    }
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
      $returnarray[] = $this->debug[$this->debugpointer++];
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

