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


  function __construct($configObj, $db) {
    $this->db = $db;
    $this->configObj = $configObj;

    $this->load_config();

  }

  function load_config() {

    $this->config = $this->configObj->get('authentication');

    $this->debug[] = 'Loaded Config for authentication';
  }

  function do_authentication() {
    $this->debug[] = 'Starting authentication';
    $form = array();
    if (isset($_REQUEST['rogo-login-form-std'])) {
      $form['std'] = new stdClass();
      $form['std']->username = $_REQUEST['ROGO_USER'];
      $form['std']->password = $_REQUEST['ROGO_PW'];
      $this->debug[] = 'Standard form data found - Storing in object';

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
$this->authObj[$number] = new $authtype($this->configObj, $settings, $this->db, $this->returndata, $number, $form);

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
    print "done dump";

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
