<?php 
$show_notes = (isset($show_notes)) ? $show_notes : true;
?>
            <tr>
              <th><label for="theme"><?php echo $string['theme'] ?></label></th>
              <td>
                <input type="text" id="theme" name="theme" cols="100" rows="5" class="form-large" value="<?php echo $question->get_theme() ?>" />
              </td>
            </tr>
<?php 
if ($show_notes):
?>
            <tr>
              <th><label for="notes"><?php echo $string['notes'] ?></label><br /><span class="note"><?php echo $string['notesmsg'] ?></span></th>
              <td>
                <textarea id="notes" name="notes" cols="100" rows="2" class="form-large"><?php echo $question->get_notes() ?></textarea>
              </td>
            </tr>
<?php 
endif;
?>