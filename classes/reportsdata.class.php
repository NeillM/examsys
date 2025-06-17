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
class ReportsData extends AbstractPageData
{

    /**
     * Get CSS files for the page
     * 
     * @return array Array of CSS file paths
     */
    protected function getCssFiles(): array
    {
        return [
            '/css/source/reports_form.css',
            '/css/source/breadcrumb.css'
        ];
    }
    
    /**
     * Get JavaScript files for the page
     * 
     * @return array Array of JavaScript file paths
     */
    protected function getScriptFiles(): array
    {
        return ['/js/reportsinit.min.js'];
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
        ?string $module = null,
        ?string $folder = null
    ): array {
        // Get paper type name
        $paperType = $properties->get_paper_type();
        
        // Prepare breadcrumb data
        $breadcrumbData = new BreadcrumbData($this->string);
        $breadcrumb = $breadcrumbData->preparePaperBreadcrumb(
            $paperID,
            $properties,
            $module,
            $folder,
            $this->string['reports']
        );
        
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

        // Get metadata fields
        $metadata = $this->getMetadataFields($paperID, $properties);

        $data = [
            'paperID' => $paperID,
            'module' => $module,
            'folder' => $folder,
            'breadcrumb' => $breadcrumb,
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
            'meta_fields' => $metadata['fields'],
            'meta_no' => $metadata['count'],
        ];

        // Add reviews section data if paper type is appropriate
        if (in_array($paperType, [\assessment::TYPE_FORMATIVE, \assessment::TYPE_PROGRESS, \assessment::TYPE_SUMMATIVE, \assessment::TYPE_OFFLINE])) {
            $data['reviews_section'] = [
                'title' => $this->string['reviews'],
                'items' => $this->getReviewsData($properties)
            ];
        }

        if (!in_array($paperType, [\assessment::TYPE_SURVEY, \assessment::TYPE_OSCE])) {
            // Add item analysis section data
            $data['item_analysis_section'] = [
                'title' => $this->string['itemanalysis'],
                'items' => $this->getItemAnalysisData($properties)
            ];

            // Add cohort reports section data
            $data['cohort_reports_section'] = [
                'title' => $this->string['cohortreports'],
                'items' => $this->getCohortReportsData($properties, $paperID)
            ];

            // Add exports section data
            $data['exports_section'] = [
                'title' => $this->string['exports'],
                'items' => $this->getExportsData($properties, $paperID)
            ];
        }

        // Add textbox marking section data if paper has textbox questions and is not type 5
        if ($paperType != \assessment::TYPE_OFFLINE && $properties->q_type_exist('textbox')) {
            $data['textbox_marking_section'] = $this->getTextboxMarkingData($paperID);
        }

        // Add anomalies section data if anomaly detection is enabled for this paper type
        if (Anomaly::anomalyDetectionEnabled($properties->get_paper_type())) {
            $data['anomalies_section'] = $this->getAnomaliesData();
        }

        // Add survey-specific sections
        if ($paperType == \assessment::TYPE_SURVEY) {
            $data['survey_quantitative_reports_section'] = $this->getSurveyQuantitativeReportsData();
            $data['survey_qualitative_analysis_section'] = $this->getSurveyQualitativeAnalysisData();
            $data['survey_exports_section'] = $this->getSurveyExportsData();

            // add survey-specific form elements
            $data['year_options'] = $this->getYearOptions();
            $data['complete_datasets_checkbox'] = [
                'id' => 'completerpt',
                'name' => 'completerpt',
                'value' => '1',
                'checked' => false
            ];
        }

        // Add OSCE-specific sections
        if ($paperType == \assessment::TYPE_OSCE) {
            $data['osce_cohort_reports_section'] = $this->getOsceCohortReportsData();
            $data['osce_item_analysis_section'] = $this->getOsceItemAnalysisData();
            $data['osce_exports_section'] = $this->getOsceExportsData();
        }

        // Add peer review-specific sections
        if ($paperType == \assessment::TYPE_PEERREVIEW) {
            $data['peer_review_reports_section'] = $this->getPeerReviewReportsData($properties);
        }

        return $data;
    }



    /**
     * Get course options for the report
     *
     * @param int $paperID Paper ID
     * @return array Array of course options
     */
    protected function getCourseOptions(int $paperID): array
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
    protected function getModuleOptions(int $paperID, string $paperType): array
    {
        if ($paperType == \assessment::TYPE_SURVEY) {
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
    protected function getCohortDirectionOptions(): array
    {
        return [
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
    protected function getPercentageOptions(): array
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
    protected function getAbsentCheckboxData(): array
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
    protected function getStudentsOnlyCheckboxData(): array
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
    protected function getReviewsData(PaperProperties $properties): array
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
     * Get data for the item analysis section
     *
     * @param PaperProperties $properties Paper properties
     * @return array Item analysis section data
     */
    protected function getItemAnalysisData(PaperProperties $properties): array
    {
        $items = [];

        if ($properties->get_paper_type() != '5') {
            $items[] = [
                'url' => '../reports/frequency_discrimination_analysis.php?',
                'text' => $this->string['frequencyanalysis'],
                'class' => 'reports'
            ];
        }

        if (!$properties->unmarked_enhancedcalc()) {
            $items[] = [
                'url' => '../reports/cohort_obj_perform.php?',
                'text' => $this->string['learningobjectiveanalysis'],
                'class' => 'reports'
            ];
        }

        return $items;
    }

    /**
     * Get data for the cohort reports section
     *
     * @param PaperProperties $properties Paper properties
     * @param int $paperID Paper ID
     * @return array Cohort reports section data
     */
    protected function getCohortReportsData(PaperProperties $properties, int $paperID): array
    {
        $items = [];

        // Class totals link
        $items[] = [
            'url' => '../reports/class_totals.php?',
            'text' => $this->string['classtotals'],
            'class' => 'reports'
        ];

        // Excel and CSV export links
        if ($properties->unmarked_enhancedcalc(1) || $properties->unmarked_textbox(1)) {
            // Determine the specific reason for disabling
            $disabledReason = $properties->unmarked_enhancedcalc(1) 
                ? $this->string['reason_unmarked_enhancedcalc'] 
                : $this->string['reason_unmarked_textbox'];
                
            $items[] = [
                'url' => '',
                'text' => $this->string['classtotalsexcel2003'],
                'class' => 'reports',
                'disabled' => true,
                'disabledReason' => $disabledReason
            ];

            $items[] = [
                'url' => '',
                'text' => $this->string['classtotalscsv'],
                'class' => 'reports',
                'disabled' => true,
                'disabledReason' => $disabledReason
            ];
        } else {
            $items[] = [
                'url' => '../reports/class_totals_xml.php?',
                'text' => $this->string['classtotalsexcel2003'],
                'class' => 'reports'
            ];

            $items[] = [
                'url' => '../reports/class_totals_csv.php?',
                'text' => $this->string['classtotalscsv'],
                'class' => 'reports'
            ];
        }

        return $items;
    }

    /**
     * Get data for the exports section
     *
     * @param PaperProperties $properties Paper properties
     * @param int $paperID Paper ID
     * @return array Exports section data
     */
    protected function getExportsData(PaperProperties $properties, int $paperID): array
    {
        $items = [];

        // Export responses as CSV
        if ($properties->get_paper_type() != \assessment::TYPE_OFFLINE) {
            $items[] = [
                'url' => '../export/assessment_data.php?',
                'text' => $this->string['exportresponsescsvnum'],
                'class' => 'reports'
            ];

            $items[] = [
                'url' => '../export/assessment_data.php?mode=text&',
                'text' => $this->string['exportresponsescsvtext'],
                'class' => 'reports'
            ];

            $items[] = [
                'url' => '../export/assessment_boolean.php?',
                'text' => $this->string['exportbooleancsv'],
                'class' => 'reports'
            ];
        }

        // Export marks as CSV
        if ($properties->unmarked_enhancedcalc()) {
            $items[] = [
                'url' => '',
                'text' => $this->string['exportmarkscsv'],
                'class' => 'reports',
                'disabled' => true,
                'disabledReason' => $this->string['reason_unmarked_enhancedcalc']
            ];
        } else {
            $items[] = [
                'url' => '../export/assessment_marks.php?',
                'text' => $this->string['exportmarkscsv'],
                'class' => 'reports'
            ];
        }

        // Add standards setting links if appropriate
        $paperType = $properties->get_paper_type();
        if (($paperType == \assessment::TYPE_FORMATIVE || $paperType == \assessment::TYPE_PROGRESS || $paperType == \assessment::TYPE_SUMMATIVE)) {
            $checklist = $properties->get_checklist();

            // Add standards setting links if stdset is in the checklist
            if (mb_strpos($checklist, 'stdset') !== false) {
                $items[] = [
                    'url' => '../reports/standards_setting_csv.php?',
                    'text' => $this->string['standardssettingcsv'],
                    'class' => 'reports'
                ];

                $items[] = [
                    'url' => '../reports/standards_setting_full_csv.php?',
                    'text' => $this->string['standardssettingfullcsv'],
                    'class' => 'reports'
                ];
            }
        }

        return $items;
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
    public function prepareFooterData(?string $module = null, ?string $folder = null): array
    {
        return [
            'scripts' => ['/js/reportsinit.min.js'],
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
        ?string $module = null,
        ?string $folder = null
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

    /**
     * Get metadata fields for the report form
     *
     * @param int $paperID Paper ID
     * @param PaperProperties $properties Paper properties
     * @return array Array of metadata fields and count
     */
    protected function getMetadataFields(int $paperID, PaperProperties $properties): array
    {
        $metadata = [];
        $metaFields = [];

        // Get modules for this paper
        $moduleIDs = Paper_utils::get_modules($paperID, $this->db);

        if (count($moduleIDs) > 0) {
            // Get metadata fields from database
            $moduleIDKeys = array_keys($moduleIDs);
            $stmt = $this->db->prepare("SELECT DISTINCT type, value 
                                      FROM users_metadata 
                                      WHERE idMod IN (" . implode(',', $moduleIDKeys) . ") 
                                      ORDER BY type, value");
            $stmt->execute();
            $stmt->bind_result($meta_type, $meta_value);

            while ($stmt->fetch()) {
                $metadata[$meta_type][] = $meta_value;
            }

            $stmt->close();

            // Format metadata fields for the template
            $metaNo = 1;
            foreach ($metadata as $meta_type => $value_array) {
                $paperType = $properties->get_paper_type();
                if ($paperType != \assessment::TYPE_PEERREVIEW || $properties->get_rubric() == $meta_type) {
                    $options = [];

                    // Add "All" option
                    $options[] = [
                        'value' => "$meta_type=%",
                        'text' => '<' . $this->string['all'] . '>'
                    ];

                    // Add options for each value
                    foreach ($value_array as $meta_value) {
                        $options[] = [
                            'value' => "$meta_type=$meta_value",
                            'text' => $meta_value
                        ];
                    }

                    // Add field to the list
                    $metaFields[] = [
                        'id' => 'meta' . $metaNo,
                        'name' => 'meta' . $metaNo,
                        'label' => $meta_type,
                        'options' => $options
                    ];

                    $metaNo++;
                }
            }
        }

        return [
            'fields' => $metaFields,
            'count' => count($metaFields) > 0 ? count($metaFields) + 1 : 1
        ];
    }

    /**
     * Get textbox marking section data
     *
     * @param int $paperID Paper ID
     * @return array Textbox marking section data
     */
    protected function getTextboxMarkingData(int $paperID): array
    {
        $items = [];

        // Primary Mark by Question link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/textbox_select_q.php?action=mark&phase=1&',
            'text' => $this->string['primarymarkbyquestion']
        ];

        // Select Papers for Remarking link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/textbox_remark.php?',
            'text' => $this->string['selectpapersforremarking']
        ];

        // Second Mark by Question link (conditional)
        $remark_array = textbox_marking_utils::get_remark_users($paperID, $this->db);
        if (count($remark_array) > 0) {
            $items[] = [
                'class' => 'reports',
                'url' => '../reports/textbox_select_q.php?action=mark&phase=2&',
                'text' => $this->string['secondmarkbyquestion']
            ];
        } else {
            $items[] = [
                'class' => 'reports',
                'text' => $this->string['secondmarkbyquestion'],
                'disabled' => true,
                'disabledReason' => $this->string['reason_no_remark_users']
            ];
        }

        // Finalise Marks link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/textbox_select_q.php?action=finalise&',
            'text' => $this->string['finalisemarks']
        ];

        return [
            'title' => $this->string['textboxmarking'],
            'items' => $items
        ];
    }

    /**
     * Get anomalies section data
     *
     * @return array Anomalies section data
     */
    protected function getAnomaliesData(): array
    {
        $items = [];

        // Anomalies link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/anomalies.php?',
            'text' => $this->string['anomalies']
        ];

        return [
            'title' => $this->string['misc'],
            'items' => $items
        ];
    }

    /**
     * Get survey quantitative reports section data
     *
     * @return array Survey quantitative reports section data
     */
    protected function getSurveyQuantitativeReportsData(): array
    {
        $items = [];

        // XHTML link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/quantitative.php?',
            'text' => $this->string['xhtml']
        ];

        // Word 2003 format link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/quantitative_xml.php?',
            'text' => $this->string['word2003format']
        ];

        return [
            'title' => $this->string['quantitativereports'],
            'items' => $items
        ];
    }

    /**
     * Get survey qualitative analysis section data
     *
     * @return array Survey qualitative analysis section data
     */
    protected function getSurveyQualitativeAnalysisData(): array
    {
        $items = [];

        // XHTML link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/qualitative.php?',
            'text' => $this->string['xhtml']
        ];

        // Word 2003 format link
        $items[] = [
            'class' => 'reports',
            'url' => '../reports/qualitative_xml.php?',
            'text' => $this->string['word2003format']
        ];

        return [
            'title' => $this->string['qualitativeanalysis'],
            'items' => $items
        ];
    }

    /**
     * Get survey exports section data
     *
     * @return array Survey exports section data
     */
    protected function getSurveyExportsData(): array
    {
        $items = [];

        // Raw data XML link
        $items[] = [
            'class' => 'reports',
            'url' => '../export/survey_xml_data.php?',
            'text' => $this->string['rawdataxml']
        ];

        // Raw data CSV link
        $items[] = [
            'class' => 'reports',
            'url' => '../export/survey_csv_data.php?',
            'text' => $this->string['rawdatacsv']
        ];

        return [
            'title' => $this->string['exports'],
            'items' => $items
        ];
    }

    /**
     * Get year options for survey papers
     *
     * @return array Array of year options
     */
    protected function getYearOptions(): array
    {
        $options = [];

        // Any year option
        $options[] = [
            'value' => '%',
            'text' => $this->string['anyyear']
        ];

        // Year 1-5 options
        for ($i = 1; $i <= 5; $i++) {
            $options[] = [
                'value' => $i,
                'text' => $this->string['year'] . ' ' . $i
            ];
        }

        return $options;
    }

    /**
     * Get OSCE cohort reports section data
     *
     * @return array OSCE cohort reports section data
     */
    protected function getOsceCohortReportsData(): array
    {
        $items = [];

        // Class totals link
        $items[] = [
            'class' => 'reports',
            'url' => '../osce/class_totals.php?',
            'text' => $this->string['classtotals']
        ];

        // Class totals Excel 2003 link
        $items[] = [
            'class' => 'reports',
            'url' => '../osce/class_totals_xml.php?',
            'text' => $this->string['classtotalsexcel2003']
        ];

        // Class totals CSV link
        $items[] = [
            'class' => 'reports',
            'url' => '../osce/class_totals_csv.php?',
            'text' => $this->string['classtotalscsv']
        ];

        return [
            'title' => $this->string['cohortreports'],
            'items' => $items
        ];
    }

    /**
     * Get OSCE item analysis section data
     *
     * @return array OSCE item analysis section data
     */
    protected function getOsceItemAnalysisData(): array
    {
        $items = [];

        // Frequency analysis link
        $items[] = [
            'class' => 'reports',
            'url' => '../osce/frequency_analysis.php?',
            'text' => $this->string['frequencyanalysis']
        ];

        return [
            'title' => $this->string['itemanalysis'],
            'items' => $items
        ];
    }

    /**
     * Get OSCE exports section data
     *
     * @return array OSCE exports section data
     */
    protected function getOsceExportsData(): array
    {
        $items = [];

        // Individual portfolio sheets link
        $items[] = [
            'class' => 'reports',
            'url' => '../osce/portfolio_sheets.php?',
            'text' => $this->string['individualportfoliosheets']
        ];

        // Export ratings CSV link
        $items[] = [
            'class' => 'reports',
            'url' => '../osce/export_ratings.php?',
            'text' => $this->string['exportratingscsv']
        ];

        return [
            'title' => $this->string['exports'],
            'items' => $items
        ];
    }

    /**
     * Get peer review reports section data
     *
     * @param PaperProperties $properties Paper properties
     * @return array Peer review reports section data
     */
    protected function getPeerReviewReportsData(PaperProperties $properties): array
    {
        $items = [];

        if ($properties->get_display_question_mark() == '1') {
            $items[] = [
                'class' => 'reports',
                'url' => '../peer_review/summary_report.php?percent=0&',
                'text' => $this->string['ReviewSummary1']
            ];

            $items[] = [
                'class' => 'reports',
                'url' => '../peer_review/summary_report.php?percent=1&',
                'text' => $this->string['ReviewSummary2']
            ];
        } else {
            $items[] = [
                'class' => 'reports',
                'url' => '../peer_review/summary_report.php?',
                'text' => $this->string['ReviewSummary3']
            ];
        }

        $items[] = [
            'class' => 'reports',
            'url' => '../peer_review/summary_report_csv.php?',
            'text' => $this->string['ReviewSummary4']
        ];

        return [
            'title' => $this->string['reports'],
            'items' => $items
        ];
    }
}
