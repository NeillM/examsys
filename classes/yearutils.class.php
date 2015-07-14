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

Class YearUtils {

    static function get_calendar_supported_years($db) {

        $supported_years = array();
        $result = $db->prepare("SELECT id, display_year FROM academic_year where cal_status = 1");
        $result->execute();
        $result->bind_result($id, $display_year);
        while ($result->fetch()) {
            $supported_years[$id] = $display_year;
        }
        $result->close();
        return $supported_years;
    }

    static function get_statistics_supported_years($db) {

        $supported_years = array();
        $result = $db->prepare("SELECT id, display_year FROM academic_year where stat_status = 1");
        $result->execute();
        $result->bind_result($id, $display_year);
        while ($result->fetch()) {
            $supported_years[$id] = $display_year;
        }
        $result->close();
        return $supported_years;
    }
}
