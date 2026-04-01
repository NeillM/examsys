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
 * Updates the default colours in the paper properties table.
 *
 * The papers properties page now uses colorpicker input elements which do now support named colours.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */

if ($updater_utils->check_version('7.7.0') and !$updater_utils->has_updated('examsys_3357')) {
    // Update the table defaults.
    $sql = <<<SQL
        ALTER TABLE `{$configObject->get('cfg_db_database')}`.`properties`
            CHANGE COLUMN `bgcolor` `bgcolor` VARCHAR(20) NULL DEFAULT '#FFFFFF',
            CHANGE COLUMN `fgcolor` `fgcolor` VARCHAR(20) NULL DEFAULT '#000000'
    SQL;
    $updater_utils->execute_query($sql);

    // Change white to its hex colour code.
    $sql = <<<SQL
        UPDATE `{$configObject->get('cfg_db_database')}`.`properties` 
        SET `bgcolor` = '#FFFFFF'
        WHERE `bgcolor` = 'white'
    SQL;
    $updater_utils->execute_query($sql);

    // Change black to its hex colour code.
    $sql = <<<SQL
        UPDATE `{$configObject->get('cfg_db_database')}`.`properties` 
        SET `bgcolor` = '#000000'
        WHERE `bgcolor` = 'black'
    SQL;
    $updater_utils->execute_query($sql);

    $updater_utils->record_update('examsys_3357');
}
