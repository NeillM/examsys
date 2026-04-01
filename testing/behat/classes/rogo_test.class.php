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

namespace testing\behat;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session;
use testing\datagenerator\loader;
use coding_exception;
use Exception;
use Config;
use WebDriver\Exception\JavaScriptError;
use WebDriver\Exception\NoSuchWindow;

/**
 * All ExamSys behat test definitions should extend this class if they wish to do browser based tests.
 *
 * It should contain only utility functions we wish all ExamSys
 * behat tests to have access to.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class rogo_test extends MinkContext
{
    /**
     * Get a data generator for adding information into the ExamSys database.
     *
     * @param string $name The name of the generator.
     * @param string $component The component the generator is from (optional).
     * @return \testing\datagenerator\generator
     * @throws \testing\datagenerator\not_found
     */
    protected function get_datagenerator($name, $component = 'core')
    {
        return loader::get($name, $component);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function locatePath($path)
    {
        // Get the base url for the site, ensure it has a trailing slash.
        $baseurl = rtrim((string) $this->getMinkParameter('base_url'), '/') . '/';
        if (mb_strpos($path, 'http') !== 0) {
            // The path is not a fully qualified url.
            $path = $baseurl . ltrim($path, '/');
        }
        return $path;
    }

    /**
     * Gets the page that the Mink session is viewing.
     *
     * @return \Behat\Mink\Element\DocumentElement
     */
    protected function get_page()
    {
        // Get the Mink session.
        $session = $this->getSession();
        // Get the current page.
        return $session->getPage();
    }

    /**
     * Tests if the correct prarmeters have been passed.
     *
     * @param string $selector selector engine name
     * @param string|array $locator selector locator
     * @return void
     * @throws coding_exception if the details are invalid
     * @throws exception If the selector type is not allowed
     */
    protected function validate_selector($selector, $locator)
    {
        if ($selector == 'named' or $selector == 'named_exact' or $selector == 'named_partial') {
            // The locator must be an array.
            if (!is_array($locator) and count($locator) !== 2) {
                throw new coding_exception('The locator for a named selector must be an aray with two values');
            }
            $name = $locator[0];
            if (!selectors::is_allowed_named($name)) {
                throw new Exception("The named selector $name is not enabled in ExamSys behat tests");
            }
        }
    }

    /**
     * Finds first element with specified selector inside the current element.
     *
     * @param string $name selector name
     * @param string $value the value to search for
     * @return \Behat\Mink\Element\NodeElement|null
     * @throws coding_exception
     * @throws exception If the element cannot be found
     *
     * @see \testing\behat\selectors for ExamSys specific selectors
     * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
     */
    public function find($name, $value)
    {
        if (selectors::is_allowed_named($name)) {
            $selector = 'named';
            $locator = [$name, $value];
        } else {
            $selector = $name;
            $locator = $value;
        }
        $page = $this->get_page();
        return $page->find($selector, $locator);
    }

    /**
     * Gets the attribute from the element
     * @param NodeElement $element the element
     * @param string $attribute the attribute
     * @return string
     */
    public function getAttribute(NodeElement $element, string $attribute): string
    {
        return $element->getAttribute($attribute);
    }
    /**
     * Checks if an element with the specified selector
     *
     * @param string $name selector name
     * @param string $value the value to search for
     * @return boolean
     *
     * @see \testing\behat\selectors for ExamSys specific selectors
     * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
     */
    public function has($name, $value)
    {
        if (selectors::is_allowed_named($name)) {
            $selector = 'named';
            $locator = [$name, $value];
        } else {
            $selector = $name;
            $locator = $value;
        }
        $page = $this->get_page();
        return $page->has($selector, $locator);
    }

    /**
     * Find all elements that match the criteria.
     *
     * @param string $name selector name
     * @param string $value the value to search for
     * @return \Behat\Mink\Element\NodeElement[]
     *
     * @see \testing\behat\selectors for ExamSys specific selectors
     * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
     */
    public function find_all($name, $value)
    {
        if (selectors::is_allowed_named($name)) {
            $selector = 'named';
            $locator = [$name, $value];
        } else {
            $selector = $name;
            $locator = $value;
        }
        $page = $this->get_page();
        return $page->findAll($selector, $locator);
    }

    /**
     * Detects errors, notices and warnings on a page.
     *
     * @param \Behat\Behat\Hook\Scope\AfterStepScope|null $event Should only be passed by the after step hook.
     * @throws \Exception
     * @throws \Behat\Mink\Exception\DriverException
     */
    public function lookForErrors(?AfterStepScope $event = null): void
    {
        $message = '';
        // First look for system errors recorded in the database.
        /* @var Config $config */
        $config = \Config::get_instance();
        $result = $config->db->query('SELECT id, errtype, errstr, errfile, errline, backtrace FROM sys_errors');
        if ($result->num_rows > 0) {
            $message .= "The following errors have been found:\n";
            while ($row = $result->fetch_assoc()) {
                $message .= "{$row['errtype']} {$row['errstr']} in {$row['errfile']} on line {$row['errline']}:\n{$row['backtrace']}\n\n";
            }
        }

        // Regular expressions for detecting errors, notices and the like.
        $fatal = "//*[contains(., 'Fatal error: ') and contains(., ' on line ')]";
        $error = "//*[contains(., 'Error: ') and contains(., ' on line ')]";
        $warning = "//*[contains(., 'Warning: ') and contains(., ' on line ')]";
        $notice = "//*[contains(., 'Notice: ') and contains(., ' on line ')]";
        $deprecation = "//*[contains(., 'Deprecated: ') and contains(., ' on line ')]";
        try {
            if ($this->getSession()->getDriver()->find($fatal)) {
                $message .= "Fatal error found on page.\n";
            }
            if ($this->getSession()->getDriver()->find($error)) {
                $message .= "Error found on page.\n";
            }
            if ($this->getSession()->getDriver()->find($warning)) {
                $message .= "Warning found on page.\n";
            }
            if ($this->getSession()->getDriver()->find($notice)) {
                $message .= "Notice found on page.\n";
            }
            if ($this->getSession()->getDriver()->find($deprecation)) {
                $message .= "Deprecation found on page.\n";
            }
        } catch (\Behat\Mink\Exception\UnsupportedDriverActionException) {
            // Nothing we can do about this.
        } catch (\WebDriver\Exception\NoSuchWindow) {
            // The action caused the window to close, so we cannot see any errors.
        } catch (\Behat\Mink\Exception\DriverException) {
            // The driver could not do a search for some reason, a browser may not be open right now.
        }

        if (!empty($message)) {
            if (!is_null($event)) {
                // We need to take the screenshot here if called in the post step otherwise it is bypassed because of the exception.
                $this->takeScreenshot($event);
            }
            throw new Exception($message);
        }
    }

    /**
     * Waits for an action to be true.
     *
     * @param callable $lambda
     * @return boolean
     * @throws Exception
     */
    public function spin($lambda)
    {
        $timeout = self::getTimeout();
        $start = microtime(true);
        $end = $start + $timeout;

        do {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception) {
                // do nothing
            }

            if (!$this->running_javascript()) {
                break;
            }

            usleep(100000);
        } while (microtime(true) < $end);

        $backtrace = debug_backtrace();
        $message = 'Timeout thrown by ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . '()';
        if (isset($backtrace[1]['file'])) {
            $message .= ' in ' . $backtrace[1]['file'] . ', line ' . $backtrace[1]['line'];
        }
        throw new Exception($message);
    }

    /**
     * Returns whether the scenario is running in a browser that can run Javascript or not.
     *
     * @return boolean
     */
    public function running_javascript()
    {
        return $this->getSession()->getDriver()::class !== \Behat\Mink\Driver\BrowserKitDriver::class;
    }

    /**
     * Gets the base asset directory for behat test fixtures.
     *
     * @return string
     */
    public function getAssetPath(): string
    {
        return environment::get_basedir() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    }

    /**
     * Sets files_path for the test
     * @param string $path location of test upload assets
     */
    public function setFilesPath(string $path): void
    {
        $this->setMinkParameter(
            'files_path',
            $this->getAssetPath() . $path
        );
    }

    /**
     * Gets the locale of the current browser.
     *
     * It should be used when the locale will make a difference to what we need to do
     * to make a step work correctly.
     *
     * If a browser does not support JavaScript a fake locale of 'no-javascript' will be returned.
     * I expect this will only happen if the Goutte driver is used. I think in this driver the
     * actions we need to do will also have a specific form (i.e. the formats of dates in inputs look
     * as though they will be the same as the input)
     *
     * @return string
     */
    protected function getBrowserLocale(): string
    {
        $session = $this->getSession();
        if (!self::runningJavascriptInSession($session)) {
            // Making an assumption of the locale of non-JavaScript based drivers.
            return 'no-javascript';
        }
        return $session->evaluateScript('return navigator.language || navigator.userLanguage;');
    }

    /**
     * Waits for all the JS to be loaded.
     * Wait for JS copied from https://github.com/moodle/moodle/blob/master/lib/behat/classes/behat_session_trait.php
     *
     * @return  bool Whether any JS is still pending completion.
     */
    public function waitForPendingJs()
    {
        $session = $this->getSession();
        return static::waitForPendingJsInSession($session) and static::waitForRunningJsInSession($session);
    }

    /**
     * Waits for all the JS to be loaded.
     * @param   Session $session The Mink Session where JS can be run
     * @return  bool Whether any JS is still pending completion.
     */
    public static function waitForPendingJsInSession(Session $session)
    {
        if (!self::runningJavascriptInSession($session)) {
            // JS is not available therefore there is nothing to wait for.
            return false;
        }

        // We don't use rogo_test::spin() here as we don't want to end up with an exception
        // if the page & JSs don't finish loading properly.
        for ($i = 0; $i < self::getExtendedTimeout() * 10; $i++) {
            try {
                $js = <<<JS
                    return (function() {
                        if (document.readyState !== "complete") {
                            return "incomplete";
                        }
                        return "";
                    })()
                JS;
                $jscode = trim((string) preg_replace('/\s+/', ' ', $js));
                $pending = self::evaluateScriptInSession($session, $jscode);
            } catch (NoSuchWindow) {
                // We catch an exception here, in case we just closed the window we were interacting with.
                // No javascript is running if there is no window right?
                $pending = '';
            } catch (JavaScriptError $e) {
                throw new \coding_exception('JavaScript in waitForPendingJsInSession failed: ' . $e->getMessage());
            }

            // If there are no pending JS we stop waiting.
            if ($pending === '') {
                return true;
            }

            // 0.1 seconds.
            usleep(100000);
        }

        // Timeout waiting for JS to complete.
        // It is unlikely that Javascript code of a page or an AJAX request needs more than
        // getExtendedTimeout() seconds to be loaded.
        throw new \Exception(
            'Javascript code and/or AJAX requests are not ready after '
             . self::getExtendedTimeout()
             . ' seconds. There is a Javascript error or the code is extremely slow (' . $pending
             . '). If you are using a slow machine, consider setting increasetimeout in behat config.'
        );
    }

    /**
     * Waits for all the JS to finish.
     *
     * @param   Session $session The Mink Session where JS can be run
     * @return  bool Whether any JS is still pending completion.
     */
    public static function waitForRunningJsInSession(Session $session)
    {
        if (!self::runningJavascriptInSession($session)) {
            // JS is not available therefore there is nothing to wait for.
            return false;
        }

        // We don't use rogo_test::spin() here as we don't want to end up with an exception
        // if the page & JSs don't finish loading properly.
        for ($i = 0; $i < self::getExtendedTimeout() * 10; $i++) {
            try {
                $js = <<<JAVASCRIPT
                    return (function() {
                        if (typeof ROGO !== 'object' || typeof ROGO.pendingJS === 'undefined') {
                            return '';
                        }

                        let returnvalue = '';

                        if (ROGO.pendingJS.isJSRunning()) {
                            returnvalue = ROGO.pendingJS.listRunningJS();
                        }

                        return returnvalue;
                    })()
                JAVASCRIPT;
                $jscode = trim((string) preg_replace('/\s+/', ' ', $js));
                $pending = self::evaluateScriptInSession($session, $jscode);
            } catch (NoSuchWindow) {
                // We catch an exception here, in case we just closed the window we were interacting with.
                // No javascript is running if there is no window right?
                $pending = '';
            } catch (JavaScriptError $e) {
                throw new \coding_exception('JavaScript in waitForRunningJsInSession failed: ' . $e->getMessage() . "\n\n" . $jscode);
            }

            // If there are no pending JS we stop waiting.
            if ($pending === '') {
                return true;
            }

            // 0.1 seconds.
            usleep(100000);
        }

        // Timeout waiting for JS to complete.
        // It is unlikely that JavaScript code of a page or an AJAX request needs more than
        // getExtendedTimeout() seconds to be loaded.
        throw new \Exception(
            'Javascript code and/or AJAX requests are still running after '
            . self::getExtendedTimeout()
            . ' seconds. There is a Javascript error or the code is extremely slow (' . $pending
            . '). If you are using a slow machine, consider setting increasetimeout in behat config.'
        );
    }

    /**
     * Whether Javascript is available in the specified Session.
     *
     * @param Session $session
     * @return boolean
     */
    protected static function runningJavascriptInSession(Session $session): bool
    {
        return $session->getDriver()::class !== \Behat\Mink\Driver\BrowserKitDriver::class;
    }

    /**
     * Gets the extended timeout.
     *
     * A longer timeout for cases where the normal timeout is not enough.
     *
     * @return int Timeout in seconds
     */
    public static function getExtendedTimeout(): int
    {
        return self::getRealTimeout(30);
    }

    /**
     * Gets the default timeout.
     *
     * The timeout for each Behat step (load page, wait for an element to load...).
     *
     * @return int Timeout in seconds
     */
    public static function getTimeout(): int
    {
        return self::getRealTimeout(15);
    }

    /**
     * Gets the required timeout in seconds.
     *
     * @param int $timeout One of the TIMEOUT constants
     * @return int Actual timeout (in seconds)
     */
    protected static function getRealTimeout(int $timeout): int
    {
        $cfg_behat_increasetimeout = Config::get_instance()->get('cfg_behat_increasetimeout');
        if (isset($cfg_behat_increasetimeout)) {
            return $timeout * $cfg_behat_increasetimeout;
        } else {
            return $timeout;
        }
    }

    /**
     * Evaluate the supplied script in the specified session, returning the result.
     *
     * @param Session $session
     * @param string $script
     * @return mixed
     */
    public static function evaluateScriptInSession(Session $session, string $script)
    {
        self::requireJavascriptInSession($session);

        return $session->evaluateScript($script);
    }

    /**
     * Require that javascript be available for the specified Session.
     *
     * @param Session $session
     * @param null|string $message An additional information message to show when JS is not available
     * @throws DriverException
     */
    protected static function requireJavascriptInSession(Session $session, ?string $message = null): void
    {
        if (self::runningJavascriptInSession($session)) {
            return;
        }

        $error = 'Javascript is required for this step.';
        if ($message) {
            $error = "{$error} {$message}";
        }
        throw new DriverException($error);
    }
}
