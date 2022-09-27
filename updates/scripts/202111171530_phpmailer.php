<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Changes the database so that the extra time column can store a value of more than 128%
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */

if ($updater_utils->check_version('7.5.0')) {
    if (!$updater_utils->has_updated('rogo_3166')) {
        $configObject->set_setting('mailer_host', '', Config::STRING);
        $configObject->set_setting('mailer_port', '', Config::STRING);
        $configObject->set_setting('mailer_debug', 0, Config::INTEGER);
        $configObject->set_setting('mailer_auth', false, Config::BOOLEAN);
        $configObject->set_setting('mailer_username', '', Config::STRING);
        $configObject->set_setting('mailer_password', '', Config::STRING);
        $configObject->set_setting('mailer_secure', '', Config::STRING);
        $updater_utils->record_update('rogo_3166');
    }
}
