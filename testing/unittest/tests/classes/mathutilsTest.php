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

namespace testing\unittest;

/**
 * Test mathutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2019 onwards The University of Nottingham
 * @package tests
 *
 * @covers \MathsUtils
 * @group math
 */
class mathutilstest extends UnitTest
{
    /**
     * Test percentile function
     */
    public function test_percentile()
    {
        $data = [100, 50, 25, 0];
        $test = \MathsUtils::percentile($data, 0.50);
        $this->assertEquals(37.5, $test);
        $test = \MathsUtils::percentile($data, 0.55);
        $this->assertEquals(33.75, $test);
    }

    /**
     * Test percentile function non numeric data
     */
    public function test_percentile_nonnumeric()
    {
        $data = [100, 50, null, 0];
        $test = \MathsUtils::percentile($data, 0.25);
        $this->assertEquals(0.0, $test);
    }

    /**
     * Test percentile function out of range
     */
    public function test_percentile_outofrange()
    {
        $data = [100, 50, 25, 0];
        $test = \MathsUtils::percentile($data, 101);
        $this->assertEquals(0.0, $test);
    }

    /**
     * Test percentile function non float percentile
     */
    public function test_percentile_nonfloat()
    {
        $data = [100, 50, 25, 0];
        $test = \MathsUtils::percentile($data, 25);
        $this->assertEquals(62.5, $test);
    }

    /**
     * Tests that the random number generator returns numbers within the expected bounds.
     *
     * @param mixed $min The lowest number to be generated.
     * @param mixed $max The highest number to be generated.
     * @param mixed $increment The increment between numbers.
     * @param int $decimals The number of decimal places that can be generated.
     *
     * @dataProvider data_gen_random_no
     */
    public function test_gen_random_no($min, $max, $increment, int $decimals)
    {
        $result = \MathsUtils::gen_random_no($min, $max, $increment, $decimals);

        // Test for the range.
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
    }

    /**
     * Data used to test that we generate random numbers correctly.
     *
     * @return array
     */
    public function data_gen_random_no(): array
    {
        return [
            'no increment' => [2, 2, 0, 0],
            'increment by 1' => [1, 10, 1, 0],
            'increment by integer' => [10, 100, 10, 0],
            'negative numbers' => [-100, -10, 10, 0],
            'increment by float' => [1, 9, 0.5, 1],
            'increment by small number' => [0, 1, 0.01, 2],
            'impossible max int' => [1, 6, 2, 0],
            'impossible max float' => [0, 1, 0.03, 2],
            'all floats' => [0.1, 0.8, 0.02, 2],
        ];
    }
}
