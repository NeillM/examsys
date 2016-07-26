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
 * An option for a question
 *
 * @author brzab3
 */
class Option {

  public $id = -1;
  protected $question_id = null;
  protected $text = '';
  protected $media = '';
  protected $media_width = '';
  protected $media_height = '';
  protected $correct_fback = '';
  protected $incorrect_fback = '';
  protected $correct = '';
  protected $marks_correct = 1;
  protected $marks_incorrect = 0;
  protected $marks_partial = 0;

  /**
   * Function to get the o_id based on the id_num
   * @param integer $id_num option id number
   * @param mysqli $db db connection
   * @return integer|bool o_id or false is non found
   */
  public static function get_oid_from_idnum($id_num, $db) {
    $sql = $db->prepare("SELECT o_id FROM options WHERE id_num = ?");
    $sql->bind_param('i', $id_num);
    $sql->execute();
    $sql->store_result();
    $sql->bind_result($o_id);
    if ($sql->num_rows == 0) {
      $o_id = false;
    } else {
      $sql->fetch();
    }
    $sql->close();
    return $o_id;
  }
  
}

?>
