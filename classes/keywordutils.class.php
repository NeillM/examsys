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
* Utility class for keyword related functionality
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/


Class keyword_utils {
 
  /**
   * See if a reference material ID actually exists.
   * @return true or false.
   */
  static function refmaterials_exist($idMod, $db) {
    $row_no = 0;
  
    $result = $db->prepare("SELECT id FROM reference_material WHERE id = ?");
    $result->bind_param('i', $refID);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    $result->fetch();
    $row_no = $result->num_rows;
    $result->close();
    
    return $row_no > 0;
  }
  
  static function name_from_ID($keywordID, $db) {
    $result = $db->prepare("SELECT keyword FROM keywords_user WHERE id = ?");
    $result->bind_param('i', $keywordID);
    $result->execute();
    $result->store_result();
    $result->bind_result($keyword);
    if ($result->num_rows == 0) {
      $keyword = false;
    } else {
      $result->fetch();
    }
    $result->close();
    
    return $keyword;
  }
}