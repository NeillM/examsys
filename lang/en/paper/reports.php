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

// Page title and headings
$string['reports'] = 'Reports';
$string['reportspage'] = 'Reports Page';

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
$string['allcandidates'] = 'All candidates';
$string['top'] = 'Top';
$string['bottom'] = 'Bottom';

// Checkbox options
$string['incabsentcandidates'] = 'Include absent candidates';
$string['studentsonly'] = 'Students only';
$string['tooltip_studentattempts'] = 'Only include attempts from students (exclude staff attempts)';

// Report sections
$string['reviews'] = 'Reviews';
$string['internalpeerreview'] = 'Internal Peer Review';
$string['sctresponses'] = 'SCT Responses';
$string['externalexaminers'] = 'External Examiners';

// Item analysis section
$string['itemanalysis'] = 'Item Analysis';
$string['frequencyanalysis'] = 'Frequency Analysis';
$string['learningobjectiveanalysis'] = 'Learning Objective Analysis';

// Cohort reports section
$string['cohortreports'] = 'Cohort Reports';
$string['classtotals'] = 'Class Totals';
$string['classtotalsexcel2003'] = 'Class Totals (Excel 2003 Format)';
$string['classtotalscsv'] = 'Class Totals (CSV Format)';