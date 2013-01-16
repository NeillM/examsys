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
 * The ldap authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
require_once 'outline_authentication.class.php';

include_once $configObj->get('cfg_web_root') . 'lang/en/include/common.inc';

class ldap_auth extends outline_authentication {

  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $this->register_callback(array($this, 'auth'), 'auth', $this->number, $this->name);
    $this->register_callback(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
    $this->register_callback(array($this, 'errordisp'), 'displayerrform', $this->number, $this->name);
    return $this->callbackarray;
  }

  function errordisp(&$displayerrformobj) {
    global $string;
    $this->savetodebug('adding ldap notice to error screen');
    $displayerrformobj->li[]=$string['tsonldap'];
  }

  function failauth(&$postauthfailreturn) {
    $this->savetodebug('Fail function passed ' . var_export($postauthfailreturn, TRUE));

    //   $this->retdata->debug[]='info:' . var_export($this->settings,TRUE);

//default behaviour is to display username/password form
    $postauthfailreturn->form = 'std';
    $postauthfailreturn->exit = TRUE;

    if ((isset($this->settings['displayfailuremessagenumber']) and $postauthfailreturn->attempt >= $this->settings['displayfailuremessagenumber']) or (!isset($this->settings['displayfailuremessagenumber']) and $postauthfailreturn->attempt > 3)) {
      $this->savetodebug('Requisite number of fail attempts so display error form');
      $postauthfailreturn->form = 'err';
      $postauthfailreturn->exit = TRUE;
    }

    if (isset($this->settings['continueonfail'])) {
      $this->savetodebug('Setting to carry on despite setting things');
      $postauthfailreturn->exit = FALSE;
      $postauthfailreturn->stop = FALSE;
    }
    $this->savetodebug('post run ' . var_export($postauthfailreturn, TRUE));

    return;

  }


  function auth($authobj) {
    global $string;
    $this->savetodebug('Authing');
    extract($this->settings);

    if (!isset($this->form['std']->username) or !isset($this->form['std']->username) or $this->form['std']->username == '' or $this->form['std']->password == '') {
      //return not sucessfull do not try
      $this->savetodebug('Check 1 blank entries');

      $this->set_fail();
      $this->retdata->message = 'Not valid entry for username or password';

      return FALSE;
    }
    $ldap = ldap_connect($ldap_server);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if (ldap_bind($ldap, $ldap_bind_rdn, $ldap_bind_password)) {
      $this->savetodebug('Sucessfull initial bind to ldap server');
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
          $this->savetodebug('Found user in ldap');
          $dn = $info[0]['dn'];
        } else {
          $this->savetodebug('<strong>' . $string['noldapaccount'] . '</strong>');
          $this->set_fail();

          return FALSE;
        }
      }

      if (@ldap_bind($ldap, $dn, utf8_encode($this->form['std']->password))) {
        $this->savetodebug('Successfully bound to ldap as the user with their password');
        ldap_unbind($ldap);
        /*
               if($lookup_info === 2) {
                 return $info;
               }
       */
        $this->savetodebug('Now looking up userid in table from username');
        $sql = "SELECT $username_col as username, $id_col as id FROM $table WHERE $username_col=?";
        $result = $this->db->prepare($sql);

        $result->bind_param('s', $this->form['std']->username);
        $result->execute();
        $result->store_result();
        $this->savetodebug('sql is:' . $sql . ' with parameter:' . $this->form['std']->username);

        $result->bind_result($uname, $id);
        $result->fetch();

        $this->savetodebug('uname:' . $uname . ' id:' . $id);
        if ($result->num_rows() !== 1) {
          // not unique match
          $this->savetodebug('Check 2 record number not = 1 no user or multiple user found in lookup');

          $this->set_fail();
          $this->retdata->message = 'Incorrect number of records returned';

          return FALSE;

        }
        $this->savetodebug('Successfully authenticated on this module username=' . $this->form['std']->username . ' id:' . $id);

        //sucessfull internaldb authentication
        $this->retdata->success = TRUE;
        $this->retdata->form = 'std';
        $this->retdata->rogoid = $id;
        $this->rogoid = $id;
        $this->retdata->url = '';
        $authobj->retdata = $this->retdata;

        return TRUE;
      } else {
        $this->savetodebug($string['incorrectpassword']);
        $this->set_fail();

        return FALSE;
      }
    } else {
      $this->savetodebug('Couldnt Bind to ldap server');
      $this->set_fail();

      return FALSE;
    }


  }

}
