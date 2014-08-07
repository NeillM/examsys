<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

require_once '../lang/' . $language . '/include/question_types.inc';
require_once '../lang/' . $language . '/include/paper_types.inc';
require_once '../lang/' . $language . '/include/blooms.inc';

$string['questionbank'] = 'Bank pytań';
$string['bytype'] = 'wg. typu';
$string['byblooms'] = 'wg. taksonomii Blooma';
$string['bykeyword'] = 'wg. słowa klucz.';
$string['bystatus'] = 'wg. statusu';
$string['bydifficulty'] = 'wg. trudności';
$string['bydiscrimination'] = 'wg. różnicowania';
$string['byperformance'] = 'wg. osiągnięć';
$string['byobjective'] = 'wg. celów ksztacenia';
$string['manageobjectives'] = 'Zarządzaj celami';
$string['managekeywords'] = 'Zarządzaj słowami kluczowymi';
$string['referencematerial'] = 'Materiał pomocniczy';
$string['nokeywords'] = 'Do tego modułu nie dodano żadnych słów kluczowych.';
$string['question'] = 'Pytanie';
$string['questions'] = 'Pytania';
$string['noquestions'] = 'No questions found in bank.';
$string['noquestionsbloom']  = 'No questions found in bank by Bloom\'s Taxonomy.';
$string['noquestionsstatus'] = 'No questions found in bank by status.';
$string['noquestionsperformance'] = 'No questions found in bank by performance.';
$string['noquestionsobjective'] = 'No questions found link bank linked to learning objectives.';
?>