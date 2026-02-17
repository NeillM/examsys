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
        $this->spin(function ($context) {
            $popups = $context->getSession()->getPage()->findAll('css', '.popup');
            foreach ($popups as $popup) {
                if ($popup->isVisible()) {
                    return true;
                }
            }
            return false;
        });
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
     * Check if no popup menus are visible.
     *
     * @Then /^no popup menus should be visible$/
     * @throws Exception
     */
    public function popupMenuShouldNotBeVisible()
    {
        // Wait for focus to be set; spin avoids a fixed sleep.
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
     * Check if a popup menu item has focus by text.
     *
     * @Then /^the "([^"]*)" popup menu item should have focus$/
     * @param string $text The text of the popup menu item
     * @throws Exception
     */
    public function popupMenuItemShouldHaveFocus($text)
    {
        if (!$this->running_javascript()) {
            throw new Exception('This step requires JavaScript to be enabled');
        }

        // Use spin() to wait for popup to be visible and item to have focus
        // This makes tests faster by waiting only until the condition is met, up to a maximum timeout
        $this->spin(function ($context) use ($text) {
            $popups = $context->getSession()->getPage()->findAll('css', '.popup');
            $popup = null;
            foreach ($popups as $p) {
                if ($p->isVisible()) {
                    $popup = $p;
                    break;
                }
            }
            if (!$popup) {
                return false;
            }
            $popupItems = $popup->findAll('css', '.popupitem');
            $foundItem = null;
            foreach ($popupItems as $item) {
                $itemText = trim($item->getText());
                if (stripos($itemText, $text) !== false) {
                    $foundItem = $item;
                    break;
                }
            }
            if (!$foundItem) {
                return false;
            }
            // Check focus using JavaScript.
            $itemXpath = addslashes($foundItem->getXpath());
            $script = <<<JS
(function() {
    var focused = document.activeElement;
    if (!focused) return false;
    var item = document.evaluate('{$itemXpath}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    return item && (focused === item || item.contains(focused));
})();
JS;
            return $context->getSession()->evaluateScript($script);
        });
    }

    /**
     * Check if a popup menu item has focus by index (1-based).
     *
     * @Then /^item "([^"]*)" in the popup menu should have focus$/
     * @param string $index The 1-based index of the popup menu item
     * @throws Exception
     */
    public function popupMenuItemByIndexShouldHaveFocus($index)
    {
        if (!$this->running_javascript()) {
            throw new Exception('This step requires JavaScript to be enabled');
        }

        // Wait for focus to be set; spin avoids a fixed sleep.
        $this->spin(function ($context) use ($index) {
            $popups = $context->getSession()->getPage()->findAll('css', '.popup');
            $popup = null;
            foreach ($popups as $p) {
                if ($p->isVisible()) {
                    $popup = $p;
                    break;
                }
            }
            if (!$popup) {
                return false;
            }
            $popupItems = $popup->findAll('css', '.popupitem');
            $itemIndex = (int) $index - 1; // Convert to 0-based index
            if ($itemIndex < 0 || $itemIndex >= count($popupItems)) {
                return false;
            }
            $item = $popupItems[$itemIndex];
            // Check focus using JavaScript.
            $itemXpath = addslashes($item->getXpath());
            $script = <<<JS
(function() {
    var focused = document.activeElement;
    if (!focused) return false;
    var item = document.evaluate('{$itemXpath}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    return item && (focused === item || item.contains(focused));
})();
JS;
            return $context->getSession()->evaluateScript($script);
        });
    }
}
