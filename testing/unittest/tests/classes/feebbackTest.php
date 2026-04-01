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

use testing\unittest\unittestdatabase;

/**
 * Unit tests the Feedback classes methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group feedback
 * @covers Feedback
 */
class FeebbackTest extends unittestdatabase
{
    /** @var array A testing module with question feedback enabled. */
    protected $testmodule;

    /** @var array Details of a preconfigured user who is on the module team. */
    protected $user;

    #[\Override]
    public function datageneration(): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $usergenerator = $this->get_datagenerator('users');

        $this->testmodule = $modulegenerator->create_module([
            'moduleid' => 'TEST1001',
            'fullname' => 'Test module',
            'exam_q_feedback' => true,
        ]);
        $this->user = $usergenerator->create_user([
            'username' => 'teacher',
            'roles' => 'Staff',
        ]);
        $modulegenerator->create_module_team([
            'moduleid' => $this->testmodule['moduleid'],
            'username' => $this->user['username']
        ]);
    }

    /**
     * Tests that we can determine if objective based feedback may be enabled for a paper.
     *
     * @param int $papertype The type of paper.
     * @param bool $expected If the paper is eligible for objective based feedback.
     * @return void
     * @dataProvider dataObjectiveFeedbackPossible
     */
    public function testObjectiveFeedbackPossible(int $papertype, bool $expected): void
    {
        $papergenerator = $this->get_datagenerator('papers');

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $feedback = new Feedback($properties);

        $this->assertEquals($expected, $feedback->objectiveFeedbackPossible());
    }

    /**
     * Data to test if objective based feedback should be displayed.
     *
     * @return array[]
     */
    public function dataObjectiveFeedbackPossible(): array
    {
        return [
            [assessment::TYPE_FORMATIVE, true],
            [assessment::TYPE_PROGRESS, true],
            [assessment::TYPE_SUMMATIVE, true],
            [assessment::TYPE_SURVEY, false],
            [assessment::TYPE_OSCE, true],
            [assessment::TYPE_OFFLINE, true],
            [assessment::TYPE_PEERREVIEW, false],
        ];
    }

    /**
     * Tests that we can save the objective feedback settings accurately.
     *
     * @param int $papertype The type of paper.
     * @param bool $enabled If the setting is already enabled.
     * @param bool $expected If the paper is eligible for objective based feedback.
     * @return void
     * @dataProvider dataSetAndHasObjectiveFeedback
     */
    public function testSetAndHasObjectiveFeedback(int $papertype, bool $enabled, bool $expected): void
    {
        $papergenerator = $this->get_datagenerator('papers');

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);

        $feedback = new Feedback($properties);

        // By default feedback should not be enabled.
        $this->assertFalse($feedback->hasObjectiveFeedback());

        // Set the feedback and then test if its result was cached in the object.
        $feedback->setObjectiveFeedback($enabled, $this->user['id']);
        $this->assertEquals($expected, $feedback->hasObjectiveFeedback());

        // Test that the results were saved to the database.
        $feedback2 = new Feedback($properties);
        $this->assertEquals($expected, $feedback2->hasObjectiveFeedback());
    }

    /**
     * @return array[]
     */
    public function dataSetAndHasObjectiveFeedback(): array
    {
        return [
            // Testing a supported paper type.
            [assessment::TYPE_FORMATIVE, true, true],
            [assessment::TYPE_FORMATIVE, false, false],
            // Testing an unsupported paper type.
            [assessment::TYPE_PEERREVIEW, true, false],
            [assessment::TYPE_PEERREVIEW, false, false],
        ];
    }

    /**
     * Tests that we can determine if question based feedback may be enabled for a paper.
     *
     * @param int $papertype The type of paper.
     * @param bool $feedbackenabled Flags if feedback is enabled for the paper
     * @param bool $expected If the paper is eligible for question based feedback.
     * @return void
     * @dataProvider dataQuestionFeedbackPossible
     */
    public function testQuestionFeedbackPossible(int $papertype, bool $feedbackenabled, bool $expected): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');

        $module = $modulegenerator->create_module([
            'moduleid' => 'TEST1002',
            'fullname' => 'Test module 2',
            'exam_q_feedback' => (int) $feedbackenabled,
        ]);

        $modulegenerator->create_module_team([
            'moduleid' => $module['moduleid'],
            'username' => $this->user['username']
        ]);

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $module['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $feedback = new Feedback($properties);

        $this->assertEquals($expected, $feedback->questionFeedbackPossible());
    }

    /**
     * Data to test if question based feedback should be displayed.
     *
     * @return array[]
     */
    public function dataQuestionFeedbackPossible(): array
    {
        return [
            // Question based feedback enabled on the module.
            [assessment::TYPE_FORMATIVE, true, false],
            [assessment::TYPE_PROGRESS, true, true],
            [assessment::TYPE_SUMMATIVE, true, true],
            [assessment::TYPE_SURVEY, true, false],
            [assessment::TYPE_OSCE, true, true],
            [assessment::TYPE_OFFLINE, true, true],
            [assessment::TYPE_PEERREVIEW, true, false],
            // Question based feedback disabled in the module.
            [assessment::TYPE_FORMATIVE, false, false],
            [assessment::TYPE_PROGRESS, false, false],
            [assessment::TYPE_SUMMATIVE, false, false],
            [assessment::TYPE_SURVEY, false, false],
            [assessment::TYPE_OSCE, false, false],
            [assessment::TYPE_OFFLINE, false, false],
            [assessment::TYPE_PEERREVIEW, false, false],
        ];
    }

    /**
     * Tests that we can save the cohort feedback settings accurately.
     *
     * @param int $papertype The type of paper.
     * @param bool $enabled If the setting is already enabled.
     * @param bool $expected If cohort feedback is enabled.
     * @return void
     * @dataProvider dataSetAndHasExternalExaminerFeedback
     */
    public function testSetAndHasQuestionFeedback(int $papertype, bool $enabled, bool $expected): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');

        $module = $modulegenerator->create_module([
            'moduleid' => 'TEST1002',
            'fullname' => 'Test module 2',
            'exam_q_feedback' => 1,
        ]);

        $modulegenerator->create_module_team([
            'moduleid' => $module['moduleid'],
            'username' => $this->user['username']
        ]);

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $module['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);

        $feedback = new Feedback($properties);

        // By default feedback should not be enabled.
        $this->assertFalse($feedback->hasQuestionFeedback());

        // Set the feedback and then test if its result was cached in the object.
        $feedback->setQuestionFeedback($enabled, $this->user['id']);
        $this->assertEquals($expected, $feedback->hasQuestionFeedback());

        // Test that the results were saved to the database.
        $feedback2 = new Feedback($properties);
        $this->assertEquals($expected, $feedback2->hasQuestionFeedback());
    }

    /**
     * Data to test changing the cohort setting.
     *
     * @return array[]
     */
    public function dataSetAndHasQuestionFeedback(): array
    {
        return [
            // Testing a supported paper type.
            [assessment::TYPE_PROGRESS, true, true],
            [assessment::TYPE_PROGRESS, false, false],
            // Testing an unsupported paper type.
            [assessment::TYPE_FORMATIVE, true, false],
            [assessment::TYPE_FORMATIVE, false, false],
        ];
    }

    /**
     * Tests that we can determine if cohort based feedback may be enabled for a paper.
     *
     * @param int $papertype The type of paper.
     * @param bool $expected If the paper is eligible for cohort based feedback.
     * @return void
     * @dataProvider dataCohortPerformanceFeedbackPossible
     */
    public function testCohortPerformanceFeedbackPossible(int $papertype, bool $expected): void
    {
        $papergenerator = $this->get_datagenerator('papers');

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $feedback = new Feedback($properties);

        $this->assertEquals($expected, $feedback->cohortPerformanceFeedbackPossible());
    }

    /**
     * Data to test if cohort based feedback should be displayed.
     *
     * @return array[]
     */
    public function dataCohortPerformanceFeedbackPossible(): array
    {
        return [
            [assessment::TYPE_FORMATIVE, false],
            [assessment::TYPE_PROGRESS, false],
            [assessment::TYPE_SUMMATIVE, true],
            [assessment::TYPE_SURVEY, false],
            [assessment::TYPE_OSCE, true],
            [assessment::TYPE_OFFLINE, true],
            [assessment::TYPE_PEERREVIEW, false],
        ];
    }

    /**
     * Tests that we can save the cohort feedback settings accurately.
     *
     * @param int $papertype The type of paper.
     * @param bool $enabled If the setting is already enabled.
     * @param bool $expected If cohort feedback is enabled.
     * @return void
     * @dataProvider dataSetAndHasCohortFeedback
     */
    public function testSetAndHasCohortFeedback(int $papertype, bool $enabled, bool $expected): void
    {
        $papergenerator = $this->get_datagenerator('papers');

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);

        $feedback = new Feedback($properties);

        // By default feedback should not be enabled.
        $this->assertFalse($feedback->hasCohortPerformanceFeedback());

        // Set the feedback and then test if its result was cached in the object.
        $feedback->setCohortPerformanceFeedback($enabled, $this->user['id']);
        $this->assertEquals($expected, $feedback->hasCohortPerformanceFeedback());

        // Test that the results were saved to the database.
        $feedback2 = new Feedback($properties);
        $this->assertEquals($expected, $feedback2->hasCohortPerformanceFeedback());
    }

    /**
     * Data to test changing the cohort setting.
     *
     * @return array[]
     */
    public function dataSetAndHasCohortFeedback(): array
    {
        return [
            // Testing a supported paper type.
            [assessment::TYPE_SUMMATIVE, true, true],
            [assessment::TYPE_SUMMATIVE, false, false],
            // Testing an unsupported paper type.
            [assessment::TYPE_FORMATIVE, true, false],
            [assessment::TYPE_FORMATIVE, false, false],
        ];
    }

    /**
     * Tests that we can determine if cohort based feedback may be enabled for a paper.
     *
     * @param int $papertype The type of paper.
     * @param bool $expected If the paper is eligible for cohort based feedback.
     * @return void
     * @dataProvider dataExternalExaminerFeedbackPossible
     */
    public function testExternalExaminerFeedbackPossible(int $papertype, bool $expected): void
    {
        $papergenerator = $this->get_datagenerator('papers');

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $feedback = new Feedback($properties);

        $this->assertEquals($expected, $feedback->externalExaminerFeedbackPossible());
    }

    /**
     * Data to test if cohort based feedback should be displayed.
     *
     * @return array[]
     */
    public function dataExternalExaminerFeedbackPossible(): array
    {
        return [
            [assessment::TYPE_FORMATIVE, false],
            [assessment::TYPE_PROGRESS, true],
            [assessment::TYPE_SUMMATIVE, true],
            [assessment::TYPE_SURVEY, false],
            [assessment::TYPE_OSCE, false],
            [assessment::TYPE_OFFLINE, false],
            [assessment::TYPE_PEERREVIEW, false],
        ];
    }

    /**
     * Tests that we can save the cohort feedback settings accurately.
     *
     * @param int $papertype The type of paper.
     * @param bool $enabled If the setting is already enabled.
     * @param bool $expected If cohort feedback is enabled.
     * @return void
     * @dataProvider dataSetAndHasExternalExaminerFeedback
     */
    public function testSetAndHasExternalExaminerFeedback(int $papertype, bool $enabled, bool $expected): void
    {
        $papergenerator = $this->get_datagenerator('papers');

        // Create a test paper.
        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => $papertype,
            'paperowner' => $this->user['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);

        $feedback = new Feedback($properties);

        // By default feedback should not be enabled.
        $this->assertFalse($feedback->hasExternalExaminerFeedback());

        // Set the feedback and then test if its result was cached in the object.
        $feedback->setExternalExaminerFeedback($enabled, $this->user['id']);
        $this->assertEquals($expected, $feedback->hasExternalExaminerFeedback());

        // Test that the results were saved to the database.
        $feedback2 = new Feedback($properties);
        $this->assertEquals($expected, $feedback2->hasExternalExaminerFeedback());
    }

    /**
     * Data to test changing the cohort setting.
     *
     * @return array[]
     */
    public function dataSetAndHasExternalExaminerFeedback(): array
    {
        return [
            // Testing a supported paper type.
            [assessment::TYPE_PROGRESS, true, true],
            [assessment::TYPE_PROGRESS, false, false],
            // Testing an unsupported paper type.
            [assessment::TYPE_FORMATIVE, true, false],
            [assessment::TYPE_FORMATIVE, false, false],
        ];
    }
}
