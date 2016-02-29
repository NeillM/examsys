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
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Step definitions for interacting with web forms.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait forms {
  /**
   * Fill in a form field.
   *
   * @Given /^I set the field "([^"]*)" to "([^"]*)"$/
   *
   * @param string $field The name, id or label of the field
   * @param string $value The value the field should be set to
   * @throws PendingException
   */
  public function i_set_field($field, $value) {
    $element = $this->find('field', $field);
    if (is_null($element)) {
      throw new \Exception("The form field $field could not be found");
    }
    $element->setValue($value);
  }
}
