<?php
require '../lang/' . $language . '/include/paper_options.inc';
require 'shared.inc';
require '../lang/' . $language . '/question/edit/likert_scales.php';

$string['import'] = 'Import'; //cognate
$string['import2'] = 'Importuj';
$string['importfromqti'] = 'Importuj z QTI';
$string['file'] = 'plik';
$string['qtiimporterror'] = 'Błąd importowania pliku QTI';
$string['qtiimported'] = 'Zaimportowano plik QTI';
$string['questionproblems'] = 'Niektóre z pytań nie zostały zaimportowane poprawnie.';
$string['hadproblemsimporting'] = 'Błąd importowania %d z %d pytań.';
$string['importedquestions'] = 'Zaimportowano %d pytań.';
$string['backtopaper'] = 'Powrót do arkusza';
$string['errmsg1'] = 'Ten typ eksportu nie jest obsługiwany';
$string['errmsg2'] = 'Ten typ importu nie jest obsługiwany';
// Niko
$string['invalidxml'] = '%s is an invalid XML file';
$string['invalidzip'] = 'Invalid Zip file Uploaded';
$string['noqtiinzip'] = 'No QTI XML files in the zip file';
$string['qunsupported'] = 'Question type %s not yet supported';
$string['noresponsegroups'] = 'Response groups are not currently supported.';
$string['norenderextensions'] = 'Render extensions are not currently supported.';
$string['mrq1other'] = 'Multiple Response - 1 mark per True Option with Other';
$string['nomultiplecard'] = 'All sets of labels are different and we have multiple cardinality, question is not supported in touchstone.';
$string['labelsetserror'] = 'Label sets for all question stems arent the same, prehaps this should be imprted as a blank with dropdowns??';
$string['nomultiinputs'] = 'Questions with multiple numeric imputs cannot be imported';
$string['blanktypeerror'] = 'Blank type question with not dropdowns or text entries';
$string['addingsub'] = 'Adding sub item - render_fib with no children';
$string['posnocond'] = 'Positive outcome with no condition, unable to work out correct answer';
$string['multiplepos'] = 'Multiple positive values on outcome, correct answer may be wrong';
$string['multiposmultiopt'] = 'Multiple positive outcomes, with multiple options on an outcome, correct answer may be wrong';
$string['nomatchinglabel'] = 'Unable to find label matching information';
$string['nolikertfeedback'] = 'Rog&#333; doesn\'t store any feedback for likert questions so it has been lost';
$string['nocorrect'] = 'Unable to find a correct answer';
$string['multipleconds'] = 'Found multiple conditions that are scoring the question, ignoring all but the 1st';
$string['mrqnoismulti'] = 'Trying to load MRQ without ismulti set!';
$string['importingtext'] = 'Importing text entry question with marking criteria. This will not be automatically marked in Rog&#333;';

$string['someneg'] = 'Some negatives - 1 mark per true option with negative';
$string['noneg'] = 'No negatives and multiple positives, 1 mark per true option';

$string['qtiimport'] = 'QTI Import';
$string['imported1_2'] = 'Imported from QTI 1.2 file';
$string['paperlocked'] = 'Paper Locked';
$string['paperlockedmsg'] = 'This paper is now locked and cannot be modified.';

$string['loadingsection'] = 'Loading section';
$string['loadingblank'] = 'Loading blank string';
$string['loadingblankdrop'] = 'Loading blank dropdown';
$string['fileoutput'] = 'File Output';