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
 * Class for interacting with the feedback of a paper.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Feedback
{
    /** @var Config Stores the ExamSys configuration object. */
    protected Config $config;

    /** @var bool[] Stores if reports are enabled. */
    protected array $reports = [
        'objectives' => false,
        'questions' => false,
        'cohort_performance' => false,
        'external_examiner' => false
    ];

    /** @var bool Stores if question feedback is enabled at the module level. */
    protected bool $question_feedback_enabled;

    /**
     * The constructor.
     *
     * @param PaperProperties $properties The properties object of a paper.
     * @param Logger|null The class used to log changes.
     */
    public function __construct(
        protected PaperProperties $properties,
        protected ?Logger $logger = null,
    ) {
        $this->config = Config::get_instance();

        // Get the current settings.
        $sql = <<<SQL
            SELECT idfeedback_release, type
            FROM feedback_release
            WHERE paper_id = ?
        SQL;

        $feedback_details = $this->config->db->prepare($sql);
        $paperid = $this->properties->get_property_id();
        $feedback_details->bind_param('i', $paperid);
        $feedback_details->execute();
        $feedback_details->bind_result($idfeedback_release, $type);
        $feedback_details->store_result();
        while ($feedback_details->fetch()) {
            $this->reports[$type] = true;
        }
        $feedback_details->close();
    }

    /**
     * Gets a change logger.
     *
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger($this->config->db);
        }
        return $this->logger;
    }

    /**
     * Saves a setting change to the database.
     *
     * @param string $type
     * @param bool $enabled
     * @param string $userid
     * @param string $value
     * @return void
     */
    protected function saveSetting(string $type, bool $enabled, string $userid, string $value): void
    {
        $logger = $this->getLogger();
        $paperID = $this->properties->get_property_id();

        if ($enabled) {
            $sql = 'INSERT INTO feedback_release VALUES (NULL, ?, NOW(), ?)';
            $old = '';
            $new = $value;
        } else {
            $sql = 'DELETE FROM feedback_release WHERE paper_id = ? AND type = ?';
            $old = $value;
            $new = '';
        }

        $editProperties = $this->config->db->prepare($sql);
        $editProperties->bind_param('is', $paperID, $type);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userid, $old, $new, 'feedback');
    }

    /**
     * Gets the base url for all feedback reports.
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $this->config->get('cfg_root_path');
    }

    /**
     * Tests if the paper may have objective based feedback enabled.
     *
     * @return bool
     */
    public function objectiveFeedbackPossible(): bool
    {
        return in_array($this->properties->get_paper_type(), [
            assessment::TYPE_FORMATIVE,
            assessment::TYPE_PROGRESS,
            assessment::TYPE_SUMMATIVE,
            assessment::TYPE_OSCE,
            assessment::TYPE_OFFLINE,
        ]);
    }

    /**
     * Tests if objective based feedback is enabled for the paper.
     *
     * @return bool
     */
    public function hasObjectiveFeedback(): bool
    {
        return $this->objectiveFeedbackPossible() && $this->reports['objectives'];
    }

    /**
     * Sets if objective feedback is enabled for the paper.
     *
     * @param bool $enabled Flags if the feedback is enabled
     * @param int $userid The user who is making the change.
     * @return void
     */
    public function setObjectiveFeedback(bool $enabled, int $userid): void
    {
        if (!$this->objectiveFeedbackPossible()) {
            return;
        }

        if ($enabled === $this->hasObjectiveFeedback()) {
            // No change.
            return;
        }

        $this->saveSetting(
            type: 'objectives',
            enabled: $enabled,
            userid: $userid,
            value: 'Objectives-based Feedback',
        );

        $this->reports['objectives'] = $enabled;
    }

    /**
     * Gets the url for the objectives feedback for the paper.
     *
     * @return string
     */
    public function getObjectiveFeedbackUrl(): string
    {
        return $this->getBaseUrl() . '/students/objectives_feedback.php?id=' . $this->properties->get_crypt_name();
    }

    /**
     * Tests if the paper may have question based feedback.
     *
     * @return bool
     */
    public function questionFeedbackPossible(): bool
    {
        // Check if the feedback is enabled at the module level.
        if (!isset($this->question_feedback_enabled)) {
            $modules = array_keys($this->properties->get_modules());
            $this->question_feedback_enabled = Paper_utils::q_feedback_enabled($modules, $this->config->db);
        }

        return $this->question_feedback_enabled && in_array($this->properties->get_paper_type(), [
            assessment::TYPE_PROGRESS,
            assessment::TYPE_SUMMATIVE,
            assessment::TYPE_OSCE,
            assessment::TYPE_OFFLINE,
        ]);
    }

    /**
     * Tests if the paper has question based feedback enabled.
     *
     * @return bool
     */
    public function hasQuestionFeedback(): bool
    {
        return $this->questionFeedbackPossible() && $this->reports['questions'];
    }

    /**
     * Sets if question feedback is enabled for the paper.
     *
     * @param bool $enabled Flags if the feedback is enabled
     * @param int $userid The user who is making the change.
     * @return void
     */
    public function setQuestionFeedback(bool $enabled, int $userid): void
    {
        if (!$this->questionFeedbackPossible()) {
            return;
        }

        if ($enabled === $this->hasQuestionFeedback()) {
            // No change.
            return;
        }

        $this->saveSetting(
            type: 'questions',
            enabled: $enabled,
            userid: $userid,
            value: 'Question-based Feedback',
        );

        $this->reports['questions'] = $enabled;
    }

    /**
     * Gets the url of the question feedback report for the paper.
     *
     * @return string
     */
    public function getQuestionFeedbackUrl(): string
    {
        return $this->getBaseUrl() . '/students/question_feedback.php?id=' . $this->properties->get_crypt_name();
    }

    /**
     * Tests if the paper may have the cohort feedback report.
     *
     * @return bool
     */
    public function cohortPerformanceFeedbackPossible(): bool
    {
        return in_array($this->properties->get_paper_type(), [
            assessment::TYPE_SUMMATIVE,
            assessment::TYPE_OSCE,
            assessment::TYPE_OFFLINE,
        ]);
    }

    /**
     * Tests if cohort based feedback is enabled for the paper.
     *
     * @return bool
     */
    public function hasCohortPerformanceFeedback(): bool
    {
        return $this->cohortPerformanceFeedbackPossible() && $this->reports['cohort_performance'];
    }

    /**
     * Sets if cohort performance feedback is enabled for the paper.
     *
     * @param bool $enabled Flags if the feedback is enabled
     * @param int $userid The user who is making the change.
     * @return void
     */
    public function setCohortPerformanceFeedback(bool $enabled, int $userid): void
    {
        if (!$this->cohortPerformanceFeedbackPossible()) {
            return;
        }

        if ($enabled === $this->hasCohortPerformanceFeedback()) {
            // No change.
            return;
        }

        $this->saveSetting(
            type: 'cohort_performance',
            enabled: $enabled,
            userid: $userid,
            value: 'Cohort Performance Feedback',
        );

        $this->reports['cohort_performance'] = $enabled;
    }

    /**
     * Gets the url for the cohort performance report.
     *
     * @return string
     */
    public function getCohortPerformanceFeedbackUrl(): string
    {
        return $this->getBaseUrl() . '/students/performance_summary.php';
    }

    /**
     * Tests if the paper may have external examiner feedback.
     *
     * @return bool
     */
    public function externalExaminerFeedbackPossible(): bool
    {
        return in_array($this->properties->get_paper_type(), [
            assessment::TYPE_PROGRESS,
            assessment::TYPE_SUMMATIVE,
        ]);
    }

    /**
     * Tests if external examiner feedback is enabled for the paper.
     *
     * @return bool
     */
    public function hasExternalExaminerFeedback(): bool
    {
        return $this->externalExaminerFeedbackPossible() && $this->reports['external_examiner'];
    }

    /**
     * Sets if external examiner feedback is enabled for the paper.
     *
     * @param bool $enabled Flags if the feedback is enabled
     * @param int $userid The user who is making the change.
     * @return void
     */
    public function setExternalExaminerFeedback(bool $enabled, int $userid): void
    {
        if (!$this->externalExaminerFeedbackPossible()) {
            return;
        }

        if ($enabled === $this->hasExternalExaminerFeedback()) {
            // No change.
            return;
        }

        $this->saveSetting(
            type: 'external_examiner',
            enabled: $enabled,
            userid: $userid,
            value: 'External Examiner Feedback',
        );

        $this->reports['external_examiner'] = $enabled;
    }

    /**
     * Get the review for the external examiners feedback page.
     *
     * @return string
     */
    public function getExternalExaminerFeedbackUrl(): string
    {
        return $this->getBaseUrl() . '/reviews/';
    }
}
