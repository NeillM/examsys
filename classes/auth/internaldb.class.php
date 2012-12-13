<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 28/11/12
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */
/**
 *
 * The internaldb authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */
class internaldb_auth {

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
  }


  function register_callback_routines() {
    $this->calling_object->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'update_password'), 'postauthsuccess', $this->number, $this->name);
  }

  function set_fail() {
    $this->retdata->success = FALSE;
    $this->retdata->form = 'std';
    $this->retdata->rogoid = 0;
    $this->retdata->url = '';
  }

  function failauth(&$postauthfailreturn) {
    $this->retdata->debug[] = 'Fail function passed ' . var_export($postauthfailreturn, TRUE);

    //   $this->retdata->debug[]='info:' . var_export($this->settings,TRUE);

//default behaviour is to display username/password form
    $postauthfailreturn->form = 'std';
    $postauthfailreturn->exit = TRUE;

    if ((isset($this->settings['displayfailuremessagenumber']) and $postauthfailreturn->attempt >= $this->settings['displayfailuremessagenumber']) or (!isset($this->settings['displayfailuremessagenumber']) and $postauthfailreturn->attempt > 3)) {
      $this->retdata->debug[] = 'Requisite number of fail attempts so display error form';
      $postauthfailreturn->form = 'err';
      $postauthfailreturn->exit = TRUE;
    }

    if (isset($this->settings['continueonfail'])) {
      $this->retdata->debug[] = 'Setting to carry on despite setting things';
      $postauthfailreturn->exit = FALSE;
      $postauthfailreturn->stop = FALSE;
    }
    $this->retdata->debug[] = 'post run ' . var_export($postauthfailreturn, TRUE);

    return;

  }

  function auth($authobj) {
    $this->retdata->debug[] = 'Authing';
    /*
        foreach ($this->settings as $key => $value) {
          ${$key} = $value;
        }*/

    extract($this->settings);

    if (!isset($this->form['std']->username) or !isset($this->form['std']->username) or $this->form['std']->username == '' or $this->form['std']->password == '') {
      //return not sucessfull do not try
      $this->retdata->debug[] = 'Check 1 blank entries';

      $this->set_fail();
      $this->retdata->message = 'Not valid entry for username or password';

      return FALSE;
    }

    $sql = "SELECT $username_col as username, $passwd_col as passwd, $id_col as id FROM $table WHERE $username_col=?";
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $this->form['std']->username);
    $result->execute();
    $result->store_result();

    $result->bind_result($uname, $pass, $id);


    if ($result->num_rows() !== 1) {
      // return not sucessfull either no user or multiple matches
      $this->retdata->debug[] = 'Check 2 record number not = 1 no user or multiple user found';

      $this->set_fail();
      $this->retdata->message = 'Incorrect number of records returned';

      return FALSE;

    }
    $result->fetch();
    if (substr($pass, 0, 3) == '$1$') {
      $old_encrypt_type = 'MD5';
      $this->retdata->debug[] = 'Using old encryption';

    } else {
      $old_encrypt_type = 'SHA-512';
    }

    $this->updatable = TRUE;
    $encrypt_password = encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password, $old_encrypt_type);

    $this->retdata->debug[] = 'encrypted password strings ' . $encrypt_password . ':::' . $pass;

    if ($encrypt_password == $pass) {
      $this->updatable = FALSE;
      if ($old_encrypt_type == 'MD5') { // Re-encrypt MD5 passwords using SHA-512.
        $this->retdata->debug[] = 'Re Encrypting PW';
        $this->update_password();

      }
      $this->retdata->debug[] = 'Successfully authenticated on this module';

      //sucessfull internaldb authentication
      $this->retdata->success = TRUE;
      $this->retdata->form = 'std';
      $this->retdata->rogoid = $id;
      $this->rogoid = $id;
      $this->retdata->url = '';
      $authobj->retdata = $this->retdata;
      $this->retdata->message = 'Internal DB Correctly Authenticated';

      return TRUE;
    }
    $this->retdata->debug[] = 'Password not matching';
    $this->set_fail();

    return FALSE;
  }

  function update_password($postauthsuccessobj = '') {
    $this->retdata->debug[] = 'Called update_password';
    if ($this->updatable === TRUE and (!isset($this->settings['donotupdatepassword']) or (isset($this->settings['donotupdatepassword']) and $this->settings['donotupdatepassword'] !== TRUE))) {
      $this->retdata->debug[] = 'Updating Password';
      extract($this->settings);
      $encpw_details = encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password);
      $stmt = $this->db->prepare("UPDATE $table SET $passwd_col = ? WHERE $username_col = ?");
      $stmt->bind_param('ss', $encpw_details, $this->form['std']->username);
      $stmt->execute();
      $stmt->close();
    } elseif ((isset($this->settings['donotupdatepassword']) and $this->settings['donotupdatepassword'] === TRUE)) {
      $this->retdata->debug[] = 'Not updating password due to settings flag';
    }
  }

  function form() {
    $retdata = new stdClass();
    $retdata->form = 'std';

    return $retdata;
  }

}
