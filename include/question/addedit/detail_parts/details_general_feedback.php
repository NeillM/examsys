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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

$show_incorrect = (isset($show_incorrect)) ? $show_incorrect : false;
$mandatory_editor = false;
$field_editor = (isset($field_correct)) ? $field_correct : 'correct_fback';
$label_editor = (isset($label_correct)) ? $label_correct : '<label for="' . $field_editor . '">' . $string['generalfeedback'] . '</label>';
$value_editor =  $question->get_correct_fback();
?>
<table id="q-feedback" class="form">
  <tbody>
<?php
    require 'details_editor.php';

    if ($show_incorrect) {
        $field_editor = (isset($field_incorrect)) ? $field_incorrect : 'incorrect_fback';
        $label_editor = (isset($label_incorrect)) ? $label_incorrect : '<label for="' . $field_editor . '">' . $string['fbincorrect'] . '</label><br /><span class="note">' . $string['fbincorrectmsg'] . '</span>';
        $value_editor = $question->get_incorrect_fback();
        require 'details_editor.php';
    }
?>
  </tbody>
</table>
