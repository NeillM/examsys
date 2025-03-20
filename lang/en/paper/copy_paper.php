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

require_once $cfg_web_root . 'lang/' . $language . '/include/paper_types.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/months.php';

// Copy paper specific strings
$string['copypaper'] = 'Copy Paper';
$string['copyname'] = 'New Paper Name';
$string['type'] = 'Type';
$string['academicsession'] = 'Academic Session';
$string['copystdsetting'] = 'Copy standard settings';
$string['copytype'] = 'Copy Type';
$string['paperonly'] = 'Paper Only';
$string['paperandquestions'] = 'Paper and Questions';
$string['cancel'] = 'Cancel';
$string['back'] = 'Back';
$string['next'] = 'Next';

// Summative exam fields
$string['summativedetails'] = 'Summative Details';
$string['campus'] = 'Campus';
$string['barriersneeded'] = 'Barriers needed';
$string['duration'] = 'Duration';
$string['hrs'] = 'Hours';
$string['mins'] = 'Minutes';
$string['daterequired'] = 'Date Required';
$string['cohortsize'] = 'Cohort Size';
$string['wholecohort'] = 'whole cohort';
$string['sittings'] = 'Number of Sittings';
$string['notes'] = 'Notes';

// Validation messages
$string['fieldrequired'] = 'This field is required';
$string['invalidduration'] = 'Please enter a valid duration';
$string['maxdurationexceeded'] = 'Duration cannot exceed the maximum allowed';
$string['requiredfields'] = 'Fields marked with asterisks are required';
