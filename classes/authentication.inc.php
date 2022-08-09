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

if(!defined('SCT_AUTH_SUCCESS')) {
    if (defined('SCT_AUTH') ) { // Check if already login)) {
        require_once $cfg_web_root . '/include/sct_review.inc';
    } else {
        $authentication = new Authentication($configObject, $mysqli, $_REQUEST, $_SESSION);
        $authentication->do_authentication($string);
        $getauthobj = new auth_obj();
        $authentication->get_auth_obj($getauthobj);
        define('SCT_AUTH_SUCCESS', 1);
    }
}

