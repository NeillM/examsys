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
use testing\behat\selectors;
use Exception;

/**
 * Statistics core step definitions.
 *
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait Statistics
{

    /**
     * Check for school statistic items.
     *
     * @Then I should see :school school statistics:
     * @param string $school the school
     * @param TableNode $data the stats
     * @throws Exception
     */
    public function iShouldSeeSchoolStatistics(string $school, TableNode $data)
    {
        if (empty($data)) {
            throw new Exception('The data list is empty');
        }
        $fields = $data->getRowsHash();
        $formative = $this->find('xpath', '//tr[@id="' . $school . '"]/td[1]')->getText();
        $progress = $this->find('xpath', '//tr[@id="' . $school . '"]/td[2]')->getText();
        $summative = $this->find('xpath', '//tr[@id="' . $school . '"]/td[3]')->getText();
        $survey = $this->find('xpath', '//tr[@id="' . $school . '"]/td[4]')->getText();
        $osce = $this->find('xpath', '//tr[@id="' . $school . '"]/td[5]')->getText();
        $offline = $this->find('xpath', '//tr[@id="' . $school . '"]/td[6]')->getText();
        $peer = $this->find('xpath', '//tr[@id="' . $school . '"]/td[7]')->getText();
        if ($formative != $fields['formative']) {
            throw new Exception('Incorrect number of formative papers');
        }
        if ($progress != $fields['progress']) {
            throw new Exception('Incorrect number of progress papers');
        }
        if ($summative != $fields['summative']) {
            throw new Exception('Incorrect number of summative papers');
        }
        if ($survey != $fields['survey']) {
            throw new Exception('Incorrect number of survey papers');
        }
        if ($osce != $fields['osce']) {
            throw new Exception('Incorrect number of osce papers');
        }
        if ($offline != $fields['offline']) {
            throw new Exception('Incorrect number of offline papers');
        }
        if ($peer != $fields['peer']) {
            throw new Exception('Incorrect number of peer review papers');
        }
    }
}
