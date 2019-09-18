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

namespace testing\datagenerator;

use \Exception,
    \UserUtils,
    \QuestionUtils,
    \random_utils;

/**
 * Generates Rogo paper.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class questions extends generator {

    /**
     * Create a new question
     *  Since _fields_required had not been used in question creating process, required fields are hard coded in HTML in webpage....
     *  here have to use hard code sql to create question.
     *
     * @param array $data mandatory question data
     * @return array
     * @throws data_error If passed parameter is invalid
     * @throws no_database
     * @throws not_found
     */
    public function insert_question($data) {
        loader::get('papers');
        $username = $data['user'];
        $userid = UserUtils::username_exists($username, $this->db);

        $defaults = array(
            "q_type" => null,
            "theme" => "",
            "leadin" => "test question leadin",
            "notes" => "",
            "display_method" => "vertical",
            "ownerID" => $userid,
            "q_media" => null,
            "q_media_width" => 0,
            "q_media_height" => 0,
            "creation_date" => date('Y-m-d H:i:s'),
            "last_edited" => date('Y-m-d H:i:s'),
            "bloom" => null,
            "scenario" => "defult scenario",
            "scenario_plain" => "defult scenario_plain",
            "leadin_plain" => "",
            "checkout_time" => null,
            "checkout_authorID" => "",
            "deleted" => false,
            "locked" => null,
            "std" => "",
            "status" => 1,
            "q_option_order" => "display order",
            "score_method" => "Mark per Option",
            "settings" => "",
            "guid" => uniqid()
        );
        $qdata = $this->set_defaults_and_clean($defaults, $data);
        $now = date('Y-m-d H:i:s');
        if ($qdata['deleted']) {
            $qdata['deleted'] = $now;
        } else {
            $qdata['deleted'] = null;
        }
        $sqlquery = <<< SQLQUERY
INSERT INTO questions (q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method,
display_method, q_option_order, std, bloom, ownerID, q_media, q_media_width, q_media_height, checkout_time, checkout_authorID,
creation_date, last_edited, locked, deleted, status, settings, guid)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQLQUERY;
        try {

            $result = $this->db->prepare($sqlquery);
            $result->bind_param('ssssssssssssssissssisssssss', $qdata['q_type'], $qdata['theme'], $qdata['scenario'],
                $qdata['scenario_plain'], $qdata['leadin'], $qdata['leadin_plain'], $qdata['notes'], $qdata['correct_fback'],
                $qdata['incorrect_fback'], $qdata['score_method'], $qdata['display_method'], $qdata['q_option_order'], $qdata['std'],
                $qdata['bloom'], $qdata['ownerID'], $qdata['q_media'], $qdata['q_media_width'], $qdata['q_media_height'], $qdata['checkout_time'],
                $qdata['checkout_authorID'], $qdata['creation_date'], $qdata['last_edited'], $qdata['locked'], $qdata['deleted'], $qdata['status'],
                $qdata['settings'], $qdata['guid']);
            $result->execute();
            $qdata['id'] = $result->insert_id;
            $result->close();
            return $qdata;
        } catch (Exception $e) {
            echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
            echo nl2br($e->getTraceAsString());
            throw new data_error("MySQL error " . $this->_mysqli->error . "<br /> Query:<br /> $sqlquery", $this->_mysqli->errno);
        }
    }

    /**
     * Create a new question
     *
     * @parm array $parameters
     *  string parameters[paperowner]
     *  string parameters[type]
     * @throws data_error If passed parameter is invalid
     * @throws no_database
     * @throws not_found
     * @return array
     */
    public function create_question($parameters) {

        $types = \QuestionEdit::$types;
        // Basic check mandatory parameters for creating question.
        if (empty($parameters['type']) or (!in_array($parameters['type'], $types)) or empty($parameters['user']) or empty($parameters['leadin'])) {
            throw new data_error('Must pass list of question type and title ');
        } else {
            $parameters['q_type'] = $parameters['type'];
            unset($parameters['type']);
            $parameters['leadin_plain'] = strip_tags($parameters['leadin']);
            if (!empty($parameters['scenario'])) {
                $parameters['scenario_plain'] = strip_tags($parameters['scenario']);
            }
            return $this->insert_question($parameters);
        }
    }

    /**
     * Add a question to a paper
     * @param array parameters
     *  string parameters[paper]
     *  string parameters[question]
     *  string parameters[screen]
     *  string parameters[displaypos]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function add_question_to_paper($parameters) {
        if (empty($parameters['paper'])) {
            throw new data_error('paper must be provided');
        }
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        if (empty($parameters['screen'])) {
            throw new data_error('screen must be provided');
        }
        if (empty($parameters['displaypos'])) {
            throw new data_error('display position must be provided');
        }
        \Paper_utils::add_question($parameters['paper'], $parameters['question'], $parameters['screen'], $parameters['displaypos'], $this->db);
        return $parameters;
    }

    /**
     * Add options to question
     * @param array parameters
     *  integer parameters[question]
     *  options are (option_text, o_media, o_media_width, o_media_width, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function add_options_to_question($parameters) {
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        $defaults = array('option_text' => null, 'o_media' => '', 'o_media_width' => '0', 'o_media_height' => '0', 'feedback_right' => null, 'feedback_wrong' => null, 'correct' => null, 'marks_correct' => null, 'marks_incorrect' => null, 'marks_partial' => null);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);

        $result = $this->db->prepare("INSERT INTO options VALUE (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
        $result->bind_param('isssssssddd', $parameters['question'], $settings['option_text'], $settings['o_media'], $settings['o_media_width'], $settings['o_media_height'], $settings['feedback_right'], $settings['feedback_wrong'], $settings['correct'], $settings['marks_correct'], $settings['marks_incorrect'], $settings['marks_partial']);
        $result->execute();
        $result->close();
        $settings['question'] = $parameters['question'];
        return $settings;
    }

    /**
     * Add a question to a module
     * @param array parameters
     *  string parameters[module]
     *  string parameters[question]
     * @throws data_error If passed parameter is invalid
     */
    public function add_to_module($parameters) {
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        if (empty($parameters['module'])) {
            throw new data_error('module must be provided');
        }
        if (is_array($parameters['module'])) {
            $modules = $parameters['module'];
        } else {
            $modules = array($parameters['module']);
        }
        QuestionUtils::add_modules($modules, $parameters['question'], $this->db);
    }

    /**
     * Add a question to a random block
     * @param array parameters
     *  string parameters[block]
     *  string parameters[question]
     * @throws data_error If passed parameter is invalid
     */
    public function add_to_random_block($parameters) {
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        if (empty($parameters['block'])) {
            throw new data_error('block must be provided');
        }

        if (!random_utils::insert_random_link($parameters['block'], $parameters['question'], $this->db)) {
            throw new data_error('question ' . $parameters['question'] . ' not inserted into random block');
        }
    }

}
