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
 * Data class that handles the creation of data for the reports page template.
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */
class ReportsData
{
    /** @var array Language strings used for the page */
    private $string;

    /** @var Config The configuration object */
    private $config;

    /** @var mysqli The database connection */
    private $db;

    /**
     * Constructor for ReportsData
     *
     * @param array $string Array of language strings
     */
    public function __construct(array $string)
    {
        $this->string = $string;
        $this->config = Config::get_instance();
        $this->db = $this->config->db;
    }

    /**
     * Prepare data for the header template
     *
     * @return array Data for the header template
     */
    public function prepareHeaderData(): array
    {
        return [
            'css' => ['/css/source/reports_form.css'],
            'metadata' => [],
            'mathjax' => $this->config->get_setting('core', 'cfg_mathjax_path'),
            'three' => $this->config->get_setting('core', 'cfg_three_path'),
            'editor' => $this->config->get_setting('core', 'cfg_editor_path'),
            'texteditor' => '',
            'scripts' => []
        ];
    }

    /**
     * Prepare data for the reports template
     *
     * @param PaperProperties $properties Paper properties
     * @param int $paperID Paper ID
     * @param string|null $module Module code
     * @param string|null $folder Folder name
     * @return array Data for the template
     */
    public function prepareTemplateData(
        PaperProperties $properties,
        int $paperID,
        $module = null,
        $folder = null
    ): array {
        // Get paper type name
        $paperType = $properties->get_paper_type();
        
        // Calculate date ranges for the report
        $dateRanges = $this->calculateDateRanges($properties);
        
        // Generate date selectors HTML
        $startDateSelector = date_utils::timedate_select(
            'start_', 
            date($dateRanges['default_start']), 
            true, 
            $dateRanges['start_year'], 
            $dateRanges['end_year'], 
            $this->string
        );
        
        $endDateSelector = date_utils::timedate_select(
            'end_', 
            date($dateRanges['default_end']), 
            true, 
            $dateRanges['start_year'], 
            $dateRanges['end_year'], 
            $this->string
        );
        
        $data = [
            'paperID' => $paperID,
            'module' => $module,
            'folder' => $folder,
            'paper_title' => $properties->get_paper_title(),
            'paper_type' => $paperType,
            'start_date_selector' => $startDateSelector,
            'end_date_selector' => $endDateSelector,
            'month_options' => $this->getMonthOptions(),
            'course_options' => $this->getCourseOptions($paperID),
            'module_options' => $this->getModuleOptions($paperID, $paperType),
            'direction_options' => $this->getCohortDirectionOptions(),
            'percentage_options' => $this->getPercentageOptions(),
            'absent_checkbox' => $this->getAbsentCheckboxData(),
            'students_only_checkbox' => $this->getStudentsOnlyCheckboxData(),
            'scripts' => ['js/modules/papersidebar.min.js', 'js/modules/reports.min.js']
        ];
        
        // Add reviews section data if paper type is appropriate
        if (in_array($paperType, ['0', '1', '2', '5'])) {
            $data['reviews_section'] = [
                'title' => $this->string['reviews'],
                'items' => $this->getReviewsData($properties)
            ];
        }
        
        return $data;
    }

    /**
     * Get month options for date selectors
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
     * Get course options for the report
     *
     * @param int $paperID Paper ID
     * @return array Array of course options
     */
    public function getCourseOptions(int $paperID): array
    {
        $courses = [];
        
        // Add "Any Course" option
        $courses[] = [
            'value' => '%',
            'text' => $this->string['anycourse']
        ];
        
        // Get courses from database
        $stmt = $this->db->prepare('SELECT DISTINCT student_grade, description 
                                FROM log_metadata, courses 
                                WHERE log_metadata.student_grade = courses.name 
                                AND paperID = ? 
                                ORDER BY student_grade');
        $stmt->bind_param('i', $paperID);
        $stmt->execute();
        $stmt->bind_result($student_grade, $description);
        
        while ($stmt->fetch()) {
            $courses[] = [
                'value' => $student_grade,
                'text' => "$student_grade: $description"
            ];
        }
        
        $stmt->close();
        
        return $courses;
    }

    /**
     * Get module options for the report
     *
     * @param int $paperID Paper ID
     * @param string $paperType Paper type
     * @return array Array of module options
     */
    public function getModuleOptions(int $paperID, string $paperType): array
    {
        if ($paperType == '3') {
            return [];
        }
        
        $modules = [];
        
        $modules[] = [
            'value' => '',
            'text' => $this->string['anymodule']
        ];
        
        // Get modules from database using Paper_utils
        $moduleIDs = Paper_utils::get_modules($paperID, $this->db);
        
        foreach ($moduleIDs as $modID => $modCode) {
            $modules[] = [
                'value' => $modID,
                'text' => $modCode
            ];
        }
        
        return $modules;
    }

    /**
     * Get cohort direction options for the report
     *
     * @return array Array of direction options
     */
    public function getCohortDirectionOptions(): array
    {
        return [
            [
                'value' => 'asc',
                'text' => $this->string['allcandidates'],
                'selected' => true
            ],
            [
                'value' => 'desc',
                'text' => $this->string['top'],
                'selected' => false
            ],
            [
                'value' => 'asc',
                'text' => $this->string['bottom'],
                'selected' => false
            ]
        ];
    }

    /**
     * Get percentage options for the report
     *
     * @return array Array of percentage options
     */
    public function getPercentageOptions(): array
    {
        $percentages = [1, 5, 10, 15, 25, 27, 33.3, 50, 100];
        $options = [];
        
        foreach ($percentages as $percent) {
            $options[] = [
                'value' => $percent,
                'text' => $percent . '%',
                'selected' => ($percent == 100) 
            ];
        }
        
        return $options;
    }

    /**
     * Get absent candidates checkbox data
     *
     * @return array Data for the absent candidates checkbox
     */
    public function getAbsentCheckboxData(): array
    {
        return [
            'id' => 'absent',
            'name' => 'absent',
            'value' => '1',
            'label' => $this->string['incabsentcandidates'],
            'checked' => false
        ];
    }

    /**
     * Get students only checkbox data
     *
     * @return array Data for the students only checkbox
     */
    public function getStudentsOnlyCheckboxData(): array
    {
        return [
            'id' => 'studentsonly',
            'name' => 'studentsonly',
            'value' => '1',
            'label' => $this->string['studentsonly'],
            'checked' => true, 
            'tooltip' => $this->string['tooltip_studentattempts']
        ];
    }

    /**
     * Get data for the reviews section
     * 
     * @param PaperProperties $properties Paper properties
     * @return array Reviews section data
     */
    public function getReviewsData(PaperProperties $properties): array
    {
        $reviews = [];
        
        // Internal peer review link
        $reviews[] = [
            'url' => '../reports/review_comments.php?type=internal&scrOfY=0&',
            'text' => $this->string['internalpeerreview'],
            'class' => 'reports'
        ];
        
        // SCT responses link (conditional)
        if ($properties->q_type_exist('sct')) {
            $reviews[] = [
                'url' => '../reports/review_sct_answers.php?type=external&',
                'text' => $this->string['sctresponses'],
                'class' => 'reports'
            ];
        }
        
        // External examiners link
        $reviews[] = [
            'url' => '../reports/review_comments.php?type=external&',
            'text' => $this->string['externalexaminers'],
            'class' => 'reports'
        ];
        
        return $reviews;
    }

    /**
     * Calculate date ranges for the report based on paper properties
     *
     * @param PaperProperties $properties Paper properties
     * @return array Date range information
     */
    private function calculateDateRanges(PaperProperties $properties): array
    {
        $date_array = getdate();
        $now_data = date('Y') . '-' . date('m') . '-' . date('d') . ' ' . date('H') . ':' . date('i') . ':00';
        $now_time = date('U', strtotime($now_data));
        
        // Calculate target start date
        $target_start_date = $properties->get_start_date();
        if (!is_null($target_start_date) && $target_start_date > $now_time) {
            $target_start_date = $properties->get_created();
        }
        
        // Calculate target end date
        $target_end_date = $properties->get_end_date();
        if (!is_null($target_end_date) && $target_end_date > $now_time) {
            $target_end_date = $now_time;
        }
        
        // Extract date components
        $start_year = date('Y', $target_start_date);
        $start_month = date('m', $target_start_date);
        $start_day = date('d', $target_start_date);
        $start_hour = date('H', $target_start_date);
        $start_minute = date('i', $target_start_date);
        
        $end_year = date('Y', $target_end_date);
        $end_month = date('m', $target_end_date);
        $end_day = date('d', $target_end_date);
        $end_hour = date('H', $target_end_date);
        $end_minute = date('i', $target_end_date);
        
        // Adjust end date if it's in the future
        if ($end_year > date('Y') || ($end_year == date('Y') && $end_month > date('m'))) {
            $end_day = date('d');
            $end_month = date('m');
        }
        
        // Adjust start date if it's in the future
        if ($start_year > $date_array['year']) {
            $start_year = $date_array['year'];
            $start_month = $date_array['mon'];
            $start_day = $date_array['mday'];
            $start_hour = $date_array['hours'];
            $start_minute = $date_array['minutes'];
        } elseif ($start_year == $date_array['year'] && intval($start_month) > $date_array['mon']) {
            $start_month = $date_array['mon'];
            $start_day = $date_array['mday'];
            $start_hour = $date_array['hours'];
            $start_minute = $date_array['minutes'];
        } elseif ($start_year == $date_array['year'] && intval($start_month) == $date_array['mon'] && intval($start_day) > $date_array['mday']) {
            $start_day = $date_array['mday'];
            $start_hour = $date_array['hours'];
            $start_minute = $date_array['minutes'];
        }
        
        // Format default start and end dates
        $default_start = $start_year . $start_month . $start_day . $start_hour . $start_minute . '00';
        
        // Adjust end date if it's in the future
        if ($end_year > $date_array['year']) {
            $end_year = $date_array['year'];
            $end_month = $date_array['mon'];
            $end_day = $date_array['mday'];
        } elseif ($end_month > $date_array['mon'] && $end_year == $date_array['year']) {
            $end_month = $date_array['mon'];
            $end_day = $date_array['mday'];
        } elseif ($end_day > $date_array['mday'] && $end_month == $date_array['mon'] && $end_year == $date_array['year']) {
            $end_day = $date_array['mday'];
        }
        
        // Ensure proper formatting for month and day
        if ($end_month < 10 && strlen($end_month) == 1) {
            $end_month = '0' . $end_month;
        }
        if ($end_day < 10 && strlen($end_day) == 1) {
            $end_day = '0' . $end_day;
        }
        
        $default_end = $end_year . $end_month . $end_day . $end_hour . $end_minute . '00';
        
        return [
            'default_start' => $default_start,
            'default_end' => $default_end,
            'start_year' => 2001,
            'end_year' => ($date_array['year'] + 1)
        ];
    }

    /**
     * Prepare data for the footer template
     *
     * @param string|null $module Module code
     * @param string|null $folder Folder name
     * @return array Data for the footer template
     */
    public function prepareFooterData($module = null, $folder = null): array
    {
        return [
            'scripts' => ['js/modules/papersidebar.min.js'],
            'module' => $module,
            'folder' => $folder
        ];
    }

    /**
     * Prepare data for the dataset template
     *
     * @param string $paperType Paper type
     * @param int $paperID Paper ID
     * @param string|null $module Module code
     * @param string|null $folder Folder name
     * @return array Data for the dataset template
     */
    public function prepareDatasetData(
        string $paperType,
        int $paperID,
        $module = null,
        $folder = null
    ): array {
        return [
            'name' => 'dataset',
            'attributes' => [
                'papertype' => $paperType,
                'paperid' => $paperID,
                'module' => $module,
                'folder' => $folder
            ]
        ];
    }
}
