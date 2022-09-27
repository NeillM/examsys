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
 * Utility class for retention related functions.
 *
 * Any table that wishes to support retention should have a column 'time' of type 'bigint(10)'
 * this column will be used to apply the data retention policy for that table.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
class Retention
{
    /**
     * Delete data according to the data retention policy.
     *
     * @param string $table the table to apply policy to
     * @return boolean
     */
    public static function deleteDataByRetentionPolicy($table): bool
    {
        $now = date('Y-m-d H:i:s', time());
        $retention = self::getRententionPeriod($table);
        if (empty($retention)) {
            return false;
        }
        $type = \DBUtils::checkColumnType($table, 'time');
        if ($type === 'timestamp') {
            $retentionperiod = date('Y-m-d H:i:s', strtotime('-' . $retention . ' day'));
        } else {
            $retentionperiod = time() - (\date_utils::DAYSECS * $retention);
        }
        $sql = 'DELETE FROM ' . $table . ' WHERE time < ?';
        $query = Config::get_instance()->db->prepare($sql);
        if ($type === 'timestamp') {
            $query->bind_param('s', $retentionperiod);
        } else {
            $query->bind_param('i', $retentionperiod);
        }
        $query->execute();
        $query->close();
        self::setLast($table, $now);
        return true;
    }

    /**
     * Set the retention period
     *
     * @param int $days retention period
     * @param string $table the table to apply policy to
     */
    public static function setRetentionPeriod(int $days, string $table): void
    {
        $current = self::getRententionPeriod($table);
        if ($current !== $days) {
            $sql = 'UPDATE retention SET days = ? WHERE `table` = ?';
            $query = Config::get_instance()->db->prepare($sql);
            $query->bind_param('is', $days, $table);
            $query->execute();
            $query->close();
        }
    }

    /**
     * Get the retention period for the data.
     *
     * @param string $table the table to apply policy to
     * @return ?int
     */
    public static function getRententionPeriod(string $table): ?int
    {
        $sql = 'SELECT days FROM retention WHERE `table` = ?';
        $query = Config::get_instance()->db->prepare($sql);
        $query->bind_param('s', $table);
        $query->execute();
        $query->store_result();
        $query->bind_result($days);
        $query->fetch();
        $query->close();
        return $days;
    }

    /**
     * Get list of retention policies tables
     * @return array
     */
    public static function getRetentionPolicesTables(): array
    {
        $tables = [];
        $sql = 'SELECT `table` FROM retention';
        $query = Config::get_instance()->db->prepare($sql);
        $query->execute();
        $query->store_result();
        $query->bind_result($table);
        while ($query->fetch()) {
            $tables[] = $table;
        }
        $query->close();
        return $tables;
    }

    /**
     * Get last time the retention process was run
     * @param string $table the table
     * @return ?string
     */
    public static function getLast(string $table): ?string
    {
        $sql = 'SELECT lastrun FROM retention WHERE `table` = ?';
        $query = \Config::get_instance()->db->prepare($sql);
        $query->bind_param(
            's',
            $table
        );
        $query->execute();
        $query->bind_result($last);
        $query->fetch();
        $query->close();
        return $last;
    }

    /**
     * Set the last time retention process was run
     * @param string $table the table
     * @param string $time the timestamp of the last sent mail
     */
    public static function setLast(string $table, string $time): void
    {
        $sql = 'UPDATE retention SET lastrun = ? WHERE `table` = ?';
        $query = \Config::get_instance()->db->prepare($sql);
        $query->bind_param(
            'ss',
            $time,
            $table
        );
        $query->execute();
        $query->close();
    }
}
