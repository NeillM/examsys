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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

/**
 * Draw tabs of available years to view.
 * 
 * @param string $current_tab - the tab currently in view 
 * @param string $type type of year - academic or calendar
 * @param string $extra - extra html to add to table cell
 * @return string $html - the html for the tab
 */
function drawTabs($current_tab, $type, $extra = '') {

    global $mysqli;

    $yearutils = new yearutils($mysqli);

    $html = '<table cellpadding="0" cellspacing="0" border="0" style="margin-top:8px; margin-left:auto; margin-right:0px"><tr>';

    if ($type == "calendar") {
        $supported_years = $yearutils->get_supported_years("CAL");
    } else {
        $supported_years = $yearutils->get_supported_years("STAT");
    }

    foreach ($supported_years as $calendar => $academic) {
        if ($type == "academic") {
            $tab_year = $academic;
        } else {
            $tab_year = $calendar;
        }

        if ($calendar == $current_tab) {
            $html .= "<td class=\"tabon\" onclick=\"location.href='" . $_SERVER['PHP_SELF'] . "?calyear=" . $calendar . $extra . "'\">" . $tab_year . "</td>";
        } else {
            $html .= "<td class=\"taboff\" onclick=\"location.href='" . $_SERVER['PHP_SELF'] . "?calyear=" . $calendar . $extra ."'\">" . $tab_year . "</td>";
        }

    }

    $html .= "</tr></table>";
  
    return $html;
}
?>