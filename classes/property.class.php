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
* Repository class for a property record in the properties table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class Property {

  /*
   * @var PropertyObject $property_object
  */

  private $property_object;

  /*
   * @var mysqli $db
   */

  private $db;

  public function __construct( PropertyObject $property_object
                             , mysqli         $db ) {
    $this->property_object = $property_object;
    $this->db              = $db;
  }

  public function get_property() {

    $sql = 'SELECT
                property_id
              , paper_title
              , start_date
              , end_date
              , exam_duration
              , calendar_year
              , password
            FROM
                properties
            WHERE
                property_id = ?';

    $paper_results = $this->db->prepare( $sql );

    $property_id = $this->get_property_id();

    $paper_results->bind_param('s', $property_id );
    $paper_results->execute();
    $paper_results->store_result();
    $paper_results->bind_result( $property_id
                               , $paper_title
                               , $start_date
                               , $end_date
                               , $exam_duration
                               , $calendar_year
                               , $password );

    if ( $paper_results->num_rows < 0 ) {
      $paper_results->close();
      return false;
    }

    $paper_results->fetch();

    $this->property_object->set_property_id( $property_id );
    $this->property_object->set_start_date( $start_date );
    $this->property_object->set_end_date( $end_date );
    $this->property_object->set_exam_duration( $exam_duration );
    $this->property_object->set_calendar_year( $calendar_year );
    $this->property_object->set_calendar_year( $calendar_year );

    $paper_results->close();

    return $this->property_object;

  }

  private function get_property_id(){
    return $this->property_object->get_property_id();
  }

}