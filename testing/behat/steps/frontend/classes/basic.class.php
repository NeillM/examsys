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
use testing\behat\rogo_test;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Exception;

/**
 * Basic core step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait basic
{
    /**
     * Click on an element on the page.
     *
     * @Given /^I click "([^"]*)" "([^"]*)"$/
     * @param string $name The value to be searched for
     * @param string $selector The type of selector
     * @throws Exception
     */
    public function i_click($name, $selector)
    {
        $element = $this->find($selector, $name);
        if (is_null($element)) {
            throw new \Exception("The \"$selector\" with the value of \"$name\" could not be found");
        }
        $element->click();
    }

    /**
     * Checks for the presense of text.
     *
     * @Then /^I should see "([^"]*)" "([^"]*)"$/
     * @param string $content
     * @param string $selector
     * @throws Exception
     */
    public function i_should_see($content, $selector)
    {
        $element = $this->find($selector, $content);
        if (is_null($element)) {
            throw new \Exception("The \"$selector\" with the value of \"$content\" could not be found");
        }
        if ($this->running_javascript() and !$element->isVisible()) {
            throw new \Exception("The \"$selector\" with the value of \"$content\" is hidden");
        }
    }

    /**
     * Checks that text is not visible to the user
     *
     * @Then /^I should not see "([^"]*)" "([^"]*)"$/
     * @param string $content
     * @param string $selector
     * @return void
     * @throws Exception
     */
    public function i_should_not_see($content, $selector)
    {
        $element = $this->find($selector, $content);
        if (is_null($element)) {
            // Element is not present at all so all is good.
            return;
        }
        if ($this->running_javascript() and !$element->isVisible()) {
            // The element is present but hidden from the user.
            return;
        }
        throw new \Exception("The \"$selector\" with the value of \"$content\" is visibile");
    }
    
    /**
     * Keep browser live, for debuging
     *
     * @Given /^I pause "(?P<seconds_number>\d+)" seconds$/
     * @param int $seconds
     */
    public function i_wait_seconds($seconds)
    {
        $this->getSession()->wait($seconds * 1000, false);
    }

    /**
     * Sets focus to the names popup window.
     *
     * @And I focus :name popup
     * @param string $name
     * @return void
     */
    public function i_focus_popup($name)
    {
        $session = $this->getSession();
        $windows = $session->getDriver()->getWindowNames();

        foreach ($windows as $window) {
            $session->switchToWindow($window);
            $title = $session->getDriver()->getWebDriverSession()->title();
            if (trim($title) === trim($name)) {
                return;
            }
        }
        throw new Exception("Popup '$name' not found");
    }

    /**
     * Sets the focus to the main Rogo screen away from any popups.
     *
     * @And I focus main window
     */
    public function i_focus_main_window()
    {
        $session = $this->getSession();
        if (is_null($this->mainwindow)) {
            throw new Exception('Main window not set');
        }
        $session->switchToWindow($this->mainwindow);
    }
  
    /**
     * Check there is a popup present.
     *
     * @Then I should see popup page with title :title
     * @param String $title The page title
     * @throws Exception
     */
    public function i_see_popup_page($title)
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session, $title) {
            $current = $session->getDriver()->getWebDriverSession()->window_handle();
            $windows = $session->getDriver()->getWindowNames();
            foreach ($windows as $window) {
                $session->switchToWindow($window);
                $name = $session->getDriver()->getWebDriverSession()->title();
                if (trim($title) === trim($name)) {
                    return true;
                }
            }
            $session->switchToWindow($current);
            return false;
        });
    }

    /**
     * Tests that only the main window is open.
     *
     * @And only main window should be open
     * @throws Exception
     */
    public function only_main_window()
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session) {
            $windows = $session->getDriver()->getWindowNames();
            if (count($windows) === 1 and $windows[0] === $context->mainwindow) {
                return true;
            }
            return false;
        });
    }

    /**
     * Checks a popup was not found.
     *
     * @And I should not see popup page with title :title
     * @param type $title
     */
    public function i_should_not_see_popup($title)
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session, $title) {
            $current = $session->getDriver()->getWebDriverSession()->window_handle();
            $windows = $session->getDriver()->getWindowNames();
            foreach ($windows as $window) {
                $session->switchToWindow($window);
                $name = $session->getDriver()->getWebDriverSession()->title();
                if (trim($title) === trim($name)) {
                    return false;
                }
            }
            $session->switchToWindow($current);
            return true;
        });
    }
  
    /**
     * Close popup window back to main window
     *
     * @Then I close popup window :title
     * @throws Exception
     */
    public function i_close_popup_window($title)
    {
        $session = $this->getSession();
        $windows = $session->getDriver()->getWindowNames();

        if (empty($windows)) {
            throw new Exception('No windows open');
        }
        if (count($windows) === 1) {
            throw new Exception('No popup windows open');
        }
        $closed = false;
        for ($i = 1; $i < count($windows); $i++) {
            $this->getSession()->switchToWindow($windows[$i]);
            $name = $session->getDriver()->getWebDriverSession()->title();
            if (trim($name) === trim($title)) {
                $this->getSession()->executeScript('window.close()');
                $closed = true;
            }
        }
        if (!$closed) {
            throw new Exception("Popup with title '$title' not found");
        }
        $this->getSession()->switchToWindow($windows[0]);
    }
  
  
    /**
     * Check the page
     *
     * @Then /^I should see page with title "([^"]*)"$/
     * @param String $title The page title
     * @throws Exception
     */
    public function i_see_page_title($title)
    {
        $pagetitle = $this->find('xpath', "//div[@class='page_title']")->getText();
        if (strpos($pagetitle, $title) === false) {
            throw new Exception('The page could not be found');
        }
    }

    /**
     * Check table content
     *
     * @Then /^I should see table with:$/
     *
     * Asserts that a table exists with specified values.
     * The table header needs to have the number of the column to which the values belong,
     * all the other text is optional, normaly using 'Column' for easier understanding:
     *
     *      | Column 1 | Column 2 | Column 4 |
     *      | Value A  | Value B  | Value D  |
     *      ...
     *      | Value I  | Value J  | Value L  |
     */
    public function i_see_table_with(TableNode $table)
    {
        $rows = $table->getRows();
        $headers = array_shift($rows);
        $max = count($headers); //number of columns in table
        foreach ($rows as $row) {
            for ($i = 1; $i <= $max; $i++) {
                $text = array_shift($row);
                $foundRows = $this->get_table_row($text, $i, "table[@id='maindata']");
                if (!$foundRows) {
                    throw new Exception('the table row could not been found');
                }
            }
        }
    }

    /**
     * Find a(all) table row(s) that match the column text
     *
     * @param string        $text       Text to be found
     * @param int           $columnnumber     In which column the text should be found
     * @param string        $tableXpath If there is a specific table
     *
     * @return \Behat\Mink\Element\NodeElement[]
     */
    public function get_table_row($text, $columnnumber, $tableXpath)
    {
        // check column
        if (!empty($columnnumber)) {
            if (is_integer($columnnumber)) {
                $column = "[$columnnumber]";
            } else {
                return false;
            }
        } else {
            return false;
        }

        $dd = $this->find('xpath', "//$tableXpath/thead/tr/th$column");
        $ww = $this->find('xpath', "//$tableXpath/tbody/tr/td" . $column . "[text()='$text']");
        if (!empty($dd) && !empty($ww)) {
            return true;
        }
        return false;
    }

    /**
     * Click on an admin tool.
     *
     * @When /^I click admin tool "([^"]*)"$/
     * @param string $name The value to be searched for
     * @throws Exception
     */
    public function i_click_admin_tool($name)
    {
        $elements = $this->find_all('xpath', "//div[@class='container' and contains(text(), '$name')]");
        $elements[0]->click();
    }

    /**
     * Waits for the Rogo page in the focused window to load.
     *
     * @And I wait for page to load
     */
    public function i_wait_for_page_to_load()
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session) {
            try {
                // Now try testing via Javascript if the page is in a loaded state.
                $js = <<<JS
return document.readyState === 'complete'
JS;
                if ($session->evaluateScript($js)) {
                    // The status code indicates the page is fully loaded.
                    return true;
                }
            } catch (UnsupportedDriverActionException $ex) {
                // Javascript evaluation is not supported so try looking at the last http status code.
                try {
                    // Try testing for a response code of 200 (Any other code is not loaded)
                    if ($session->getStatusCode() === 200) {
                        // The status code indicates the page returned content successfully.
                        return true;
                    }
                } catch (UnsupportedDriverActionException $ex) {
                    // All methods of determining if the page is fully loaded are not supported,
                    //  so we must assume it is and hope for the best.
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Check javascript popup message
     *
     * @Then /^(?:|I )should see "([^"]*)" in popup$/
     *
     * @param string $message The message.
     *
     * @return bool
     */
    public function assert_popup_message($message)
    {
        return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
    }

    /**
     * Confirm a javascript popup window, click OK/Yes button
     *
     * @Then /^(?:|I )confirm the popup$/
     */
    public function confirm_popup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * Cancel a javascript popup window, click No/Cancel button
     *
     * @Then /^(?:|I )cancel the popup$/
     */
    public function cancel_popup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }
}
