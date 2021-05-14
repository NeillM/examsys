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

if ($updater_utils->check_version('7.4.0')) {
    if (!$updater_utils->has_updated('ROGO-2671')) {
        $sql = 'ALTER TABLE questions_metadata ADD CONSTRAINT questions_metadata_fk0 FOREIGN KEY (questionID) REFERENCES questions(q_id)';
        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('ROGO-2671');
    }
}
