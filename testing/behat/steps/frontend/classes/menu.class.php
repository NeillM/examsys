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

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use testing\behat\selectors;
use Exception;

/**
 * Basic core step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait menu
{
    /**
     * Check for menu items.
     *
     * @Then /^I should see menu with following items:$/
     * @param TableNode $menuitems The menu's items
     * @throws Exception
     */
    public function i_should_see_menu_with_following_item(TableNode $menuitems)
    {

        if (empty($menuitems)) {
            throw new Exception('The menu element or its items list are empty');
        }
        foreach ($menuitems->getHash() as $menuitem) {
            $title = $menuitem['menu_items'];
            $element = $this->find('link', $title);
            if (empty($element)) {
                throw new Exception("$title is not in the menu");
            }
        }
    }

    /**
     * Check for menu section items.
     *
     * @Then I should see :menu_section menu section with following items
     * @param string $menu_section section title
     * @param TableNode $menuitems The menu's items
     * @throws Exception
     */

    public function i_should_see_menu_section_with_following_item($menu_section, TableNode $menuitems)
    {
        if (empty($menuitems) || empty($menu_section)) {
            throw new Exception('The menu name or items is empty');
        }
        foreach ($menuitems->getHash() as $menuitem) {
            $title = $menuitem['items'];
            //$element = $this->find('sub_menu', $title);
            $menuitem = $this->find('xpath', "//div[contains(concat(' ', normalize-space(@class), ' '), ' submenuheading ') and contains(normalize-space(.) , '" . $menu_section . "')]/following-sibling::div/div[contains(concat(' ', normalize-space(@class), ' '), ' menuitem ') and contains(normalize-space(.) , '" . $title  . "')]");
            if (empty($menuitem)) {
                throw new Exception('menu section item is not exist in the submenu');
            }
        }
    }

    /**
     * Check for submenu items.
     *
     * @Then /^I should see submenu with following items:$/
     * @param TableNode $menuitems The menu's items
     * @throws Exception
     */
    public function i_should_see_submenu_with_following_item(TableNode $menuitems)
    {

        if (empty($menuitems)) {
            throw new Exception('The submenu items list is empty');
        }
        foreach ($menuitems->getHash() as $menuitem) {
            $title = $menuitem['menu_items'];
            $element = $this->find('sub_menu', $title);
            if (empty($element)) {
                throw new Exception("$title is not in the submenu");
            }
        }
    }


    /**
     * Checks if topright menu is hiden.
     *
     * @Then /^(?:|I )should not see main menu$/
     */
    public function i_not_see_main_menu()
    {
        $node = null;
        if (!$node = $this->find('xpath', selectors::get_selectors('main_menu'))) {
            throw new Exception('Could not find main menu');
        }
        if ($node->isVisible()) {
            throw new Exception('Main menu should be not visible.');
        }
    }

    /**
     * Toggle the main menu.
     *
     * @Then /^(?:|I )toggle the main menu$/
     */
    public function toggle_main_menu()
    {
        $node = null;
        if (!$node = $this->find('xpath', selectors::get_selectors('main_menu_icon'))) {
            throw new Exception('Could not find main menu');
        }
        $node->click();
    }

    /**
     * Checks for main menu items.
     *
     * @Then /^I should see main menu with following items:$/
     * @param TableNode $menuitems The menu's items
     * @throws Exception
     */
    public function i_see_main_menu(TableNode $menuitems)
    {
        if (empty($menuitems)) {
            throw new Exception('The menu element or its items list are empty');
        }
        $toprightmenu = $this->find('xpath', "//div[contains(@id, 'toprightmenu') and contains(@style, 'display: block;')]");

        if (empty($toprightmenu)) {
            throw new Exception('Main menu is not found');
        }

        foreach ($menuitems->getHash() as $menuitem) {
            $title = $menuitem['Item'];
            $element = $this->find('main_menu_item', $title);
            if (empty($element)) {
                throw new Exception("$title is not in the submenu");
            }
        }
    }

    /**
     * Checks for popup search menu items.
     *
     * @Then /^I should see popup search menu with following items:$/
     * @param TableNode $menuitems The menu's items
     * @throws Exception
     */
    public function i_see_search_menu(TableNode $menuitems)
    {
        if (empty($menuitems)) {
            throw new Exception('The search menu element or its items list are empty');
        }
        $searchmenu = $this->find('xpath', selectors::get_selectors('search_menu'));

        if (empty($searchmenu)) {
            throw new Exception('popup search menu is not found');
        }

        foreach ($menuitems->getHash() as $menuitem) {
            $item = $menuitem['Item'];
            $element = $this->find('sub_search_menu_item', $item);
            if (empty($element)) {
                throw new Exception("$item in sub menu could not be found");
            }
        }
    }

    /**
     * Focus on a menu item.
     *
     * @When /^I focus on "([^"]*)" "([^"]*)"$/
     * @param string $text The text of the menu item
     * @param string $type The type of menu item (menu_item, popup_item, etc.)
     * @throws Exception
     */
    public function iFocusOnMenuItem($text, $type)
    {
        $element = $this->find($type, $text);
        if (empty($element)) {
            throw new Exception("Could not find $type with text '$text'");
        }
        // Find the focusable element (button or link) within the menu item
        $focusable = $element->find('css', 'button, a');
        if ($focusable) {
            $focusable->focus();
        } else {
            $element->focus();
        }
        // Don't wait here - let the step that checks for focus (e.g., itemShouldHaveFocus) wait for it
    }

    /**
     * Press a keyboard key.
     *
     * @When /^I press "([^"]*)" key$/
     * @param string $key The key to press (ArrowDown, ArrowUp, ArrowLeft, ArrowRight, Enter, Escape)
     * @throws Exception
     */
    public function iPressKey($key)
    {
        if (!$this->running_javascript()) {
            throw new Exception('This step requires JavaScript to be enabled');
        }

        $keyCodes = [
            'ArrowDown' => 40,
            'ArrowUp' => 38,
            'ArrowLeft' => 37,
            'ArrowRight' => 39,
            'Enter' => 13,
            'Escape' => 27,
        ];

        if (!isset($keyCodes[$key])) {
            throw new Exception("Unknown key: $key");
        }

        $keyCode = $keyCodes[$key];
        // Use JavaScript to dispatch keyboard event (Mink doesn't support arrow keys or :focus selector)
        $script = 'var focused = document.activeElement || document.body; '
            . "var e = new KeyboardEvent('keydown', { "
            . "key: '$key', code: '$key', keyCode: $keyCode, which: $keyCode, "
            . 'bubbles: true, cancelable: true }); '
            . 'focused.dispatchEvent(e);';
        $this->getSession()->executeScript($script);
        // Don't wait here - let the step that checks for the result (e.g., popupMenuShouldBeVisible) wait for it
    }

    /**
     * Check if popup menu is visible.
     *
     * @Then /^the popup menu should be visible$/
     * @throws Exception
     */
    public function popupMenuShouldBeVisible()
    {
        // Use spin() to wait for popup menu to appear, rather than fixed wait
        // This makes tests faster by waiting only until the condition is met, up to a maximum timeout
        $this->spin(function ($context) {
            $popups = $context->getSession()->getPage()->findAll('css', '.popup');
            foreach ($popups as $popup) {
                if ($popup->isVisible()) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Check if popup menu is not visible.
     *
     * @Then /^the popup menu should not be visible$/
     * @throws Exception
     */
    public function popupMenuShouldNotBeVisible()
    {
        // Use spin() to wait for popup menu to disappear, rather than fixed wait
        // This makes tests faster by waiting only until the condition is met, up to a maximum timeout
        $this->spin(function ($context) {
            $popups = $context->getSession()->getPage()->findAll('css', '.popup');
            foreach ($popups as $popup) {
                if ($popup->isVisible()) {
                    return false; // Still visible, keep waiting
                }
            }
            return true; // No visible popups found
        });
    }

    /**
     * Check if first popup item has focus.
     *
     * @Then /^the first popup item should have focus$/
     * @throws Exception
     */
    public function firstPopupItemShouldHaveFocus()
    {
        // Delegate to the generic method in basic.class.php
        $this->popupMenuItemByIndexShouldHaveFocus('1');
    }

    /**
     * Check if second popup item has focus.
     *
     * @Then /^the second popup item should have focus$/
     * @throws Exception
     */
    public function secondPopupItemShouldHaveFocus()
    {
        // Delegate to the generic method in basic.class.php
        $this->popupMenuItemByIndexShouldHaveFocus('2');
    }
}
