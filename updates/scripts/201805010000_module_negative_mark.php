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

if ($updater_utils->check_version('7.0.0')) {
    if (!$updater_utils->has_updated('rogo2356')) {
        // Remove NULL from the neg_marking column. It will only be present in newer installs on the default modules.
        $sql = 'UPDATE modules SET neg_marking = 0 WHERE neg_marking IS NULL';
        $updater_utils->execute_query($sql, false);
        // Set the default value to be 0.
        $altersql = 'ALTER TABLE modules CHANGE COLUMN neg_marking neg_marking TINYINT(1) NULL DEFAULT 0';
        $updater_utils->execute_query($altersql, false);
        $updater_utils->record_update('rogo2356');
    }
}
