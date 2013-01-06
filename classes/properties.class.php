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
* Repository class for the properties table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class Properties {


  /*
   * @var mysqli $db
   */

  private $db;

  /*
   * @var SplObjectStorage $data_collection
  */

  private $data_collection;

  /*
   * @param mysqli $db
   */
  public function __construct( mysqli $db ) {
    $this->db              = $db;
    $this->data_collection = new SplObjectStorage();
  }

  /*
   * @param  LabObject $lab_object
   * @return SplObjectStorage
   */
  public function get_invigilator_properties( LabObject $lab_object ) {

    $sql = 'SELECT
                properties.property_id
              , paper_title
              , start_date
              , end_date
              , exam_duration
              , calendar_year
              , password
            FROM
                properties
            WHERE
                paper_type = "2"
            AND
                labs
            LIKE
                ?
            AND
                start_date < DATE_ADD( NOW(), interval 30 minute )
            AND
                end_date > NOW()
            AND
                deleted IS NULL';

    $paper_results = $this->db->prepare( $sql );

    $paper_results->bind_param('s', $lab_object->get_id() );
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

    while ( $paper_results->fetch() ) {

      $property_object = new PropertyObject();

      $property_object->set_property_id( $property_id );
      $property_object->set_paper_title($paper_title);
      $property_object->set_start_date( $start_date );
      $property_object->set_end_date( $end_date );
      $property_object->set_exam_duration($exam_duration);
      $property_object->set_calendar_year($calendar_year);
      $property_object->set_calendar_year($calendar_year);

      $this->data_collection->attach( $property_object );
    }

    $paper_results->close();

    return $this->data_collection;

  }

}