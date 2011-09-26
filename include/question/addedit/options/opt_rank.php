<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$hidden = (($num_options == 0 and $index > 6) or ($num_options > 0 and $index > $num_options)) ? ' hide' : '';
//$correct = ($option->get_correct() == $index) ? ' checked="checked"' : '';
$correct_vals = array('' => '', '0' => 'N/A');
for ($i = 1; $i <= 20; $i++) {
  $postfix = 'th';
  if ($i == 1) $postfix = 'st';
  if ($i == 2) $postfix = 'nd';
  if ($i == 3) $postfix = 'rd';
  $correct_vals[$i] = $i . $postfix;
}
if ($index %2 == 0) {
  $alt_c = ' class="alt"';
} else {
  $alt_c = '';
}
$spaced = ($index > 1) ? ' spaced-top' : '';
?>
          <tbody class="option<?php echo $hidden ?>">
            <tr<?php echo $alt_c ?>>
              <th class="spaced-bottom<?php echo $spaced ?>"><label for="option_text<?php echo $index ?>"><?php printf($string['optiontext'], $index) ?></label></th>
              <td class="spaced-bottom<?php echo $spaced ?>">
                <input type="text" name="option_text<?php echo $index ?>" id="option_text<?php echo $index ?>" class="form-med-large" value="<?php echo $option->get_text() ?>" />
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
              </td>
              <td class="small align-centre spaced-bottom<?php echo $spaced ?>">
                <select id="option_correct<?php echo $index ?>" name="option_correct<?php echo $index ?>">
<?php 
echo ViewHelper::render_options($correct_vals, $option->get_correct(), 3);
?>
                </select>
              </td>
            </tr>
          </tbody>
