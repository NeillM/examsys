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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

$correct_t = ($option->get_correct() == 't') ? ' checked="checked"' : '';
$correct_f = ($option->get_correct() == 'f') ? ' checked="checked"' : '';
?>
          <tbody class="option">
            <tr>
              <th><?php echo $string['value'];?></th>
              <td>
                <input type="radio" id="option_correct1_t" name="option_correct1" value="t"<?php echo $correct_t ?> /> <label for="option_correct1_t" class="heavy dichotomous-true spaced-right"><?php echo $string['true'] ?></label>
                <input type="radio" id="option_correct1_f" name="option_correct1" value="f"<?php echo $correct_f ?> /> <label for="option_correct1_f" class="heavy dichotomous-false"><?php echo $string['false'] ?></label>
                <input name="optionid1" value="<?php echo $opt_id ?>" type="hidden" />
              </td>
            </tr>
            <tr>
              <th><label for="option_correct_fback1"><?php echo $string['fbcorrect'];?>:</label><br /><span class="note warning-severe"><?php echo $string['fbcorrectmsg'];?></span></th>
              <td>
                <textarea cols="85" rows="2" id="option_correct_fback1" name="option_correct_fback1" class="form-med-large"><?php echo $option->get_correct_fback() ?></textarea>
              </td>
            </tr>
            <tr>
              <th><label for="option_incorrect_fback1"><?php echo $string['fbincorrect'];?>:</label><br /><span class="note"><?php echo $string['fbincorrectmsg'];?></span></th>
              <td>
                <textarea cols="85" rows="2" id="option_incorrect_fback1" name="option_incorrect_fback1" class="form-med-large"><?php echo $option->get_incorrect_fback() ?></textarea>
              </td>
            </tr>
          </tbody>
