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

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style>
    body {font-size:75%}
    .icon {width:56px}
  </style>
</head>
<body>
<?php
  $question_types = array();
  $question_types['area']['desc'] = 'Allows student to specify an area as their answer';
  $question_types['area']['title'] = 'Area';
  
  $question_types['enhancedcalc']['desc'] = 'Numeric answer entry based on questions with random variables.';
  $question_types['enhancedcalc']['title'] = 'Calculation';
  
  $question_types['dichotomous']['desc'] = 'Presentation of multiple true/false questions.';
  $question_types['dichotomous']['title'] = 'Dichotomous';
  
  $question_types['extmatch']['desc'] = 'Presentation of multiple scenarios sharing a common set of answer options.';
  $question_types['extmatch']['title'] = 'Extended Matching';
  
  $question_types['blank']['desc'] = 'A paragraph of text with blanks inserted which the student completes.';
  $question_types['blank']['title'] = 'Fill-in-the-Blank';
  
  $question_types['info']['desc'] = 'Not a question as such - this provides information to the student to assist them with the rest of the questions/paper.';
  $question_types['info']['title'] = 'Information Block';
  
  $question_types['matrix']['desc'] = 'Match questions to answers in a matrix presentation.';
  $question_types['matrix']['title'] = 'Matrix';
  
  $question_types['hotspot']['desc'] = 'Student has to click on the correct part of an image. Multiple parts can be presented in a single question.';
  $question_types['hotspot']['title'] = 'Image Hotspot';
  
  $question_types['labelling']['desc'] = 'Student has to drag labels to the correct placeholders on top of a background image.';
  $question_types['labelling']['title'] = 'Labelling';
 
  $question_types['likert']['desc'] = 'Psychometric scale for use on surveys.';
  $question_types['likert']['title'] = 'Likert Scale';
 
  $question_types['mcq']['desc'] = 'Pick one correct option from many.';
  $question_types['mcq']['title'] = 'Multiple Choice Question (MCQ)';
  
  $question_types['mrq']['desc'] = 'Pick several correct options from many.';
  $question_types['mrq']['title'] = 'Multiple Response';
  
  $question_types['keyword_based']['desc'] = "This question is a container for a set of 'source' questions based on a specified keyword, one of which will be choosen at random when sat by a student";
  $question_types['keyword_based']['title'] = 'Keyword-Based';
  
  $question_types['random']['desc'] = "This question is a container for a set of 'source' questions, one of which will be choosen at random when sat by a student.";
  $question_types['random']['title'] = 'Random';
  
  $question_types['rank']['desc'] = 'Rank a set of options in order.';
  $question_types['rank']['title'] = 'Ranking';
  
  $question_types['sct']['desc'] = 'Questions designed to assess clinical data interpretation skills.';
  $question_types['sct']['title'] = 'Script Concordance Test';
  
  $question_types['textbox']['desc'] = 'Textboxes capture free-text student responses. Can be used in surveys and assessments. Textbox answers on assessments require manual marking by academics.';
  $question_types['textbox']['title'] = 'Textbox';
  
  $question_types['true_false']['desc'] = 'A single question which is answered True or False.';
  $question_types['true_false']['title'] = 'True/False';
  
  $break_no = round(count($question_types) / 2);
?>
  <table cellspacing="0" cellpadding="0" border="0">
<?php
foreach ($question_types as $type=>$details) {
  echo "<tr><td class=\"icon\"><img src=\"../artwork/new_$type.png\" width=\"48\" height=\"48\" /></td><td><strong>" . $details['title'] . "</strong><br />" . $details['desc'] . "</td></tr>\n";
}
?>
  </table>  
</body>
</html>