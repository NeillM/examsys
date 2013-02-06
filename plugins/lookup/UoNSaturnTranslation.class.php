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

include_once $configObj->get('cfg_web_root') . 'lang/en/include/common.inc';


class UoNSaturnTranslation_lookup extends outline_authentication {

  public $impliments_api_lookup_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'usertranslatelookup'), 'usertranslatelookup', $this->number, $this->name);


    return $callbackarray;
  }

  function usertranslatelookup($userlookupobj) {

    if ($this->orsearchlist($userlookupobj->lookupdata->role, array('Undergraduate', 'Postgraduate', 'UG', 'PGT', 'PG'))) {
      $userlookupobj->role = 'Student';
    }

    if ($this->orsearchlist($userlookupobj->lookupdata->role, array('S'))) {
      $userlookupobj->role = 'Staff';
    }

    if (isset($userlookupobj->lookupdata->staffID)) {
      $userlookupobj->lookupdata->role = 'Staff';
    }

    if (isset($userlookupobj->lookupdata->sttudentID)) {
      $userlookupobj->lookupdata->role = 'Student';
    }

    if (strpos($userlookupobj->lookupdata->attendstatus, 'Suspended') !== FALSE) {
      $userlookupobj->lookupdata->disabled = TRUE;
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
