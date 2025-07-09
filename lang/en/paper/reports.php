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

require_once $cfg_web_root . 'lang/' . $language . '/include/months.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/paper_types.php';

// Page title and headings
$string['reports'] = 'Reports';
$string['reportspage'] = 'Reports Page';
$string['title'] = 'Reports';

// Paper information
$string['paperid'] = 'Paper ID';
$string['papertitle'] = 'Paper Title';
$string['papertype'] = 'Paper Type';

// Date functionality
$string['dates'] = 'Dates';
$string['to'] = 'to';
$string['tooltip_daterange'] = 'Select a date range for your report';
$string['datefilter'] = 'Date Filter';
$string['applyfilter'] = 'Apply Filter';
$string['resetfilter'] = 'Reset Filter';

// Course and module filters
$string['course'] = 'Course';
$string['anycourse'] = 'Any Course';
$string['module'] = 'Module';
$string['anymodule'] = 'Any Module';

// Cohort filters
$string['cohort'] = 'Cohort';
$string['direction'] = 'Direction';
$string['percentage'] = 'Percentage';
$string['allcandidates'] = 'All candidates';
$string['top'] = 'Top';
$string['bottom'] = 'Bottom';

// Checkbox options
$string['incabsentcandidates'] = 'Include absent candidates';
$string['studentsonly'] = 'Students only';
$string['tooltip_studentattempts'] = 'Only include attempts from students (exclude staff attempts)';

// Filter section headings
$string['reportfilters'] = 'Report Filters';
$string['datefiltersection'] = 'Date Filter';
$string['coursefiltersection'] = 'Course Filter';
$string['modulefiltersection'] = 'Module Filter';
$string['cohortfiltersection'] = 'Cohort Filter';
$string['optionssection'] = 'Report Options';
$string['surveyoptionssection'] = 'Survey Options';

// Report sections
$string['reviews'] = 'Reviews';
$string['internalpeerreview'] = 'Internal Peer Review';
$string['sctresponses'] = 'SCT Responses';
$string['externalexaminers'] = 'External Examiners';

// Item analysis section
$string['itemanalysis'] = 'Item Analysis';
$string['frequencyanalysis'] = 'Frequency & Discrimination (U-L) Analysis';
$string['learningobjectiveanalysis'] = 'Learning Objective Analysis';

// Cohort reports section
$string['cohortreports'] = 'Cohort Reports';
$string['classtotals'] = 'Class Totals';
$string['classtotalsexcel2003'] = 'Class Totals (Excel 2003)';
$string['classtotalscsv'] = 'Class Totals (CSV)';

// Exports section
$string['exports'] = 'Exports';
$string['exportresponsescsvnum'] = 'Export responses as CSV file (raw)';
$string['exportresponsescsvtext'] = 'Export responses as CSV file (text)';
$string['exportbooleancsv'] = 'Export boolean responses as CSV file';
$string['exportmarkscsv'] = 'Export marks as CSV file';
$string['standardssettingcsv'] = 'Standards Setting summary as CSV file';
$string['standardssettingfullcsv'] = 'Standards Setting full responses as CSV file';

// Metadata
$string['metadata'] = 'Metadata';
$string['all'] = 'All';

// Textbox marking section
$string['textboxmarking'] = 'Textbox Marking';
$string['primarymarkbyquestion'] = 'Primary Mark by Question';
$string['selectpapersforremarking'] = 'Select Papers for Remarking';
$string['secondmarkbyquestion'] = 'Second Mark by Question';
$string['finalisemarks'] = 'Finalise Marks';

// Anomalies section
$string['misc'] = 'Misc';
$string['anomalies'] = 'Anomalies';

// Survey reports section
$string['quantitativereports'] = 'Quantitative Reports';
$string['qualitativeanalysis'] = 'Qualitative Analysis';
$string['xhtml'] = 'XHTML';
$string['word2003format'] = 'Word 2003 Format';
$string['rawdataxml'] = 'Raw Data XML';
$string['rawdatacsv'] = 'Raw Data CSV';

// Survey form elements
$string['year'] = 'Year';
$string['anyyear'] = 'Any Year';
$string['completedatasets'] = 'Complete datasets only';

// OSCE reports section
$string['individualportfoliosheets'] = 'Individual Portfolio Sheets';
$string['exportratingscsv'] = 'Export Ratings as CSV';

// Peer review reports section
$string['ReviewSummary1'] = 'Review Summary (with marks)';
$string['ReviewSummary2'] = 'Review Summary (with percentages)';
$string['ReviewSummary3'] = 'Review Summary';
$string['ReviewSummary4'] = 'Review Summary (CSV)';
$string['datefilter'] = 'Date Filter';
$string['coursefilter'] = 'Course Filter';
$string['modulefilter'] = 'Module Filter';
$string['cohortfilter'] = 'Cohort Filter';
$string['reportoptions'] = 'Report Options';
$string['surveyoptions'] = 'Survey Options';

// Disabled link reasons
$string['unavailable'] = 'unavailable';
$string['reason_unmarked_questions'] = 'This option is unavailable because there are unmarked questions.';
$string['reason_unmarked_textbox'] = 'This option is unavailable because there are unmarked textbox questions.';
$string['reason_unmarked_enhancedcalc'] = 'This option is unavailable because there are unmarked enhanced calculation questions.';
$string['reason_no_remark_users'] = 'This option is unavailable because no papers have been selected for remarking.';

// Accessibility labels for date selectors
$string['day'] = 'Day';
$string['month'] = 'Month';
$string['start_date_day'] = 'Start date day';
$string['start_date_month'] = 'Start date month';
$string['start_date_year'] = 'Start date year';
$string['start_date_hour'] = 'Start date hour';
$string['start_date_minute'] = 'Start date minute';
$string['end_date_day'] = 'End date day';
$string['end_date_month'] = 'End date month';
$string['end_date_year'] = 'End date year';
$string['end_date_hour'] = 'End date hour';
$string['end_date_minute'] = 'End date minute';
