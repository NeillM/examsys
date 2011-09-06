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
?>
        <table id="q-details" class="form" summary="Edit question details">
          <tbody>
            <tr>
              <th>Marking</th>
              <td>
                <label for="score_method" class="heavy">Method</label>
                <select id="score_method" name="score_method" class="spaced-right-large">
<?php
echo ViewHelper::render_options($question->get_score_methods(), $question->get_score_method(), 3, true);
?>
                </select>
                <label for="option_marks_correct" class="heavy">Marks if Correct</label>
                <select id="option_marks_correct" name="option_marks_correct" class="spaced-right-large">
<?php
echo ViewHelper::render_options($marks_positive, $mark_correct, 3);
?>
                </select>
                <label for="option_marks_incorrect" class="heavy">Marks if Incorrect</label>
                <select id="option_marks_incorrect" name="option_marks_incorrect" class="spaced-right-large">
<?php
echo ViewHelper::render_options($marks_negative, $mark_incorrect, 3);
?>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
