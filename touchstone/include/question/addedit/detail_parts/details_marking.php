<?php
$marks_positive = range(0, 20);
$marks_negative = array(0, -0.5, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10);
if (count($question->options) > 0) {
  $option = reset($question->options);
  $mark_correct = $option->get_marks_correct();
  $mark_incorrect = $option->get_marks_incorrect();
  $mark_partial = $option->get_marks_partial();
} else {
  $mark_correct = 1;
  $mark_incorrect = 0;
  $mark_partial = 0;
}
$allow_neg = $question->allow_negative_marks();
?>
        <table id="q-details" class="form" summary="Edit question details">
          <tbody>
            <tr>
<?php
if ($question->allow_change_marking_method()):
?>
              <th><label for="score_method" class="heavy"><?php echo $string['markingmethod'] ?></label></th>
              <td>
                
                <select id="score_method" name="score_method" class="spaced-right-large">
<?php
echo ViewHelper::render_options($question->get_score_methods(), $question->get_score_method('int'), 3, true);
?>
                </select>
                <label for="option_marks_correct" class="heavy"><?php echo $string['markscorrect']?></label>
<?php
else:
?>
              <th><label for="option_marks_correct" class="heavy"><?php echo $string['markscorrect']?></label></th>
              <td>

<?php
endif;
?>
                <select id="option_marks_correct" name="option_marks_correct" class="spaced-right-large">
<?php
echo ViewHelper::render_options($marks_positive, $mark_correct, 3);
?>
                </select>
<?php
if ($question->allow_negative_marks()):
?>
                <label for="option_marks_incorrect" class="heavy"><?php echo $string['marksincorrect']?></label>
                <select id="option_marks_incorrect" name="option_marks_incorrect" class="spaced-right-large">
<?php
echo ViewHelper::render_options($marks_negative, $mark_incorrect, 3);
?>
                </select>
<?php
endif;
if ($question->allow_partial_marks()):
  $show_partial = ($question->get_score_method() == 'Allow partial Marks') ? '' : ' class="hide"';
?>
                <span id="marks-partial"<?php echo $show_partial ?>>
                  <label for="option_marks_partial" class="heavy"><?php echo $string['markspartial']?></label>
                  <select id="option_marks_partial" name="option_marks_partial">
<?php
echo ViewHelper::render_options($marks_positive, $mark_partial, 3);
?>
                  </select>
                </span>
<?php
endif;
?>
              </td>
            </tr>
          </tbody>
        </table>
