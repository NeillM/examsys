<?php

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
 * The caculation question
 *
 * @author Anthony Brown 
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class Caculation extends Question implements questionInterface  {
  
  
  public function __construct() {
    
  }
  
  /*
   * Mark the users awnser 
   * 
   *  This Must handle exclusions
   */
  public function caculateUserMark($userawnser) {
    
  }
  
  /*
   * caulate how many marks is this question worth form its options 
   *    
   *   This Must handle exclusions
   */
  public function caculateQuestionMark() {
    
  }
  
  /*
   * caculate the Random Mark for this question 
   *  This Must handle exclusions
   */
  public function caculateRandomMark() {
    
  }
  
  /*
   * Display the question
   *
   * The Paper handles question numbering this function renders the inner part of the question 
   * we need at least 2 renders one for the exam script (start.php) one for formative feedback on (finish.php)
   */
  public function render() {
    
  }
  
}

?>
