<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 28/11/12
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */
class internaldb {

  private $name;
  private $number;
  private $returndata;
  private $retdata;
  private $form;
  private $settings;
  private $db;
  private $calling_object;


  function __construct($calling_object, $settings, $db, &$returndata, $number, $form) {
    $this->db = new mysqli();
    $this->calling_object = $calling_object;
    $this->returndata = $returndata;
    $this->number = $number;
    $this->retdata = $returndata[$number];
    $this->form = $form;
    $this->db = $db;
    $this->settings = $settings;
  }


  function register_callback_routines() {

    $this->calling_object->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
    $this->calling_object->register_callback(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);

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

    if (isset($this->settings['continueonfail'])) {
      $this->retdata->debug[] = 'Setting to carry on despite setting things';
      $postauthfailreturn->exit = FALSE;
      $postauthfailreturn->stop = FALSE;
    }
    $this->retdata->debug[] = 'post run ' . var_export($postauthfailreturn, TRUE);

    return;

  }

  function auth() {
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
      return false;
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
      return false;

    }
    $result->fetch();
    if (substr($pass, 0, 3) == '$1$') {
      $old_encrypt_type = 'MD5';
      $this->retdata->debug[] = 'Using old encryption';

    } else {
      $old_encrypt_type = 'SHA-512';
    }


    $encrypt_password = encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password, $old_encrypt_type);

    $this->retdata->debug[] = $encrypt_password . ':::' . $pass;
    if ($encrypt_password == $pass) {
      if ($old_encrypt_type == 'MD5') { // Re-encrypt MD5 passwords using SHA-512.
        $this->retdata->debug[] = 'Re Encrypting PW';

        $encpw_details = encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password);
        $stmt = $this->db->prepare("UPDATE $table SET $passwd_col = ? WHERE $username_col = ?");
        $stmt->bind_param('ss', $encpw_details, $this->form['std']->username);
        $stmt->execute();
        $stmt->close();
      }
      $this->retdata->debug[] = 'Success point';

      //sucessfull internaldb authentication
      $this->retdata->success = TRUE;
      $this->retdata->form = 'std';
      $this->retdata->rogoid = $id;
      $this->retdata->url = '';
      $this->retdata->message = 'Internal DB Correctly Authenticated';

      return true;
    }
    $this->retdata->debug[] = 'Password not matching';
    $this->set_fail();
    return false;
  }

  function form() {
    $retdata = new stdClass();
    $retdata->form = 'std';
    return $retdata;
  }

}
