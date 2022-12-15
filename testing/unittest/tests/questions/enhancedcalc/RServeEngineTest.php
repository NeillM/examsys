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

use plugins\questions\enhancedcalc\Engine as BaseEngine;
use plugins\questions\enhancedcalc\engine\rrserve\Engine;

require_once('EngineTest.php');

/**
 * Test the Rserve calculation engine does maths correctly.
 *
 * The tests themselves come from the parent class, as the behaviour must be the same for all engines.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2022 onwards The University of Nottingham
 * @package tests
 * @group questions
 */
class RServeEngineTest extends EngineTest
{
    /**
     * Connects to RServe if possible.
     *
     * @return \plugins\questions\enhancedcalc\Engine
     */
    protected function getEngine(): BaseEngine
    {
        // We failed to connect previously.
        if ($this->engine === false) {
            $this->markTestSkipped('RServe Not available');
        }

        // Do we have a working connection.
        if (!empty($this->engine)) {
            return $this->engine;
        }

        // Try connecting to localhost.
        $localhostconfig = [
            'host' => 'localhost',
            'port' => '6311',
            'timeout' => '5',
        ];
        $localengine = new Engine($localhostconfig);
        if ($localengine->connect()) {
            $this->engine = $localengine;
            return $this->engine;
        }

        // Allow the new attempt to work.
        Engine::resetConnection();

        // Try using docker.
        $dockerconfig = [
            'host' => 'calc',
            'port' => '6311',
            'timeout' => '5',
        ];
        $dockerengine = new Engine($dockerconfig);
        if ($dockerengine->connect()) {
            $this->engine = $dockerengine;
            return $this->engine;
        }

        // We could not connect.
        $this->engine = false;
        $this->markTestSkipped('RServe Not available');
    }

    /**
     * Ensure that there are no cached connections.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        Engine::resetConnection();
        parent::tearDownAfterClass();
    }
}
