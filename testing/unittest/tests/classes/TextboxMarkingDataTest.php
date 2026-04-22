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
 * Testcase for class textbox_marking_utils - database-dependent tests.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 onwards The University of Nottingham
 * @package tests
 * @group textbox
 * @group question
 */
class TextboxMarkingDataTest extends \testing\unittest\unittestdatabase
{
    /** @var array A student used in each test. */
    protected $student1;
    /** @var array A student used in each test. */
    protected $student2;
    /** @var array A student used in each test. */
    protected $student3;
    /** @var array A module used in tests. */
    protected $module1;
    /** @var array Staff member on the module. */
    protected $staff1;
    /** @var array A summative paper used in tests. */
    protected $paper;
    /** @var array A summative paper used in tests. */
    protected $paper2;
    /** @var array A formative paper used in tests. */
    protected $formative_paper;
    /** @var array A formative paper used in tests. */
    protected $formative_paper2;
    /** @var array A progress paper used in tests. */
    protected $progress_paper;
    /** @var array Textbox questions created for tests. */
    protected $textbox_questions = [];

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        // Create students
        $usergen = $this->get_datagenerator('users', 'core');
        $this->student1 = $usergen->create_user(['roles' => 'Student', 'sid' => '1001', 'surname' => 'Smith']);
        $this->student2 = $usergen->create_user(['roles' => 'Student', 'sid' => '1002', 'surname' => 'Johnson']);
        $this->student3 = $usergen->create_user(['roles' => 'Student', 'sid' => '1003', 'surname' => 'Williams']);

        // Create a staff member
        $this->staff1 = $usergen->create_user(['roles' => 'Staff', 'surname' => 'Anderson']);

        // Create module
        $modgen = $this->get_datagenerator('modules', 'core');
        $this->module1 = $modgen->create_module(['fullname' => 'Test Module', 'moduleid' => 'CTTEST']);

        // Enroll students
        $modgen->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student1['id']]);
        $modgen->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student2['id']]);
        $modgen->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student3['id']]);

        // Enroll the member of staff.
        $modgen->create_module_team(['moduleid' => 'CTTEST', 'username' => $this->staff1['username']]);

        // Create textbox questions textbox_questions[]
        $questiongen = $this->get_datagenerator('questions', 'core');
        $this->textbox_questions[] = $questiongen->create_question([
            'user' => $this->staff1['username'],
            'type' => 'textbox',
            'leadin' => 'Test textbox question 1',
        ]);
        $this->textbox_questions[] = $questiongen->create_question([
            'user' => $this->staff1['username'],
            'type' => 'textbox',
            'leadin' => 'Test textbox question 2',
        ]);
        $this->textbox_questions[] = $questiongen->create_question([
            'user' => $this->staff1['username'],
            'type' => 'textbox',
            'leadin' => 'Test textbox question 3',
        ]);

        // Create papers with different types
        $papergen = $this->get_datagenerator('papers', 'core');

        // Formative paper
        $this->formative_paper = $papergen->create_paper([
            'papertitle' => 'Formative Test Paper',
            'papertype' => \assessment::TYPE_FORMATIVE,
            'paperowner' => $this->staff1['username'],
            'modulename' => [$this->module1['fullname']],
            'startdate' => '2023-01-01 09:00:00',
            'enddate' => '2023-01-31 17:00:00',
        ]);

        // Formative paper2
        $this->formative_paper2 = $papergen->create_paper([
            'papertitle' => 'Formative Test Paper2',
            'papertype' => \assessment::TYPE_FORMATIVE,
            'paperowner' => $this->staff1['username'],
            'modulename' => [$this->module1['fullname']],
            'startdate' => '2023-01-01 09:00:00',
            'enddate' => '2023-01-31 17:00:00',
        ]);

        // Progress paper
        $this->progress_paper = $papergen->create_paper([
            'papertitle' => 'Progress Test Paper',
            'papertype' => \assessment::TYPE_PROGRESS,
            'paperowner' => $this->staff1['username'],
            'modulename' => [$this->module1['fullname']],
            'startdate' => '2023-02-01 09:00:00',
            'enddate' => '2023-02-28 17:00:00',
        ]);

        // Summative paper
        $this->paper = $papergen->create_paper([
            'papertitle' => 'Summative Test Paper',
            'papertype' => \assessment::TYPE_SUMMATIVE,
            'paperowner' => $this->staff1['username'],
            'modulename' => [$this->module1['fullname']],
            'startdate' => '2023-03-01 09:00:00',
            'enddate' => '2023-03-31 17:00:00',
        ]);
        // Summative paper2
        $this->paper2 = $papergen->create_paper([
            'papertitle' => 'Summative Test Paper2',
            'papertype' => \assessment::TYPE_SUMMATIVE,
            'paperowner' => $this->staff1['username'],
            'modulename' => [$this->module1['fullname']],
            'startdate' => '2023-03-01 09:00:00',
            'enddate' => '2023-03-31 17:00:00',
        ]);


        // Add questions to papers
        $questiongen = $this->get_datagenerator('questions', 'core');
        foreach ($this->textbox_questions as $index => $question) {
            $questiongen->add_question_to_paper([
                'paper' => $this->formative_paper['id'],
                'question' => $question['id'],
                'screen' => $index + 1,
                'displaypos' => $index + 1,
            ]);

            $questiongen->add_question_to_paper([
                'paper' => $this->progress_paper['id'],
                'question' => $question['id'],
                'screen' => $index + 1,
                'displaypos' => $index + 1,
            ]);

            $questiongen->add_question_to_paper([
                'paper' => $this->paper['id'],
                'question' => $question['id'],
                'screen' => $index + 1,
                'displaypos' => $index + 1,
            ]);
        }
    }

    /**
     * Tests get_count_textbox_responses with formative paper type.
     */
    public function testGet_count_textbox_responses_formative()
    {
        $loggen = $this->get_datagenerator('log', 'core');

        // Create records for log_metadata for formative paper (log1)
        $meta1 = $loggen->create_metadata([
            'userID' => $this->student1['id'],
            'paperID' => $this->formative_paper['id'],
            'started' => '2023-01-15 10:00:00',
        ]);

        $meta2 = $loggen->create_metadata([
            'userID' => $this->student2['id'],
            'paperID' => $this->formative_paper['id'],
            'started' => '2023-01-16 11:00:00',
        ]);

        // Create log_metadata for formative_paper2
        $meta3 = $loggen->create_metadata([
            'userID' => $this->student3['id'],
            'paperID' => $this->formative_paper2['id'],
            'started' => '2023-01-17 12:00:00',
        ]);

        // Create log for log_metadata items
        $loggen->create_formative([
            'metadataID' => $meta1['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-01-15 10:01:00',
        ]);

        $loggen->create_formative([
            'metadataID' => $meta1['id'],
            'q_id' => $this->textbox_questions[1]['id'],
            'started' => '2023-01-15 10:02:00',
        ]);

        $loggen->create_formative([
            'metadataID' => $meta2['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-01-16 11:01:00',
        ]);

        // Create log for log_metadata meta3
        $loggen->create_formative([
            'metadataID' => $meta3['id'],
            'q_id' => $this->textbox_questions[1]['id'],
            'started' => '2023-01-16 11:02:00',
        ]);

        // Test the function
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-01 00:00:00',
            '2023-01-31 23:59:59',
            '',
            30,
        );

        $this->assertIsArray($result);
        // Assert results, data should not contain $meta3 formative_paper2
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should include response at exact date boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-15 10:00:00',
            '2023-01-17 12:00:00',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should exclude response before startdate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-15 10:00:01',
            '2023-01-17 12:00:00',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should include response at exact enddate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-01 00:00:00',
            '2023-01-16 11:00:00',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should exclude response after enddate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-01 00:00:00',
            '2023-01-16 10:59:59',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: date range excludes all data (before all responses)
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-01 00:00:00',
            '2023-01-14 23:59:59',
            '',
            0,
        );
        $this->assertEmpty($result);

        // Boundary test: date range excludes all data (after all responses)
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->formative_paper['id'],
            \assessment::TYPE_FORMATIVE,
            '2023-01-20 00:00:00',
            '2023-01-31 23:59:59',
            '',
            0,
        );
        $this->assertEmpty($result);
    }

    /**
     * Tests get_count_textbox_responses with progress paper type.
     */
    public function testGet_count_textbox_responses_progress()
    {
        $loggen = $this->get_datagenerator('log', 'core');

        // Create log_metadata entries for progress paper (log0 and log1)
        $meta1 = $loggen->create_metadata([
            'userID' => $this->student1['id'],
            'paperID' => $this->progress_paper['id'],
            'started' => '2023-02-15 10:00:00',
        ]);

        // Create log entries for questions using progress log method
        $loggen->create_progress([
            'metadataID' => $meta1['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-02-15 10:01:00',
        ]);

        // Create log_metadata entries for progress paper (log0 and log1)
        $meta2 = $loggen->create_metadata([
            'userID' => $this->student2['id'],
            'paperID' => $this->progress_paper['id'],
            'started' => '2023-02-15 20:00:00',
        ]);

        // Create log entries in formative log.
        $loggen->create_formative([
            'metadataID' => $meta2['id'],
            'q_id' => $this->textbox_questions[1]['id'],
            'started' => '2023-02-15 20:01:00',
        ]);

        // Create log entry for staff1
        $metaStaff1 = $loggen->create_metadata([
            'userID' => $this->staff1['id'],
            'paperID' => $this->progress_paper['id'],
            'started' => '2023-02-16 12:00:00',
        ]);

        $loggen->create_progress([
            'metadataID' => $metaStaff1['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-02-16 12:01:00',
        ]);

        // Test the function with student-only filtering
        $rolesjoin = \log::get_student_only('u.id');
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-01 00:00:00',
            '2023-02-28 23:59:59',
            $rolesjoin,
            30,
        );
        // Assert results - only student responses counted (staff1 filtered out)
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result); // 1 response, staff1 filtered out

        // Assert results for all responses including staff1.
        $rolesjoin = '';
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-01 00:00:00',
            '2023-02-28 23:59:59',
            $rolesjoin,
            30,
        );
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Assert results for all responses after '2023-02-15 10:01:00' without $time_int.
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-15 10:01:00',
            '2023-02-28 23:59:59',
            $rolesjoin,
            0,
        );
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Assert results for all responses after '2023-02-15 10:01:00' with $time_int.
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-15 10:01:00',
            '2023-02-28 23:59:59',
            $rolesjoin,
            2,
        );
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Assert results for all responses within time '2023-02-15 20:00:00' to '2023-02-16 12:01:00'.
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-15 20:00:00',
            '2023-02-16 12:01:00',
            $rolesjoin,
            0,
        );
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should include response at exact startdate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-15 10:00:00',
            '2023-02-28 23:59:59',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should exclude response before startdate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-15 10:00:01',
            '2023-02-16 12:00:00',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should include response at exact datetime boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-15 10:00:00',
            '2023-02-16 12:00:00',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should exclude response after enddate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-01 00:00:00',
            '2023-02-16 11:59:59',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
            $this->textbox_questions[1]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: date range before all progress responses
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-01 00:00:00',
            '2023-02-14 23:59:59',
            '',
            0,
        );
        $this->assertEmpty($result);

        // Boundary test: date range after all progress responses
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->progress_paper['id'],
            \assessment::TYPE_PROGRESS,
            '2023-02-20 00:00:00',
            '2023-02-28 23:59:59',
            '',
            0,
        );
        $this->assertEmpty($result);
    }

    /**
     * Tests get_count_textbox_responses with summative paper.
     */
    public function testGet_count_textbox_responses_summative()
    {
        $loggen = $this->get_datagenerator('log', 'core');

        // Create log entries for summative paper (log2)
        $meta1 = $loggen->create_metadata([
            'userID' => $this->student1['id'],
            'paperID' => $this->paper['id'],
            'started' => '2023-03-15 10:00:00',
        ]);
        // Create meta4 log entries for summative paper (log2)
        $meta4 = $loggen->create_metadata([
            'userID' => $this->student1['id'],
            'paperID' => $this->paper2['id'],
            'started' => '2023-03-15 10:10:00',
        ]);
        $loggen->create_summative([
            'metadataID' => $meta4['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-03-15 10:11:00',
        ]);

        $loggen->create_summative([
            'metadataID' => $meta1['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-03-15 10:01:00',
        ]);

        $meta2 = $loggen->create_metadata([
            'userID' => $this->student2['id'],
            'paperID' => $this->paper['id'],
            'started' => '2023-03-16 11:00:00',
        ]);

        $loggen->create_summative([
            'metadataID' => $meta2['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-03-16 11:01:00',
        ]);

        $meta3 = $loggen->create_metadata([
            'userID' => $this->student3['id'],
            'paperID' => $this->paper['id'],
            'started' => '2023-03-17 12:00:00',
        ]);

        $loggen->create_summative([
            'metadataID' => $meta3['id'],
            'q_id' => $this->textbox_questions[0]['id'],
            'started' => '2023-03-17 12:01:00',
        ]);

        // Test with summative paper
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-01 00:00:00',
            '2023-03-31 23:59:59',
            '',
            120,
        );

        // Assert results
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 3,
        ];
        $this->assertEquals($expected, $result);

        // Test with summative paper within a time frame
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-15 10:01:00',
            '2023-03-17 11:59:59',
            '',
            0,
        );
        $this->assertIsArray($result);
        $expected = [
            $this->textbox_questions[0]['id'] => 1,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should include response at exact datetime boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-15 10:00:00',
            '2023-03-17 12:00:00',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 3,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should exclude response before startdate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-15 10:00:01',
            '2023-03-31 23:59:59',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: Should exclude response after enddate boundary
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-01 00:00:00',
            '2023-03-17 11:59:59',
            '',
            0,
        );
        $expected = [
            $this->textbox_questions[0]['id'] => 2,
        ];
        $this->assertEquals($expected, $result);

        // Boundary test: date range before all summative responses
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-01 00:00:00',
            '2023-03-14 23:59:59',
            '',
            0,
        );
        $this->assertEmpty($result);

        // Boundary test: date range after all summative responses
        $result = textbox_marking_utils::get_count_textbox_responses(
            $this->paper['id'],
            \assessment::TYPE_SUMMATIVE,
            '2023-03-20 00:00:00',
            '2023-03-31 23:59:59',
            '',
            0,
        );
        $this->assertEmpty($result);
    }
}
