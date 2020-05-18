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

use testing\unittest\unittestdatabase;

/**
 * Testcase for class YearUtils.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group utils
 */
class YearUtilsTest extends unittestdatabase
{
    /**
     * Generate common data for test.
     */
    public function datageneration(): void
    {
        // Currently only base data required.
    }

    /**
     * Test generating tabs.
     */
    public function testGenerateTabs(): void
    {
        $yearutils = new yearutils($this->db);
        $currentsession = $yearutils->get_current_session();
        // Current session may exist.
        if ($yearutils->check_calendar_year($currentsession)) {
            $expected[] = array(
                'selected' => false,
                'url' => $_SERVER['PHP_SELF'] . '?calyear=' . $currentsession . '&month=05',
                'tabyear' => $currentsession . '/' . date('y', strtotime($currentsession))
            );
        }
        // Current year exists by default.
        $currentyear = date('Y');
        $expected[] = array(
            'selected' => true,
            'url' => $_SERVER['PHP_SELF'] . '?calyear=' . $currentyear . '&month=05',
            'tabyear' => $currentyear . '/' . (date('y') + 1)
        );
        $this->assertEquals($expected, $yearutils->generateTabs($currentyear, 'academic', '&month=05'));

        // Current session may exist.
        if ($yearutils->check_calendar_year($currentsession)) {
            $expected2[] = array(
                'selected' => false,
                'url' => $_SERVER['PHP_SELF'] . '?calyear=' . $currentsession,
                'tabyear' => $currentsession
            );
        }
        // Current year exists by default.
        $expected2[] = array(
                'selected' => true,
                'url' => $_SERVER['PHP_SELF'] . '?calyear=' . $currentyear,
                'tabyear' => $currentyear
        );
        $this->assertEquals($expected2, $yearutils->generateTabs($currentyear, 'calendar', ''));
    }
}
