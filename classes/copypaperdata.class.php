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
 * Data class that handles the creation of data for the copy_paper.html template.
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */
class CopyPaperData
{
    /** @var array Language strings used for the page */
    private $string;

    /**
     * Constructor for CopyPaperData
     *
     * @param array $string Array of language strings
     */
    public function __construct(array $string)
    {
        $this->string = $string;
    }

    /**
     * Get paper types options based on current paper type
     *
     * @param int $currentType The current paper type
     * @return array Array of paper type options
     */
    public function getPaperTypeOptions(int $currentType): array
    {
        $options = [];

        switch ($currentType) {
            case \assessment::TYPE_FORMATIVE:
            case \assessment::TYPE_PROGRESS:
            case \assessment::TYPE_SUMMATIVE:
                $options[] = [
                    'value' => \assessment::TYPE_FORMATIVE,
                    'text' => $this->string['formative self-assessment'],
                    'selected' => ($currentType == \assessment::TYPE_FORMATIVE)
                ];
                $options[] = [
                    'value' => \assessment::TYPE_PROGRESS,
                    'text' => $this->string['progress test'],
                    'selected' => ($currentType == \assessment::TYPE_PROGRESS)
                ];
                $options[] = [
                    'value' => \assessment::TYPE_SUMMATIVE,
                    'text' => $this->string['summative exam'],
                    'selected' => ($currentType == \assessment::TYPE_SUMMATIVE)
                ];
                break;
            case \assessment::TYPE_SURVEY:
                $options[] = [
                    'value' => \assessment::TYPE_SURVEY,
                    'text' => $this->string['survey'],
                    'selected' => true
                ];
                break;
            case \assessment::TYPE_OSCE:
                $options[] = [
                    'value' => \assessment::TYPE_OSCE,
                    'text' => $this->string['osce station'],
                    'selected' => true
                ];
                break;
            case \assessment::TYPE_OFFLINE:
                $options[] = [
                    'value' => \assessment::TYPE_OFFLINE,
                    'text' => $this->string['offline paper'],
                    'selected' => true
                ];
                break;
            case \assessment::TYPE_PEERREVIEW:
                $options[] = [
                    'value' => \assessment::TYPE_PEERREVIEW,
                    'text' => $this->string['peer review'],
                    'selected' => true
                ];
                break;
        }

        return $options;
    }

    /**
     * Get all campus details
     *
     * @param mysqli $mysqli Database connection
     * @return array Array of campus details
     */
    public function getCampusDetails(mysqli $mysqli): array
    {
        $campusobj = new campus($mysqli);
        return $campusobj->get_all_campus_details();
    }

    /**
     * Get month options for date required dropdown
     *
     * @return array Array of month options
     */
    public function getMonthOptions(): array
    {
        $months = [];
        $month_keys = ['january', 'february', 'march', 'april', 'may', 'june',
                      'july', 'august', 'september', 'october', 'november', 'december'];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'value' => $i,
                'text' => $this->string[$month_keys[$i - 1]]
            ];
        }

        return $months;
    }

    /**
     * Get cohort size options
     *
     * @param Config $configObject Config object
     * @return array Array of cohort size options
     */
    public function getCohortSizeOptions(Config $configObject): array
    {
        $options = [];
        $cohort_sizes = $configObject->get_setting('core', 'summative_cohort_sizes');

        foreach ($cohort_sizes as $size) {
            $display_value = ($size === '<whole cohort>') ? $this->string['wholecohort'] : $size;
            $options[] = [
                'value' => $size,
                'text' => $display_value
            ];
        }

        return $options;
    }

    /**
     * Get sitting options
     *
     * @param Config $configObject Config object
     * @return array Array of sitting options
     */
    public function getSittingOptions(Config $configObject): array
    {
        $options = [];
        $maxSittings = $configObject->get_setting('core', 'summative_max_sittings');

        $maxSittings = max(1, intval($maxSittings));

        for ($i = 1; $i <= $maxSittings; $i++) {
            $options[] = [
                'value' => $i,
                'text' => $i
            ];
        }

        return $options;
    }

    /**
     * Prepare data for the header template
     *
     * @param Config $configObject Config object
     * @return array Data for the header template
     */
    public function prepareHeaderData(Config $configObject): array
    {
        return [
            'css' => ['/css/source/copy_paper.css'],
            'metadata' => [],
            'mathjax' => $configObject->get_setting('core', 'cfg_mathjax_path'),
            'three' => $configObject->get_setting('core', 'cfg_three_path'),
            'editor' => $configObject->get_setting('core', 'cfg_editor_path'),
            'texteditor' => '',
            'scripts' => []
        ];
    }

    /**
     * Prepare data for the footer template
     *
     * @return array Data for the footer template
     */
    public function prepareFooterData(): array
    {
        return [
            'scripts' => ['/js/copypaperinit.min.js']
        ];
    }

    /**
     * Prepare data for the dataset template
     *
     * @param int $paperID Paper ID
     * @param string $summative_mgmt Summative management setting
     * @param string $max_duration Maximum duration setting
     * @param array $validation_strings Validation strings for form validation
     * @return array Data for the dataset template
     */
    public function prepareDatasetData(
        int $paperID, 
        string $summative_mgmt, 
        string $max_duration,
        array $validation_strings
    ): array
    {
        return [
            'name' => 'dataset',
            'attributes' => [
                'paperid' => $paperID,
                'summative-mgmt' => $summative_mgmt,
                'max-duration' => $max_duration,
                'required-field' => $validation_strings['required_field'],
                'invalid-duration' => $validation_strings['invalid_duration'],
                'max-duration-exceeded' => $validation_strings['max_duration_exceeded']
            ]
        ];
    }

    /**
     * Prepare data for the copy paper template
     *
     * @param PaperProperties $properties Paper properties
     * @param int $paperID Paper ID
     * @param Config $configObject Config object
     * @param mysqli $mysqli Database connection
     * @param string $module Module ID
     * @param string $folder Folder ID
     * @return array Data for the template
     */
    public function prepareTemplateData(
        PaperProperties $properties,
        int $paperID,
        Config $configObject,
        mysqli $mysqli,
        $module,
        $folder
    ): array {
        $yearutils = new yearutils($mysqli);
        $calendar_year_options = $yearutils->get_calendar_year_dropdown_options(
            $properties->get_paper_type(),
            $properties->get_calendar_year(),
            $this->string
        );

        return [
            'paperID' => $paperID,
            'module' => $module,
            'folder' => $folder,
            'summative_mgmt' => $configObject->get_setting('core', 'cfg_summative_mgmt'),
            'max_duration' => $configObject->get_setting('core', 'paper_max_duration'),
            'paper_title' => $properties->get_paper_title(),
            'paper_types' => $this->getPaperTypeOptions($properties->get_paper_type()),
            'calendar_year_options' => $calendar_year_options,
            'stdset_copy_std_setting' => $configObject->get_setting('core', 'stdset_copy_std_setting'),
            'campuses' => $this->getCampusDetails($mysqli),
            'months' => $this->getMonthOptions(),
            'cohort_sizes' => $this->getCohortSizeOptions($configObject),
            'sittings' => $this->getSittingOptions($configObject),
            'validation_strings' => [
                'required_field' => $this->string['fieldrequired'],
                'invalid_duration' => $this->string['invalidduration'],
                'max_duration_exceeded' => $this->string['maxdurationexceeded']
            ]
        ];
    }
}
