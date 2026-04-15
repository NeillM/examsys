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

/**
 *
 * Utility class for Textbox Marking related functionality
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class textbox_marking_utils
{
    /**
     * Returns an array of textbox question IDs and count of user's responses.
     *
     * @param int $paperID - ID of the paper to be used
     * @param int $paper_type - Type of paper
     * @param string $startdate - Start date of the exam
     * @param string $enddate - End date of the exam
     * @param string $rolesjoin - Join for roles
     * @param int $time_int - Start time interval in minutes
     * @return array - List of question IDs and responses' count.
     */
    public static function get_count_textbox_responses(int $paperID, int $paper_type, string $startdate, string $enddate, string $rolesjoin, int $time_int)
    {
        $db = Config::get_instance()->db;
        $questionID = null;
        $responded = null;
        $params = [$paperID, $startdate, $enddate];
        $param_types = 'iss';

        $numberofresponded = [];
        // SQL for count responses of textbox questions
        if (($paper_type == \assessment::TYPE_FORMATIVE) or ($paper_type == \assessment::TYPE_PROGRESS)) {

            $sql = "
                SELECT q_id, COUNT(*)
                FROM (
                    SELECT q.q_id, l.id
                    FROM log1 l
                    INNER JOIN log_metadata lm ON lm.id = l.metadataID
                    INNER JOIN users u ON u.id = lm.userID
                        $rolesjoin
                    INNER JOIN questions q ON q.q_id = l.q_id
                    WHERE lm.paperID = ?
                        AND q.q_type = 'textbox'
                        AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
                        AND lm.started <= ?
                    UNION
                    SELECT q.q_id, l.id
                    FROM log0 l
                    INNER JOIN log_metadata lm ON lm.id = l.metadataID
                    INNER JOIN users u ON u.id = lm.userID
                        $rolesjoin
                    INNER JOIN questions q ON q.q_id = l.q_id
                    WHERE lm.paperID = ?
                      AND q.q_type = 'textbox'
                      AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
                      AND lm.started <= ?) AS q
                GROUP BY q_id";
            $result = $db->prepare($sql);
            $result->bind_param('ississ', $paperID, $startdate, $enddate, $paperID, $startdate, $enddate);
        } else {
            $log_table = "log$paper_type";
            $sql = "
                SELECT q.q_id, COUNT(*)
                FROM $log_table l
                INNER JOIN log_metadata lm ON lm.id = l.metadataID
                INNER JOIN users u ON u.id = lm.userID
                $rolesjoin
                INNER JOIN questions q ON q.q_id = l.q_id
                WHERE lm.paperID = ?
                    AND q.q_type = 'textbox'
                    AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
                    AND lm.started <= ?
                GROUP BY q.q_id";
            $result = $db->prepare($sql);
            $result->bind_param('iss', $paperID, $startdate, $enddate);
        }
        $result->execute();
        $result->bind_result($questionID, $responded);
        while ($result->fetch()) {
            $numberofresponded[$questionID] = $responded;
        }
        $result->close();
        return $numberofresponded;
    }

    /**
     * Returns an array of user IDs who are down for second marking.
     *
     * @param $paperID - ID of the paper to be used
     * @param $db      - Database connection
     * @return array   - List of users who are set for remarking.
     */
    public static function get_remark_users($paperID, $db)
    {
        $remark_array = [];

        $result = $db->prepare('SELECT userID FROM textbox_remark WHERE paperID = ?');
        $result->bind_param('i', $paperID);
        $result->execute();
        $result->bind_result($userID);
        while ($result->fetch()) {
            $remark_array[$userID] = true;
        }
        $result->close();

        return $remark_array;
    }

    /**
     * Converts a time/date from 20140301103059 into 01/03/2014 10:30.
     *
     * Please use date_utils::rogoToDisplay instead. This function will be removed in the future.
     *
     * @param string $original - The date that needs to be convered.
     * @return string
     * @deprecated since 7.2.0
     */
    public static function nicedate($original)
    {
        return date_utils::rogoToDisplay($original);
    }

    /**
     * Highlight key terms in user answer.
     *
     * @param array $settings question settings
     * @param string $answer user answer
     * @return string
     */
    public static function higlightterms($settings, $answer)
    {
        if (isset($settings['terms'])) {
            $correct_answers = json_decode($settings['terms']);
            if (!is_null($correct_answers)) {
                foreach ($correct_answers as $single_answer) {
                    $regexp = '/(?![^<]*>)' // Checks the term is not inside a html tag like structure.
                        . '(' . preg_quote((string) $single_answer, '/') . ')' // Adds in the term.
                        . '/i'; // Is case-insensitive.
                    $answer = preg_replace(
                        $regexp,
                        '<span class="highlight">' . $single_answer . '</span>',
                        (string) $answer
                    );
                }
            }
        }
        return $answer;
    }
}
