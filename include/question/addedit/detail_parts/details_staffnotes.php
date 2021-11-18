            <tr>
                <th>
                    <label for="staffnotes"><?php echo $string['staffnotes'] ?></label><br />
                    <span class="note"><?php echo $string['staffnotesmsg'] ?></span>
                </th>
                <td>
                    <textarea id="staffnotes" name="staffnotes" cols="100" rows="2" class="form-large"><?php echo $question->get_staffnotes() ?></textarea>
                </td>
            </tr>
