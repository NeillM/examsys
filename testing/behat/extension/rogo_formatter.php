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

use Behat\Behat\Formatter\ProgressFormatter;
use Behat\Behat\Event\SuiteEvent;

/**
 *
 */
class rogo_formatter extends ProgressFormatter {
  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * @return array The event names to listen to
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events['beforeSuite'] = 'beforeSuite';
    return $events;
  }

  /**
   * Output some information about the tests that are going to be run.
   *
   * @param SuiteEvent $event
   */
  public function beforeSuite(SuiteEvent $event) {
    $this->writeln('Hello Rogo. the tests are starting');
  }
}
