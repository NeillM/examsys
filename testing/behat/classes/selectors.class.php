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

namespace testing\behat;

/**
 * Used to define things that behat can select in Rogo
 * 
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class selectors {
  /**
   * An array of selector types that can be used by behat tests,
   * unless built into behat directly they should also have an 
   * entry in self::$rogoselectors.
   *
   * @var array
   */
  protected static $allowedrogoselectors = array(
    // Built in selectors.
    'id' => 'id',
    'id_or_name' => 'id_or_name',
    'link' => 'link',
    'button' => 'button',
    'link_or_button' => 'link_or_button',
    'content' => 'content',
    'field' => 'field',
    'select' => 'select',
    'checkbox' => 'checkbox',
    'radio' => 'radio',
    'file' => 'file',
    'optgroup' => 'optgroup',
    'option' => 'option',
    'fieldset' => 'fieldset',
    'table' => 'table',
    // Rogo selectors.
    'menu_item' => 'menu_item',
  );

  /**
   * An array containing XPATH selectors for elements of Rogo that behat can select.
   * The key is the name of the selector, the value the XPATH string describing it.
   * 
   * @var array 
   */
  protected static $rogoselectors = array(
    'menu_item' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' menuitem ')]/a
XPATH
  );

  /**
   * Get the custom Rogo selector list.
   * 
   * @return array
   */
  public static function get_selectors() {
    return self::$rogoselectors;
  }

  /**
   * Checks if the the named selector is allowed in Rogo behat tests.
   *
   * @param string $namesselector
   * @return boolean
   */
  public static function is_allowed_named($namesselector) {
    return isset(self::$allowedrogoselectors[$namesselector]);
  }

  /**
   * Adds the custom Rogo selectors to behat.
   *
   * @param \testing\behat\Behat\Mink\Session $session The mink session
   * @return void
   */
  public static function register_rogo_selectors(Behat\Mink\Session $session) {
    foreach (self::get_selectors() as $name => $xpath) {
      $session->getSelectorsHandler()->getSelector('named_exact')->registerNamedXpath($name, $xpath);
      $session->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath($name, $xpath);
    }
  }
}
