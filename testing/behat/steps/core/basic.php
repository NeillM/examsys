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

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step\Given,
    Behat\Behat\Context\Step\When,
    Behat\Behat\Context\Step\Then,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use testing\behat\rogo_test;

/**
 * Basic core step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
class core_basic extends rogo_test {
  /**
   * Click on an element on the page.
   *
   * @Given /^I click "([^"]*)" "([^"]*)"$/
   */
  public function i_click($name, $selector) {
    $element = $this->find($selector, $name);
    if (is_null($element)) {
      throw new Exception("The $selector with the value of $name could not be found");
    }
    $element->click();
  }

  /**
   * @Then /^I should see "([^"]*)" "([^"]*)"$/
   */
  public function i_should_see($content, $selector) {
    $element = $this->find($selector, $content);
    if (is_null($element)) {
      throw new Exception("The $selector with the value of $name could not be found");
    }
    if ($this->running_javascript() and !$element->isVisible()) {
      throw new Exception("The $selector with the value of $name is hidden");
    }
  }
}

