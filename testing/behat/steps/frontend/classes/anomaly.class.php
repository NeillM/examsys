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

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use testing\behat\selectors;
use Exception;

/**
 * Anomaly report manipulation step definitions.
 *
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait Anomaly
{
    /**
     * Checks the anomaly details for a user
     *
     * @Given I should see :user clock anomaly
     * @param string $user the user
     */
    public function iShouldSeeClockAnomaly(string $user): void
    {
        $selector = 'clock_anomaly';
        $this->i_should_see($user, $selector);
    }

    /**
     * Checks for missing anomaly details for a user
     *
     * @Given I should not see :user clock anomaly
     * @param string $user the user
     */
    public function iShouldNotSeeClockAnomaly(string $user): void
    {
        $selector = 'clock_anomaly';
        $this->i_should_not_see($user, $selector);
    }
}
