<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

require '../lang/' . $language . '/include/paper_options.inc';
require 'shared.inc';
require '../lang/' . $language . '/question/edit/likert_scales.php';

$string['import'] = 'Importovat';
$string['import2'] = 'Importovat';
$string['importfromqti'] = 'Importovat z QTI';
$string['file'] = 'Soubor';
$string['qtiimporterror'] = 'Během importu Vašeho QTI souboru došlo k chybě.';
$string['qtiimported'] = 'Váš QTI soubor byl importován';
$string['questionproblems'] = 'Některé z Vašich úloh nebyly naimportovány správně.';
$string['hadproblemsimporting'] = '%d z %d úloh mělo potíže s importem.';
$string['importedquestions'] = 'Importováno %d úloh.';
$string['backtopaper'] = 'Zpět na dokument';
$string['errmsg1'] = 'Tento typ exportu není podporován';
$string['errmsg2'] = 'Tento typ importu není podporován';
$string['invalidxml'] = '%s je neplatný XML soubor';
$string['invalidzip'] = 'Nahrán neplatný Zip soubor';
//$string['invalidzip2'] = '%s is an invalid XML file';
$string['noqtiinzip'] = 'V Zip souboru nejsou žádné QTI XML soubory';
$string['qunsupported'] = 'Typ úlohy %s není podporován';
$string['noresponsegroups'] = 'Skupiny nejsou v současnosti podporovány.';
$string['norenderextensions'] = 'Render extensions nejsou v současnosti podporovány.';
$string['mrq1other'] = 'Vícenásobná odpověď - 1 známka za správnou odpověď 1 mark per True Option with Other';
$string['nomultiplecard'] = 'All sets of labels are different and we have multiple cardinality, question is not supported in Rog&#333;.';
$string['labelsetserror'] = 'Label sets for all question stems arent the same, prehaps this should be imported as a blank with dropdowns??';
$string['nomultiinputs'] = 'Úlohy s mnohačetnými numerickými vstupy nelze naimporotvat';
$string['blanktypeerror'] = 'Úloha doplňovacího typu postrádá rozbalovací nabídku či vkládaný text';
$string['addingsub'] = 'Adding sub item - render_fib with no children';
$string['posnocond'] = 'Positive outcome with no condition, unable to work out correct answer';
$string['multiplepos'] = 'Multiple positive values on outcome, correct answer may be wrong';
$string['multiposmultiopt'] = 'Multiple positive outcomes, with multiple options on an outcome, correct answer may be wrong';
$string['nomatchinglabel'] = 'Unable to find label matching information';
$string['nolikertfeedback'] = 'Rog&#333; doesn\'t store any Komentář for likert questions so it has been lost';
$string['nocorrect'] = 'Nelze najít správnou odpověď';
$string['multipleconds'] = 'Found multiple conditions that are scoring the question, ignoring all but the 1st';
$string['mrqnoismulti'] = 'Trying to load MRQ without ismulti set!';
$string['importingtext'] = 'Importing text entry question with marking criteria. This will not be automatically marked in Rog&#333;';
$string['someneg'] = 'Some negatives - 1 mark per true option with negative';
$string['noneg'] = 'No negatives and multiple positives, 1 mark per true option';

$string['qtiimport'] = 'Importovat QTI ';
$string['imported1_2'] = 'Importováno ze souboru QTI 1.2';
$string['paperlocked'] = 'Dokument je uzamčen';
$string['paperlockedmsg'] = 'Tento dokument je v současnosti uzamčen a nelze jej upravovat.';

$string['loadingsection'] = 'Loading section';
$string['loadingblank'] = 'Loading blank string';
$string['loadingblankdrop'] = 'Loading blank dropdown';
$string['fileoutput'] = 'File Output';

$string['type'] = 'Typ dokumentu';
?>