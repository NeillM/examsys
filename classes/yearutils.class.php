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

class YearUtils {

    private $mysqli;

    /**
     *
     * @param type $mysqli
     */
    function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }


    /**
     *
     * @param type $state
     * @return type
     */
    public function get_supported_years($state = "ALL") {

        if ($state == "STAT") {
            $filter = "WHERE stat_status = 1";
        } else if ($state == "CAL") {
            $filter = "WHERE cal_status = 1";
        } else if ($state == "BOTH") {
            $filter = "WHERE cal_status = 1 AND stat_status = 1";
        } else {
            $filter = "";
        }

        $supported_years = array();
        $result = $this->mysqli->prepare("SELECT calendar_year, academic_year FROM academic_year $filter");
        $result->execute();
        $result->bind_result($calendar_year, $academic_year);
        while ($result->fetch()) {
            $supported_years[$calendar_year] = $academic_year;
        }
        $result->close();
        return $supported_years;
    }

    public function get_calendar_year_dropdown_options($paper_type, $calendar_year, $string) {
        $list = "";
        if ($paper_type != '2' and $paper_type != '4') {
            $list = "<option value=\"\">" . $string['na'] .  "</option>\n";
        }

        $years = $this->get_supported_years();

        foreach ($years as $calendar => $academic) {
            $list .= "<option value=\"" . $calendar . "\"";
            if ($calendar_year == $academic) {
                $list .= 'selected';
            }
            $list .= ">" . $academic . "</option>\n";
        }
        return $list;
    }
}
