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

if ($updater_utils->check_version('7.5.0') and !$updater_utils->has_updated('ROGO-3233')) {
    $sql1 = 'ALTER TABLE questions_modules ADD CONSTRAINT `questions_modules_fk1` FOREIGN KEY (`q_id`) REFERENCES `questions` (`q_id`)';
    $sql2 = 'ALTER TABLE questions_modules ADD CONSTRAINT `questions_modules_fk2` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)';
    $updater_utils->execute_query($sql1, false);
    $updater_utils->execute_query($sql2, false);
    $updater_utils->record_update('ROGO-3233');
}