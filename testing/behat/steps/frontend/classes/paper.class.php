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

    Behat\Gherkin\Node\TableNode,
    Behat\Behat\Tester\Exception\PendingException,
    testing\behat\selectors,
    Exception;

/**
 * Paper creation and manipulation step definitions.
 *
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait paper
{
    /**
     * Creates a new paper when the user is on a module page.
     *
     * @Given I create a new :type paper:
     * @param string $type The type of paper
     * @param TableNode $data The parameters used to create the paper
     */
    public function i_create_a_new_paper($type, TableNode $data)
    {
        $this->i_click('New Paper', 'link');
        $this->i_focus_popup('Rogō: Create new Paper');
        switch ($type) {
            case 'formative':
                $this->create_formative($data);
                break;
            case 'offline':
                $this->create_offline($data);
                break;
            case 'osce':
                $this->create_osce($data);
                break;
            case 'peer review':
                $this->create_peer_review($data);
                break;
            case 'progress':
                $this->create_progress($data);
                break;
            case 'summative':
                $this->create_summative($data);
                break;
            case 'survey':
                $this->create_survey($data);
                break;
            default:
                throw new PendingException("No handler for creating $type papers");
        }
        $this->i_click('Finish', 'button');
        $this->only_main_window();
        $this->i_focus_main_window();
        $this->i_wait_for_page_to_load();
    }

    /**
     * Creates a formative paper.
     *
     * @param TableNode $data
     */
    protected function create_formative(TableNode $data)
    {
        $fields = $data->getRowsHash();
        $this->paper_basics('Formative Self-Assessment', $fields['name']);
        // Set the start date.
        $this->fill_in_date_time('f', $fields['start']);
        // Set the end date.
        $this->fill_in_date_time('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fill_in_timezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Creates a offline paper.
     *
     * @param TableNode $data
     */
    protected function create_offline(TableNode $data)
    {
        $fields = $data->getRowsHash();
        $this->paper_basics('Offline Paper', $fields['name']);
        // Set the start date.
        $this->fill_in_date_time('f', $fields['start']);
        // Set the end date.
        $this->fill_in_date_time('t', $fields['end']);
        if (isset($fields['session'])) {
            $this->fill_in_session($fields['session']);
        }
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fill_in_timezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Creates a osce paper.
     *
     * @param TableNode $data
     */
    protected function create_osce(TableNode $data)
    {
        $fields = $data->getRowsHash();
        $this->paper_basics('OSCE Station', $fields['name']);
        // Set the start date.
        $this->fill_in_date_time('f', $fields['start']);
        // Set the end date.
        $this->fill_in_date_time('t', $fields['end']);
        if (isset($fields['session'])) {
            $this->fill_in_session($fields['session']);
        }
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fill_in_timezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Creates a peer review paper.
     *
     * @param TableNode $data
     */
    protected function create_peer_review(TableNode $data)
    {
        $fields = $data->getRowsHash();
        $this->paper_basics('Peer Review', $fields['name']);
        // Set the start date.
        $this->fill_in_date_time('f', $fields['start']);
        // Set the end date.
        $this->fill_in_date_time('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fill_in_timezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Creates a progress paper.
     *
     * @param TableNode $data
     */
    protected function create_progress(TableNode $data)
    {
        $fields = $data->getRowsHash();
        $this->paper_basics('Progress Test', $fields['name']);
        // Set the start date.
        $this->fill_in_date_time('f', $fields['start']);
        // Set the end date.
        $this->fill_in_date_time('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fill_in_timezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Creates a summative paper.
     *
     * @param TableNode $data
     */
    protected function create_summative(TableNode $data)
    {
        $config = \Config::get_instance();
        $management = $config->get_setting('core', 'cfg_summative_mgmt');
        $fields = $data->getRowsHash();
        $this->paper_basics('Summative Exam', $fields['name']);
        if ($management) {
            $this->fill_in_session($fields['session']);
            $this->fillField('barriers_needed', ($fields['barriers'] == 1));
            $this->fill_in_duration($fields['duration']);
            $this->fill_in_date_required($fields['period']);
            $this->fillField('cohort_size', $fields['size']);
            $this->fillField('sittings', $fields['sittings']);
            $this->fillField('campus', $fields['campus']);
            if (isset($fields['notes'])) {
                $this->fillField('notes', $fields['notes']);
            }
        } else {
            // Set the start date.
            $this->fill_in_date_time('f', $fields['start']);
            // Set the end date.
            $this->fill_in_date_time('t', $fields['end']);
            // Set optional fields.
            if (isset($fields['session'])) {
                $this->fill_in_session($fields['session']);
            }
            if (isset($fields['timezone'])) {
                $this->fill_in_timezone($fields['timezone']);
            }
        }
        // This is present in both forms of summative creation.
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Creates a survey.
     *
     * @param TableNode $data
     */
    protected function create_survey(TableNode $data)
    {
        $fields = $data->getRowsHash();
        $this->paper_basics('Survey', $fields['name']);
        // Set the start date.
        $this->fill_in_date_time('f', $fields['start']);
        // Set the end date.
        $this->fill_in_date_time('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fill_in_timezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fill_in_modules($fields['modules']);
        }
    }

    /**
     * Fills in the Date required field.
     *
     * @param string $month English month name
     */
    protected function fill_in_date_required($month)
    {
        $dates = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
        $this->fillField('period', array_search($month, $dates));
    }

    /**
     * Fills in a date time box.
     *
     * @param string $prefix The prefix for the datetime fields
     * @param string $datestring A string that will evaluate to a valid date time object
     */
    protected function fill_in_date_time($prefix, $datestring)
    {
        $date = new \DateTime($datestring);
        $this->fillField("{$prefix}day", $date->format('d'));
        $this->fillField("{$prefix}month", $date->format('m'));
        $this->fillField("{$prefix}year", $date->format('Y'));
        $this->fillField("{$prefix}time", $date->format('His'));
    }

    /**
     * Fills in the duration fields.
     *
     * @param string $duration
     * @throws Exception
     */
    protected function fill_in_duration($duration)
    {
        $duration = explode(':', $duration);
        if (count($duration) !== 2) {
            throw new Exception('The duration must be formatted as hours:minutes');
        }
        $this->fillField('duration_hours', $duration[0]);
        $this->fillField('duration_mins', $duration[1]);
    }

    /**
     * Fills in the fields which define which modules the paper is attached to.
     *
     * @param string $modules_string Comma separated list of module codes.
     */
    protected function fill_in_modules($modules_string)
    {
        // TODO: Refactor form so it will be possible to select the modules for a paper.
    }

    /**
     * Fills in a Academic session field.
     *
     * @param type $session
     */
    protected function fill_in_session($session)
    {
        $date = new \DateTime($session);
        $this->fillField('session', $date->format('Y'));
    }

    /**
     * Fills in a timezone field for the paper.
     *
     * @param string $timezone
     */
    protected function fill_in_timezone($timezone)
    {
        $this->fillField('timezone', $timezone);
    }

    /**
     * Fills in the first page of the paper creation dialogue.
     *
     * @param string $type The type of paper to be created.
     * @param string $name The name of the paper
     */
    protected function paper_basics($type, $name)
    {
        $this->i_click($type, 'paper_type');
        $this->fillField('paper_name', $name);
        $this->i_click('Next >', 'button');
    }
}
