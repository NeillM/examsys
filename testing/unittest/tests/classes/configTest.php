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

use testing\unittest\unittest;

/**
 * Unit tests the for  config class.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group core
 */
class configTest extends unittest {
    /**
     * Tests check_type function in config class.
     *
     * @dataProvider dataCheckType
     */
    public function testCheckType(string $input, string $checktype, bool $expected) {
        $datatype = Config::check_type($input, $checktype);
        $this->assertEquals($expected, Config::check_type($input, $datatype));
    }

    /**
     * Data used to test check_type function checking BOOLEAN.
     *
     * @return array
     */
    public function dataCheckType(): array {
        return [
            'default' => ['', Config::BOOLEAN, true],
            'default' => ['somevalue', Config::BOOLEAN, true],
            'default' => ['1', Config::BOOLEAN, true],
            'default' => ['0', Config::BOOLEAN, true]
        ];
    }
}