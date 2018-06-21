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

namespace import;

/**
 * Import modules from csv format to rogo db format
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */
class import_modules extends importer {

  /**
   * The list of modules where school was not found.
   * @var array
   */
  private $moduleexists;

  /**
   * The list of modules added.
   * @var array
   */
  private $moduleadded;

  /**
   * The list of modules that failed to add.
   * @var array
   */
  private $modulefailed;

  /**
   * Do the module import described in the csv file.
   */
  public function execute() {
    // Set the required header.
    $this->data->required_header(
      array(
        'moduleid',
        'fullname',
        'school',
        'smsapi',
        'objectiveapi',
        'peerreview',
        'externalexaminers',
        'stdset',
        'mapping',
        'active',
        'selfenrol',
        'negmarking',
        'timedexams',
        'questionbasedfb',
        'addteammember',
        'yearstart',
        'externalid',
      )
    );
    $default_academic_year_start = $this->config->get_setting('core', 'system_academic_year_start');
    try {
      // Get a list of schools held by Rogo.
      $school_list = \schoolutils::get_schools();
      while ($line = $this->data->get_line()) {
        $line['moduleid'] = trim($line['moduleid']);
        $line['fullname'] = trim($line['fullname']);
        if (isset($school_list[trim($line['school'])])) {
          $line['school'] = $school_list[trim($line['school'])];
        } else {
          // School not found.
          $this->modulefailed[] = $line['moduleid'];
          continue;
        }
        $line['smsapi'] = trim($line['smsapi'] );
        $line['objectiveapi'] = trim($line['objectiveapi']);
        $line['peerreview'] = returnTrueFalse($line['peerreview']);
        $line['externalexaminers'] = returnTrueFalse($line['externalexaminers']);
        $line['stdset'] = returnTrueFalse($line['stdset']);
        $line['mapping'] = returnTrueFalse($line['mapping']);
        $line['active'] = returnTrueFalse($line['active']);
        $line['selfenrol'] = returnTrueFalse($line['selfenrol']);
        $line['negmarking'] = returnTrueFalse($line['negmarking']);

        if (isset($line['timedexams'])) {
          $line['timedexams'] = returnTrueFalse($line['timedexams']);
        } else {
          $line['timedexams'] = 0;
        }

        if (isset($line['questionbasedfb'])) {
          $line['questionbasedfb'] = returnTrueFalse($line['questionbasedfb']);
        } else {
          $line['questionbasedfb'] = 0;
        }

        if (isset($line['addteammember'])) {
          $line['addteammember'] = returnTrueFalse($line['addteammember']);
        } else {
          $line['addteammember'] = 0;
        }


        if (isset($line['yearstart']) and preg_match ('([0-1][0-9]/[0-3][0-9])', $fields[15]) ) {
          $line['yearstart']= trim($line['yearstart']);
        } else {
          $line['yearstart']= $default_academic_year_start;
        }

        if (isset($line['externalid'])) {
          $line['externalid'] = trim($line['externalid']);
        } else {
          $line['externalid'] = null;
        }

        if (\module_utils::module_exists($line['moduleid'], $this->config->db)) {
          $updateData = array();

          $checklist = '';
          if ($line['peerreview'] == true) $checklist .= ',peer';
          if ($line['externalexaminers'] == true) $checklist .= ',external';
          if ($line['stdset'] == true) $checklist .= ',stdset';
          if ($line['mapping'] == true) $checklist .= ',mapping';
          $updateData['checklist'] = substr($checklist, 1);
          $updateData['fullname'] = $line['fullname'];
          $updateData['vle_api'] = $line['objectiveapi'];
          $updateData['sms'] = $line['smsapi'];
          $updateData['schoolid'] = $line['school'];
          $updateData['active'] = $line['active'];
          $updateData['selfenroll'] = $line['selfenrol'];
          $updateData['neg_marking'] = $line['negmarking'];
          $updateData['timed_exams'] = $line['timedexams'];
          $updateData['exam_q_feedback'] = $line['questionbasedfb'];
          $updateData['add_team_members'] = $line['addteammember'];
          $updateData['academic_year_start'] = $line['yearstart'];
          $updateData['externalid'] = $line['externalid'];

          \module_utils::update_module_by_code($line['moduleid'], $updateData, $this->config->db);
          $this->moduleexists[] = $line['moduleid'];
        } else {
          $success = \module_utils::add_modules(
            $line['moduleid'],
            $line['fullname'],
            $line['active'],
            $line['school'],
            $line['objectiveapi'],
            $line['smsapi'],
            $line['selfenrol'],
            $line['peerreview'],
            $line['externalexaminers'],
            $line['stdset'],
            $line['mapping'],
            $line['negmarking'],
            '',
            $this->config->db,
            0,
            $line['timedexams'],
            $line['questionbasedfb'],
            $line['addteammember'],
            1,
            $line['yearstart'],
            $line['externalid']
          );
          if ($success) {
            $this->moduleadded[] = $line['moduleid'];
          } else {
            $this->modulefailed[] = $line['moduleid'];
          }
        }
      }
    } catch (csv_load_exception $e) {
      // The csv file is invalid.
      $this->modulefailed[] = 'Boom!';
    }
  }

  /**
   * Get failed modules
   * @return array
   */
  public function get_failed() {
    return $this->modulefailed;
  }

  /**
   * Get added modules
   * @return array
   */
  public function get_added() {
    return $this->moduleadded;
  }

  /**
   * Get modules that already exist
   * @return array
   */
  public function get_exists() {
    return $this->moduleexists;
  }
}