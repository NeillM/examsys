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
 * Utility class for scheduled mail related functions.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
trait ScheduledMail
{
    /**
     * Send mail.
     */
    abstract public static function sendMail();

    /**
     * Get last time scheduled mail was sent.
     * @param string $type type of mail
     * @return ?string
     */
    public static function getLast(string $type): ?string
    {
        $sql = 'SELECT last FROM scheduledmail WHERE type = ?';
        $query = \Config::get_instance()->db->prepare($sql);
        $query->bind_param(
            's',
            $type
        );
        $query->execute();
        $query->bind_result($last);
        $query->fetch();
        $query->close();
        return $last;
    }

    /**
     * Set the last time scheduled mail was sent.
     * @param string $type type of mail
     * @param int $time the timestamp of the last sent mail
     */
    public static function setLast(string $type, int $time): void
    {
        $sql = 'UPDATE scheduledmail SET last = ? WHERE type = ?';
        $query = \Config::get_instance()->db->prepare($sql);
        $query->bind_param(
            'ss',
            $time,
            $type
        );
        $query->execute();
        $query->close();
    }
}
