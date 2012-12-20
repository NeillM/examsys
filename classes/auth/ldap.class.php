<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 12/12/12
 * Time: 11:41
 * To change this template use File | Settings | File Templates.
 */
/**
 *
 * The ldap authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

include_once $configObj->get('cfg_web_root') . 'lang/en/include/common.inc';

class ldap_auth {

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


  function set_fail() {
    $this->retdata->success = FALSE;
    $this->retdata->form = 'std';
    $this->retdata->rogoid = 0;
    $this->retdata->url = '';
  }

  function auth($authobj) {
    global $string;
    $this->retdata->debug[] = 'Authing';
    extract($this->settings);

    if (!isset($this->form['std']->username) or !isset($this->form['std']->username) or $this->form['std']->username == '' or $this->form['std']->password == '') {
      //return not sucessfull do not try
      $this->retdata->debug[] = 'Check 1 blank entries';

      $this->set_fail();
      $this->retdata->message = 'Not valid entry for username or password';

      return FALSE;
    }
    $ldap = ldap_connect($ldap_server);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if (ldap_bind($ldap, $ldap_bind_rdn, $ldap_bind_password)) {
      $this->retdata->debug[]='Sucessfull initial bind to ldap server';
      if (!($search = @ldap_search($ldap, $ldap_search_dn, $ldap_user_prefix . $this->form['std']->username))) {
        $this->retdata->debug[] = $string['ldapservernosearch'];
        $this->set_fail();

        return FALSE;
      } else {

        $info = ldap_get_entries($ldap, $search);
        /*
                if($lookup_info === 1 and $info['count'] > 0) {
                  $this->set_fail();
                  return $info;
                }
        */
        if ($info['count'] == 1) {
          $this->retdata->debug[]='Found user in ldap';
          $dn = $info[0]['dn'];
        } else {
          $this->retdata->debug[] = '<strong>' . $string['noldapaccount'] . '</strong>';
          $this->set_fail();

          return FALSE;
        }
      }

      if (@ldap_bind($ldap, $dn, utf8_encode($this->form['std']->password))) {
        $this->retdata->debug[]='Successfully bound to ldap as the user with their password';
        ldap_unbind($ldap);
        /*
               if($lookup_info === 2) {
                 return $info;
               }
       */
        $this->retdata->debug[] = 'Now looking up userid in table from username';
        $sql = "SELECT $username_col as username, $id_col as id FROM $table WHERE $username_col=?";
        $result = $this->db->prepare($sql);

        $result->bind_param('s', $this->form['std']->username);
        $result->execute();
        $result->store_result();
        $this->retdata->debug[]='sql is:' . $sql . ' with parameter:' . $this->form['std']->username;

        $result->bind_result($uname, $id);
        $result->fetch();

        $this->retdata->debug[] = 'uname:' . $uname . ' id:' . $id;
        if ($result->num_rows() !== 1) {
          // not unique match
          $this->retdata->debug[] = 'Check 2 record number not = 1 no user or multiple user found in lookup';

          $this->set_fail();
          $this->retdata->message = 'Incorrect number of records returned';

          return FALSE;

        }
        $this->retdata->debug[] = 'Successfully authenticated on this module username=' . $this->form['std']->username . ' id:' . $id;

        //sucessfull internaldb authentication
        $this->retdata->success = TRUE;
        $this->retdata->form = 'std';
        $this->retdata->rogoid = $id;
        $this->rogoid = $id;
        $this->retdata->url = '';
        $authobj->retdata = $this->retdata;

        return TRUE;
      } else {
        $this->retdata->debug[] = $string['incorrectpassword'];
        $this->set_fail();

        return FALSE;
      }
    } else {
      $this->retdata->debug[] = 'Couldnt Bind to ldap server';
      $this->set_fail();

      return FALSE;
    }


  }

}
