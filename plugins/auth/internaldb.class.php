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
 * The internaldb authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
require_once 'outline_authentication.class.php';

class internaldb_auth extends outline_authentication {

  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  private $updatable = FALSE;

  function register_callback_routines() {
    $callbackarray[]=array(array($this, 'auth'), 'auth', $this->number, $this->name);
    $callbackarray[]=array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
    $callbackarray[]=array(array($this, 'update_password'), 'postauthsuccess', $this->number, $this->name);
    $callbackarray[]=array(array($this, 'lookupuser'), 'lookupuser', $this->number, $this->name);

    return $callbackarray;
  }

  function failauth($postauthfailreturn) {
    $this->savetodebug('Fail function run'); // . var_export($postauthfailreturn, TRUE);

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


    return $postauthfailreturn;

  }

  function lookupuser($lookupuserobj) {

    if (!isset($lookupuserobj->username)) {
      $this->savetodebug('Lookup user has nothing to lookup');

    }
    extract($this->settings);
    $sql = "SELECT $username_col as username, $passwd_col as passwd, $id_col as id FROM $table WHERE $username_col=?";
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $lookupuserobj->username);
    $result->execute();
    $result->store_result();

    $result->bind_result($uname, $pass, $id);
    /*
        if ($result->num_rows() !== 1) {
          // return not sucessfull either no user or multiple matches
          $this->retdata->debug[] = 'Lookup user record number not = 1 no user or multiple user found';

          return FALSE;

        }
        */
    while ($result->fetch()) {
      $datastore = new stdClass();
      $datastore->userid = $id;
      $datastore->uname = $uname;
      $lookupuserobj->results[] = $datastore;
      $this->savetodebug(var_export($datastore, TRUE));
    }

    $lookupuserobj->found = TRUE;

    return $lookupuserobj;
  }

  function auth($authobj) {
    $this->retdata =& $authobj;
    $this->savetodebug('Authing');
    /*
        foreach ($this->settings as $key => $value) {
          ${$key} = $value;
        }*/

    extract($this->settings);

    if (!isset($this->form['std']->username) or !isset($this->form['std']->password) or $this->form['std']->username == '' or $this->form['std']->password == '') {
      //return not sucessfull do not try
      $this->savetodebug('Check 1 blank entries');

      $this->retdata->fail($this->number);
      $this->retdata->message = 'Not valid entry for username or password';

      return $authobj;
    }

    $sql = "SELECT $username_col as username, $passwd_col as passwd, $id_col as id FROM $table WHERE $username_col=?";
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $this->form['std']->username);
    $result->execute();
    $result->store_result();

    $result->bind_result($uname, $pass, $id);


    if ($result->num_rows() !== 1) {
      // return not sucessfull either no user or multiple matches
      $this->savetodebug('Check 2 record number not = 1 no user or multiple user found');

      $this->retdata->fail($this->number);
      $this->retdata->message = 'Incorrect number of records returned';

      return $authobj;

    }
    $result->fetch();
    if (substr($pass, 0, 3) == '$1$') {
      $old_encrypt_type = 'MD5';
      $this->savetodebug('Using old encryption');

    } else {
      $old_encrypt_type = 'SHA-512';
    }

    $this->updatable = TRUE;
    $encrypt_password = encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password, $old_encrypt_type);

    $this->savetodebug('encrypted password strings ' . $encrypt_password . ':::' . $pass);

    if ($encrypt_password == $pass) {
      $this->updatable = FALSE;
      if ($old_encrypt_type == 'MD5') { // Re-encrypt MD5 passwords using SHA-512.
        $this->savetodebug('Re Encrypting PW');
        $this->update_password();

      }
      $this->savetodebug('Successfully authenticated on this module');

      //sucessfull internaldb authentication
      $this->retdata->success($this->number, $id);
      $this->retdata->message = 'Internal DB Correctly Authenticated';

      return $authobj;
    }
    $this->savetodebug('Password not matching');
    $authobj->fail();

    return $authobj;
  }

  function update_password($postauthsuccessobj = '') {
    $this->savetodebug('Called update_password');
    if ($this->updatable === TRUE and (!isset($this->settings['donotupdatepassword']) or (isset($this->settings['donotupdatepassword']) and $this->settings['donotupdatepassword'] !== TRUE))) {
      $this->savetodebug('Updating Password');
      extract($this->settings);
      $encpw_details = encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password);
      $stmt = $this->db->prepare("UPDATE $table SET $passwd_col = ? WHERE $username_col = ?");
      $stmt->bind_param('ss', $encpw_details, $this->form['std']->username);
      $stmt->execute();
      $stmt->close();
    } elseif ((isset($this->settings['donotupdatepassword']) and $this->settings['donotupdatepassword'] === TRUE)) {
      $this->savetodebug('Not updating password due to settings flag');
    }
    return $postauthsuccessobj;
  }

}
