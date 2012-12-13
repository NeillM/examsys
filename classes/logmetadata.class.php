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
* Repository class for the log_meta_data table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

class LogMetadata {

  private $paper_id;

  /*
   * @var mysqli $db
   */

  private $db;

  /*
   * @var userObject $userObject
  */

  private $userObject;

  public function __construct( userObject $userObject, $paper_id, mysqli $db ) {
    $this->userObject = $userObject;
    $this->paper_id   = $paper_id;
    $this->db         = $db;
  }

  public function set_completed_to_now() {

    $query = 'UPDATE
                 log_metadata
               SET
                 completed = NOW()
               WHERE
                 userID = ?
               AND
                paperID = ?';

    $result     = $this->db->prepare( $query );

    $result->bind_param('ii', $this->userObject->get_user_ID(), $this->paper_id );
    $result->execute();
    $result->close();

  }

  public function set_completed_to_null() {

    $query =  'UPDATE
                 log_metadata
               SET
                 completed = NULL
               WHERE
                 userID = ?
               AND
                paperID = ?';

    $result     = $this->db->prepare( $query );

    $result->bind_param('ii', $this->userObject->get_user_ID(), $this->paper_id );
    $result->execute();
    $result->close();

  }

  public function  is_users_paper_completed(){

    $query = 'SELECT
               id
             FROM
               log_metadata
             WHERE
               completed IS NOT NULL
             AND
               userID = ?
             AND
               paperID = ?';

    $result = $this->db->prepare( $query );

    $result->bind_param( 'ii', $this->userObject->get_user_ID(), $this->paper_id );
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if( $num_rows < 1 ){
      return false;
    }

    return true;

  }

}

?>