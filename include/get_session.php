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
 * Start a session
 *
 * @author Yijun Xue
 * @version 1.0
 * @copyright Copyright (c) 2022 The University of Nottingham
 * @package
 */

// Cookie sessions only.
ini_set('session.use_only_cookies', 1);
// Use secure cookie if on secure conenction.
if ($configObject->get('cfg_secure_connection')) {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
}

if (empty(session_id())) { // If the session not exist
    //start the session early as the lang class looks in the session
    if ($configObject->get('cfg_session_name') != '') {
        session_name($configObject->get('cfg_session_name'));
    } else {
        session_name('RogoAuthentication');
    }

    if (ini_get('session.save_handler') === 'memcache' or ini_get('session.save_handler') === 'memcached') {
        $sessionhandler = new memcachesessionhandler();
        session_set_save_handler($sessionhandler);
    }
    $return = session_start();
}
