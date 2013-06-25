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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

$vars = $question->get_variables();
$num_vars = count($vars);
$decimals = array('', 0, 1, 2, 3, 4, 5, 6, 7, 8);
$increments = array('', 0.0001, 0.001, 0.02, 0.01, 0.5, 0.2, 0.1, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 50, 100, 1000);
$labels = $question->get_variable_labels();
if (count($question->options) > 0) {
  $first = reset($question->options);
  $formula = $first->get_correct();
  $marks = $first->get_marks_correct();
} else {
  $formula = '';
  $marks = 1;
}
?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php require_once 'details_common.php' ?>
					</tbody>
				</table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['variables'] ?></h2>
        </div>

        <table id="q-options" class="form" summary="Edit question variables">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th class="align-left"><?php echo $string['min'] ?></th>
              <th class="align-left"><?php echo $string['max'] ?></th>
              <th class="align-left"><?php echo $string['decimals'] ?></th>
              <th class="align-left"><?php echo $string['increment'] ?></th>
            </tr>
          </thead>
<?php
// TODO: linking options
$index = 1;
//foreach ($question->options as $o_id => $option) {
//  $option->set_variable($labels[$index-1]);
//  include 'options/opt_calculation.php';
//  $index++;
//}
//
//for ($index = $num_vars + 1; $index <= count($labels); $index++) {
//  $option = OptionEdit::option_factory($mysqli, $userObject->get_user_ID(), $question, $index, $string);
//  $option->set_variable($labels[$index-1]);
//  include 'options/opt_calculation.php';
//}

foreach ($vars as $variable) {
  include 'options/opt_extendedcalc.php';
  $index++;
}

for ($index = $num_vars + 1; $index <= count($labels); $index++) {
  $variable = new CalculationVar('$' . $labels[$index-1], '', '', '', '');
  include 'options/opt_extendedcalc.php';
}

if($question->get_locked() == '') {
?>
          <tbody class="add-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="4">
                <input name="next-option" class="next-option" value="<?php echo $string['addoptions'] ?>" type="button">
              </td>
            </tr>
          </tbody>
<?php
}
?>
        </table>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['answer'] ?></h2>
        </div>

        <table id="q-options" class="form" summary="Edit question formulae">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th class="align-left auto">Formula <span class="note indent"><a href="#" class="help-link" rel="68"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['onlinehelp'] ?>" border="0" /></a>&nbsp;<a href="#" class="help-link" rel="68"><?php echo $string['suppfunctions'] ?></a></span></th>
              <th class="align-left auto">Units</th>
            </tr>
          </thead>
<?php
for ($i = 0; $i < $question->max_answers; $i++) {
  include 'options/ans_extendedcalc.php';
}
?>
          <tbody class="add-option-holder">
            <tr>
              <td>&nbsp;</td>
              <td colspan="2" class="align-left">
                <input name="next-answer" class="next-option" value="Add More Answers..." type="button" data-target="answer">
              </td>
            </tr>
          </tbody>
          <tbody>
            <tr>
              <th class="spaced-top"><label for="display_units" style="padding:0">Display units for question</label></th>
              <td class="spaced-top"><input type="checkbox" name="display_units" id="display_units"></td>
            </tr>
            <tr>
              <th class="spaced-top"><label for="unit_marks">Unit marking</label></th>
              <td class="spaced-top" colspan="2">
                <select name="unit_marks" id="unit_marks">
                  <option value="0">N/A</option>
                  <option value="incorrect">Award zero for question</option>
                  <option value="-0.25">-0.25</option>
                  <option value="-0.5">-0.5</option>
                  <option value="-1">-1</option>
                  <option value="-2">-2</option>
                  <option value="-3">-3</option>
                  <option value="-4">-4</option>
                  <option value="-5">-5</option>
                  <option value="-6">-6</option>
                  <option value="-7">-7</option>
                  <option value="-8">-8</option>
                  <option value="-9">-9</option>
                  <option value="-10">-10</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
<hr>





        <table id="q-options" class="form" summary="Edit question variables">
          <tbody>
            <tr>
              <th>
                <label for="option_correct"><span class="mandatory">*</span><?php echo $string['formula'] ?></label><br />
                <span class="note"><a href="#" class="help-link" rel="68"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['onlinehelp'] ?>" border="0" /></a>&nbsp;<a href="#" class="help-link" rel="68"><?php echo $string['suppfunctions'] ?></a></span>
              </th>
              <td colspan="2">
                <textarea id="option_correct" name="option_correct" cols="100" rows="3" class="form-large"><?php echo $formula ?></textarea>
              </td>
            </tr>
            <tr>
              <th class="spaced-top"><label for="units"><?php echo $string['units'] ?></label></th>
              <td class="spaced-top"><input type="text" id="units" name="units" value="<?php echo $question->get_units() ?>" /></td>
              <td class="spaced-top">
                <label for="answer_decimals" class="spaced-right"><strong><?php echo $string['decimals'] ?></strong></label>
                <select id="answer_decimals" name="answer_decimals">
<?php
echo ViewHelper::render_options($decimals, $question->get_answer_decimals(), 3);
?>
                </select>
              </td>
            </tr>
            <tr>
              <th class="spaced-top"><img src="../../artwork/information_icon.gif" width="16" height="16" alt="Information" title="<?php echo $string['percenttolerance'] ?>" class="tiptop" /> <?php echo $string['tolerance'] ?></th>
              <td class="spaced-top"><label for="tolerance_full" class="spaced-right"><strong><?php echo $string['tolerance_full'] ?></strong></label><input type="text" id="tolerance_full" name="tolerance_full" value="<?php echo $question->get_tolerance_full() ?>" /></td>
              <td class="spaced-top"><span class="marks-partial<?php echo $show_partial ?>"><label for="tolerance_partial" class="spaced-right"><strong><?php echo $string['tolerance_partial'] ?></strong></label><input type="text" id="tolerance_partial" name="tolerance_partial" value="<?php echo $question->get_tolerance_partial() ?>" /></span></td>
            </tr>
          </tbody>
        </table>

