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
 * The UoN Saturn Translation for XML to Rogo Internal format
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once 'outline_lookup.class.php';

include_once $configObject->get('cfg_web_root') . 'lang/en/include/common.inc';


class UoNSaturnTranslation_lookup extends outline_lookup {

  public $impliments_api_lookup_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'usertranslatelookup'), 'usertranslatelookup', $this->number, $this->name);


    return $callbackarray;
  }

  function usertranslatelookup($userlookupobj) {

    $this->savetodebug('Running user translate lookup in UoN Saturn Translate');

    if ($this->orsearchlist($userlookupobj->lookupdata->role, array('Undergraduate', 'Postgraduate', 'UG', 'PGT', 'PG'))) {
      $this->savetodebug('Detected Student, correcting role');
      $userlookupobj->lookupdata->role = 'Student';
    }

    if ($this->orsearchlist($userlookupobj->lookupdata->role, array('S')) or isset($userlookupobj->lookupdata->staffID)) {
      $this->savetodebug('Detected staff, correcting role and filling in fields');
      $userlookupobj->lookupdata->role = 'Staff';
      $userlookupobj->lookupdata->coursecode = 'University Lecturer';
      $userlookupobj->lookupdata->yearofstudy = 1;
    }

    if (isset($userlookupobj->lookupdata->sttudentID)) {
      $this->savetodebug('Detected Possible Student, correcting role for safety');
      $userlookupobj->lookupdata->lookupdata->role = 'Student';
    }

    if (isset($userlookupobj->lookupdata->attendstatus) and strpos($userlookupobj->lookupdata->attendstatus, 'Suspended') !== FALSE) {
      $this->savetodebug('status is suspended diasbling');
      $userlookupobj->lookupdata->disabled = TRUE;
    }

    if (isset($userlookupobj->lookupdata->gender) and $userlookupobj->lookupdata->gender == 'M') {
      $userlookupobj->lookupdata->gender = 'Male';
    }
    if (isset($userlookupobj->lookupdata->gender) and $userlookupobj->lookupdata->gender == 'F') {
      $userlookupobj->lookupdata->gender = 'Female';
    }


    if (!isset($userlookupobj->lookupdata->gender)) {
      if (stripos($userlookupobj->lookupdata->title, 'Mr') !== FALSE) {
        $userlookupobj->lookupdata->gender = 'Male';
      }
      if (stripos($userlookupobj->lookupdata->title, 'Ms') !== FALSE or stripos($userlookupobj->lookupdata->title, 'Miss') !== FALSE or stripos($userlookupobj->lookupdata->title, 'Mrs') !== FALSE) {
        $userlookupobj->lookupdata->gender = 'Female';
      }
    }

    return $userlookupobj;
  }

  function orsearchlist($field, $text) {
    $found = FALSE;
    foreach ($text as $value) {
      if ($field == $value) {
        $found = TRUE;
      }
    }

    return $found;
  }

}
