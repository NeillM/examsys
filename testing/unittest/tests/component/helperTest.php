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

use component\Helper;

/**
 * Tests the component helper class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 * @group component
 * @covers \component\Helper
 */
class HelperTest extends \testing\unittest\UnitTest
{
    /**
     * The output string should include the components strings.
     */
    public function testCombineLangAdding(): void
    {
        $string = [
            'mystring' => 'My String',
        ];

        $component = new \component\breadcrumb\Breadcrumb();

        $output = Helper::combineLang($string, $component);

        // We will not compare the entire output array as we do not want the test
        // to break if new strings are added to the component in the future.
        $this->assertEquals('My String', $output['mystring']);

        // We are checking for a single known string from the Breadcrumb component.
        $this->assertArrayHasKey('breadcrumb', $output);
        $this->assertNotEmpty($output['breadcrumb']);
    }

    /**
     * Existing language strings should not be overwritten by component strings.
     */
    public function testCombineLangNoOverwrite(): void
    {
        $string = [
            'breadcrumb' => 'My String',
        ];

        $component = new \component\breadcrumb\Breadcrumb();

        $output = Helper::combineLang($string, $component);

        // We will not compare the entire output array as we do not want the test
        // to break if new strings are added to the component in the future.
        $this->assertEquals('My String', $output['breadcrumb']);
    }
}
