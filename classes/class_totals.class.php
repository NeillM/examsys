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
* Class total reports, included functions used in:
*
*    class_totals.php
*    class_totals_csv.php
*    class_totals_xml.php
*    assessment_marks.php
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/calculate_marks.inc';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/exclusion.class.php';
require_once '../classes/mathsutils.class.php';
require_once '../classes/results_cache.class.php';

class ClassTotals {

  private $db;
  private $demo;
  private $user_results;
  private $paper_buffer;
  private $stats;
  private $cohort_size;
  private $absent;
  private $studentsonly;
  private $paperID;
  private $paper_type;
  private $calendar_year;
  private $ss_pass;
  private $ss_hon;
  private $student_cohort;
  private $startdate;
  private $enddate;
  private $exclusions;
  private $moduleID_in;
  private $user_modules;
  private $metadata_array;
  private $late_cohort;
  private $marking;
  private $distinction_score;
  private $pass_mark;
  private $distinction_mark;
  private $question_no;
  private $log_late;
  private $repcourse;
  private $repmodule;
  private $total_marks;
  private $orig_total_marks;
  private $total_random_mark;
  private $display_excluded;
  private $display_experimental;
  private $q_medians;
  private $log_late_missing_users;
  private $random_q_ids;
  private $config;
  private $propertyObj;
  private $user_no;

  public function __construct($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $db) {
    $userObject = UserObject::get_instance();

  	$this->db = $db;
    $this->demo                   = is_demo($userObject);
    $this->paperID                = $propertyObj->get_property_id();
    $this->paper_type             = $propertyObj->get_paper_type();
    $this->calendar_year          = $propertyObj->get_calendar_year();
    $this->startdate              = $startdate;
    $this->enddate                = $enddate;
    $this->absent                 = $absent;
    $this->studentsonly           = $studentsonly;
    $this->marking                = $propertyObj->get_marking();
    $this->percent                = $percent;
    $this->ordering               = $ordering;
    $this->sortby                 = $sortby;
    $this->repcourse              = $repcourse;
    $this->repmodule              = $repmodule;
    $this->pass_mark              = $propertyObj->get_pass_mark();
    $this->distinction_mark       = $propertyObj->get_distinction_mark();
    $this->log_late               = array();
    $this->q_medians              = array();
    $this->log_late_missing_users = array();
    $this->random_q_ids           = array();
    $this->config                 = Config::get_instance();
    $this->propertyObj            = $propertyObj;
    $this->exclusions             = new Exclusion($this->paperID, $this->db);
    $this->display_excluded       = '';
    $this->user_no                = 0;
  }

  public function get_user_results() {
    return $this->user_results;
  }

  public function set_user_results($user_results) {
    $this->user_results = $user_results;
  }

  public function get_paper_buffer() {
    return $this->paper_buffer;
  }

  public function get_stats() {
    return $this->stats;
  }

  public function get_cohort_size() {
    return $this->cohort_size;
  }

  public function get_ss_pass() {
    return $this->ss_pass;
  }

  public function get_ss_hon() {
    return $this->ss_hon;
  }

  public function get_student_cohort() {
    return $this->student_cohort;
  }

  public function get_question_no() {
    return $this->question_no;
  }

  public function get_log_late() {
    return $this->log_late;
  }

  public function get_total_marks() {
    return $this->total_marks;
  }

  public function get_orig_total_marks() {
    return $this->orig_total_marks;
  }

  public function get_total_random_mark() {
    return $this->total_random_mark;
  }

  public function get_display_excluded() {
    return $this->display_excluded;
  }

  public function get_display_experimental() {
    return $this->display_experimental;
  }

  public function get_exclusions() {
    return $this->exclusions;
  }

  public function get_user_no() {
    return $this->user_no;
  }

  public function compile_report($recache) {

    $moduleID = Paper_utils::get_modules($this->paperID, $this->db);
    $this->moduleID_in = implode(',', array_keys($moduleID));

    $this->exclusions->load();                                                                                // Get any questions to exclude.

    $this->load_answers();

    $this->set_log_late();

    $this->load_absent();

    $this->find_users();                                                                                      // Get all the users on the module(s) the paper is on.

    $this->load_metadata();                                                                                   // Query for metadata

    $this->load_results();                                                                                    // Load the student data

    $this->load_late_cohort();                                                                                // Get any late users

    $this->adjust_marks();                                                                                    // Scale marks (random marks or standards setting)

    $this->add_rank();                                                                                        // Add in rank data.

    $this->convert_moduleIDs();                                                                               // Convert Module IDs into codes

    $this->flag_subpart();                                                                                    // Used to flag subsets of the cohort (i.e. top 33%)

    $this->add_late_students();                                                                               // Add in late users into main dataset

    $this->add_absent_students();                                                                             // Add any absent students into main dataset

    $this->generate_stats();                                                                                  // Generate the main statistics

    $this->add_deciles();                                                                                     // Add in deciles per student

    $this->sort_results();                                                                                    // Sort the whole array by the right column

    $results_cache = new ResultsCache($this->db);
    if ($recache or $results_cache->should_cache($this->propertyObj, $this->percent, $this->absent)) {
      $results_cache->save_paper_cache($this->propertyObj, $this->percent, $this->absent, $this->stats);                  // Cache general paper stats

      $results_cache->save_student_mark_cache($this->propertyObj, $this->percent, $this->absent, $this->user_results);    // Cache student/paper marks

      $results_cache->save_median_question_marks($this->propertyObj, $this->percent, $this->absent, $this->q_medians);    // Cache the question/paper medians
    }
  }

  public function nicedate($original) {
    return substr($original, 6, 2) . '/' . substr($original, 4, 2) . '/' . substr($original, 0, 4) . ' ' . substr($original, 8, 2) . ':' . substr($original, 10, 2);
  }

  private function adjust_marks() {
    $user_no = count($this->user_results);

    if ($this->marking == '1') {                              // Monkey mark
      for ($i=0; $i<$user_no; $i++) {
        $this->user_results[$i]['percent'] = (($this->user_results[$i]['mark'] - $this->total_random_mark) / ($this->total_marks - $this->total_random_mark)) * 100;
      }
    } elseif ($this->marking{0} == '2') {                     // Standards Setting
      $this->set_ss_pass();

      for ($i=0; $i<$user_no; $i++) {
        $this->user_results[$i]['percent'] = $this->crankMark($this->user_results[$i]['percent']);
      }
    }
  }

  private function getRandomDetails($questionID) {
    $this->random_q_ids[] = $questionID;

    $this->question_no  = 0;
    $random_questions   = array();
    $old_q_id           = '';
    $old_score_method   = '';
    $old_q_media_width  = '';
    $old_q_media_height = '';
    $old_correct        = array();
    $old_option_text    = array();
    $old_scenario       = '';
    $old_leadin         = '';
    $old_media          = '';
    $old_type           = '';
    $old_score_method   = '';

    $result = $this->db->prepare("SELECT options1.option_text, leadin, scenario, q_media_width, q_media_height, options2.correct, options2.marks_correct, options2.marks_incorrect, options2.option_text, q_type, display_method, score_method, status FROM options AS options1, questions, options AS options2 WHERE options1.option_text=questions.q_id AND questions.q_id=options2.o_id AND options1.o_id=?");
    $result->bind_param('i', $questionID);
    $result->execute();
    $result->store_result();
    if ($result->num_rows > 0) {
      $result->bind_result($q_id, $leadin, $scenario, $q_media_width, $q_media_height, $correct, $marks_correct, $marks_incorrect, $option_text, $q_type, $display_method, $score_method, $status);
      while ($result->fetch()) {
        if ($old_q_id != $q_id and $old_q_id != '') {
          $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
          $random_questions[$old_q_id]['q_id']                = $old_q_id;
          $random_questions[$old_q_id]['q_type']              = $old_q_type;
          $random_questions[$old_q_id]['leadin']              = $old_leadin;
          $random_questions[$old_q_id]['scenario']            = $old_scenario;
          $random_questions[$old_q_id]['correct']             = $old_correct;
          $random_questions[$old_q_id]['display_method']      = $old_display_method;
          $random_questions[$old_q_id]['score_method']        = $old_score_method;
          $random_questions[$old_q_id]['status']              = $old_status;
          $random_questions[$old_q_id]['marks_correct']       = $old_marks_correct;
          $random_questions[$old_q_id]['marks_incorrect']     = $old_marks_incorrect;
          $random_questions[$old_q_id]['random_mark']         = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
          $random_questions[$old_q_id]['option_text']         = $old_option_text;
          $old_correct     = array();
          $old_option_text = array();
          $this->question_no++;
        }
        $old_q_id             = $q_id;
        $old_q_type           = $q_type;
        $old_leadin           = $leadin;
        $old_scenario         = $scenario;
        $old_status           = $status;
        $old_marks_correct    = $marks_correct;
        $old_marks_incorrect  = $marks_incorrect;
        $old_correct[]        = $correct;
        $old_option_text[]    = $option_text;
        $old_display_method   = $display_method;
        $old_score_method     = $score_method;
        $old_q_media_width    = $q_media_width;
        $old_q_media_height   = $q_media_height;
      }

      // Write out the last question.
      $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
      $random_questions[$old_q_id]['q_id']                = $old_q_id;
      $random_questions[$old_q_id]['q_type']              = $old_q_type;
      $random_questions[$old_q_id]['leadin']              = $old_leadin;
      $random_questions[$old_q_id]['scenario']            = $old_scenario;
      $random_questions[$old_q_id]['correct']             = $old_correct;
      $random_questions[$old_q_id]['display_method']      = $old_display_method;
      $random_questions[$old_q_id]['score_method']        = $old_score_method;
      $random_questions[$old_q_id]['status']              = $old_status;
      $random_questions[$old_q_id]['marks_correct']       = $old_marks_correct;
      $random_questions[$old_q_id]['marks_incorrect']     = $old_marks_incorrect;
      $random_questions[$old_q_id]['random_mark']         = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      $random_questions[$old_q_id]['option_text']         = $old_option_text;
    }
    $result->close();

    $this->paper_buffer[$questionID]['random_questions'] = $random_questions;

    return $old_q_id;
  }

  private function random_qMarks($random_questions) {
    $min = 999;
    $max = 0;

    foreach ($random_questions as $individual_question) {
      if ($individual_question['marks'] > $max) $max = $individual_question['marks'];
      if ($individual_question['marks'] < $min) $min = $individual_question['marks'];
    }

    if ($min == $max) {
      return $min;
    } else {
      return 'ERR';
    }
  }

  private function checkDisplayExcluded($exclude, $q_no) {
    $subpart = '';

    if (strlen($exclude) > 1) {
      for ($i=0; $i<strlen($exclude); $i++) {
        if ($exclude{$i} == '1') {
          if ($subpart == '') {
            $subpart = chr($i+97);
          } else {
            $subpart .= ', Q' . $q_no . chr($i+97);
          }
        }
      }
    }
    if (strpos($exclude, '1') !== false) {
      if ($this->display_excluded == '') {
        $this->display_excluded = 'Q' . $q_no . $subpart;
      } else {
        $this->display_excluded .= ', Q' . $q_no . $subpart;
      }
    }
  }

  private function displayExperimental($q_no) {
    if ($this->display_experimental == '') {
      $this->display_experimental = 'Q' . $q_no;
    } else {
      $this->display_experimental .= ', Q' . $q_no;
    }
  }

  private function set_ss_pass() {
    $mark_parts = explode(',', $this->marking);

    $ebel_marks = array();
    $ebel_percents = array();

    $std_types = array('EE', 'EI', 'EN', 'ME', 'MI', 'MN', 'HE', 'HI', 'HN', 'EE2', 'EI2', 'EN2', 'ME2', 'MI2', 'MN2', 'HE2', 'HI2', 'HN2');
    foreach ($std_types as $std_type) {
      $ebel_marks[$std_type] = 0;
      $ebel_percents[$std_type] = 0;
    }

    $std_total = 0;
    $question_no = 0;

    // Get question ratings.
    $result = $this->db->prepare("SELECT questionID, rating, method, status, q_type, score_method FROM standards_setting, questions WHERE standards_setting.questionID = questions.q_id AND paperID = ? AND setterID = ? AND std_set = ?");
    $result->bind_param('iis', $this->paperID, $mark_parts[1], $mark_parts[2]);
    $result->execute();
    $result->bind_result($questionID, $rating, $method, $status, $q_type, $score_method);
    while ($result->fetch()) {
      if ($status != 'Experimental') {
        $individual_ratings = explode(',', $rating);
        $count_individual_ratings = count($individual_ratings);
        for ($i=0; $i<$count_individual_ratings; $i++) {
          $excluded = $this->exclusions->get_exclusions_by_qid($questionID);

          $bad_bonus_mark = ($q_type == 'rank' and $score_method == 'Bonus Mark' and strpos($excluded, '1') !== false);
          if (($excluded{$i} == 0) and !$bad_bonus_mark) {
            if (in_array($individual_ratings[$i], $std_types)) $ebel_marks[$individual_ratings[$i]]++;
          }
          $std_total += $individual_ratings[$i];
          $question_no++;
        }
      }
    }
    $result->close();

    // Get the Ebel grid.
    if ($method == 'Ebel') {
      $result = $this->db->prepare("SELECT category, percentage FROM ebel WHERE setterID = ? AND date_set = ?");
      $result->bind_param('is', $mark_parts[1], $mark_parts[2]);
      $result->execute();
      $result->bind_result($category, $percentage);
      while ($result->fetch()) {
        $ebel_percents[$category] = round($percentage, 2);
      }
      $result->close();

      $cut_marks = 0.0;
      $cut_marks += $ebel_marks['EE'] * $ebel_percents['EE'] * 100;
      $cut_marks += $ebel_marks['EI'] * $ebel_percents['EI'] * 100;
      $cut_marks += $ebel_marks['EN'] * $ebel_percents['EN'] * 100;
      $cut_marks += $ebel_marks['ME'] * $ebel_percents['ME'] * 100;
      $cut_marks += $ebel_marks['MI'] * $ebel_percents['MI'] * 100;
      $cut_marks += $ebel_marks['MN'] * $ebel_percents['MN'] * 100;
      $cut_marks += $ebel_marks['HE'] * $ebel_percents['HE'] * 100;
      $cut_marks += $ebel_marks['HI'] * $ebel_percents['HI'] * 100;
      $cut_marks += $ebel_marks['HN'] * $ebel_percents['HN'] * 100;
      $this->ss_pass = ($cut_marks / ($this->total_marks  * 100)) * 100;
      $this->ss_pass = round($this->ss_pass, 1);

      if ($ebel_percents['EE2'] === NULL) {
        $distinction_score = -1;
      } else {
        $cut_marks2 = 0.0;
        $cut_marks2 += $ebel_marks['EE'] * $ebel_percents['EE2'] * 100;
        $cut_marks2 += $ebel_marks['EI'] * $ebel_percents['EI2'] * 100;
        $cut_marks2 += $ebel_marks['EN'] * $ebel_percents['EN2'] * 100;
        $cut_marks2 += $ebel_marks['ME'] * $ebel_percents['ME2'] * 100;
        $cut_marks2 += $ebel_marks['MI'] * $ebel_percents['MI2'] * 100;
        $cut_marks2 += $ebel_marks['MN'] * $ebel_percents['MN2'] * 100;
        $cut_marks2 += $ebel_marks['HE'] * $ebel_percents['HE2'] * 100;
        $cut_marks2 += $ebel_marks['HI'] * $ebel_percents['HI2'] * 100;
        $cut_marks2 += $ebel_marks['HN'] * $ebel_percents['HN2'] * 100;
        $this->distinction_score = (($cut_marks2 / ($this->total_marks * 100)) * 100);
        $this->distinction_score = round($this->distinction_score, 1);
      }
    } else {
      // Modified Angoff method.
      if ($question_no > 0) {
        $this->ss_pass = round($std_total / $question_no, 1);
      } else {
        $this->ss_pass = 0;
      }
    }

    if ($this->distinction_score == 0) {
      $this->set_ss_hon();
      $this->distinction_score = $this->ss_hon;
    }
  }

  private function set_ss_hon() {
    $user_no = count($this->user_results);

    $marks_data = array();
    for ($i=0; $i<$user_no; $i++) {
      if (isset($this->user_results[$i]['percent'])) $marks_data[] = $this->user_results[$i]['percent'];
    }

    $this->ss_hon = MathsUtils::percentile($marks_data, 0.2);
  }

  private function crankMark($percent) {
    if ($this->ss_hon > 0 and $this->ss_hon < 100) {
      if ($percent < $this->ss_pass) {
        $cranked = $this->pass_mark  - ($this->pass_mark - 0) * (($this->ss_pass - $percent) / ($this->ss_pass - 0));
      } elseif ($percent >= $this->ss_pass and $percent < $this->ss_hon) {
        $cranked = $this->distinction_mark - ($this->distinction_mark - $this->pass_mark) * (($this->ss_hon - $percent) / ($this->ss_hon - $this->ss_pass));
      } else {
        $cranked = 100 - (100 - $this->distinction_mark) * ((100 - $percent) / (100 - $this->ss_hon));
      }
    } else {
      if ($percent < $this->ss_pass) {
        $cranked = $pass_mark  - ($this->pass_mark - 0) * (($this->ss_pass - $percent) / ($this->ss_pass - 0));
      } else {
        $cranked = 100 - (100 - $this->pass_mark) * ((100 - $percent) / (100 - $this->ss_pass));
      }
    }
    return $cranked;
  }

  private function writeUserResults($tmp, &$user_number, $tmp_user_mark, $tmp_user_mark_array, $tmp_user_duration, $marking_comp) {
    $this->user_results[$user_number] = $tmp;

    $this->user_results[$user_number]['mark'] = round($tmp_user_mark, 1);
    $this->user_results[$user_number]['mark_array'] = $tmp_user_mark_array;
    if ($this->total_marks == 0) {
      $this->user_results[$user_number]['percent'] = 0;
    } else {
      $this->user_results[$user_number]['percent'] = ($tmp_user_mark / $this->total_marks) * 100;
    }
    $this->user_results[$user_number]['duration']         = $tmp_user_duration;
    $this->user_results[$user_number]['marking_complete'] = $marking_comp;
    $this->user_results[$user_number]['visible']          = true;    // Default to visible unless switched off below.

    if ($this->demo) {
      $this->user_results[$user_number]['surname']     = demo_replace($tmp['surname'], $this->demo);
      $this->user_results[$user_number]['initials']    = demo_replace($tmp['initials'], $this->demo);
      $this->user_results[$user_number]['first_names'] = demo_replace($tmp['first_names'], $this->demo);
      $this->user_results[$user_number]['email']       = demo_replace($tmp['email'], $this->demo);
      $this->user_results[$user_number]['student_id']  = demo_replace_number($tmp['student_id'], $this->demo);
    }

    // Add metadata
    if (!empty($this->metadata_array['types'])) {
      foreach ($this->metadata_array['types'] as $type) {
        if (isset($this->metadata_array['students'][$tmp['userID']][$type]) ) {
          $this->user_results[$user_number]['meta_' . $type] = $this->metadata_array['students'][$tmp['userID']][$type];
        } else {
          $this->user_results[$user_number]['meta_' . $type] = '';
        }
      }
    }

    // Check metadata exclusions
    $i = 1;
    while (isset($_GET["meta$i"])) {
      $meta_parts = explode('=', $_GET["meta$i"]);
      if ($meta_parts[1] != '%') {
        if ($this->user_results[$user_number][$meta_parts[0]] != $meta_parts[1]) {
          $this->user_results[$user_number]['visible'] = false;
        }
      }
      $i++;
    }

    if (isset($this->student_cohort)) {
      $this->check_and_clear_cohort($tmp['username']);
    }

    $user_number++;
  }

  private function find_random_question($q_id, &$tmp_q) {
    $tmp_q_id = -1;

    $randomIDs = $this->random_q_ids;
    foreach ($randomIDs as $rnd_id) {
      if (isset($this->paper_buffer[$rnd_id]) and isset($this->paper_buffer[$rnd_id]['random_questions']) and count($this->paper_buffer[$rnd_id]['random_questions']) > 0) {
        if (in_array($q_id, array_keys($this->paper_buffer[$rnd_id]['random_questions']))) {
          $tmp_q_id = $q_id;
          $tmp_q = $this->paper_buffer[$rnd_id]['random_questions'][$q_id];
        }
      }
    }

    return $tmp_q_id;
  }

  private function extract_blank_correct($option_text, $display_method, $question_id) {
    $correct = array();

    $not_used = preg_match('/mark="([0-9]{1,3})"/', $option_text, $results);
    $blank_details = explode("[blank", $option_text);
    $no_answers = count($blank_details) - 1;
    for ($i=1; $i<=$no_answers; $i++) {
      $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
      $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);
      $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
      $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
      $answer_list = explode(',', $blank_details[$i]);
      $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
      if ($display_method == 'textboxes') {
        foreach ($answer_list as $individual_answer) {
          $correct[$i-1][] = html_entity_decode(trim($individual_answer));
        }
      } else {
        foreach ($answer_list as $tmp_answer) {
          $correct[$i-1][] = html_entity_decode(trim($tmp_answer));
        }
      }
    }

    return $correct;
  }

  private function getUserMark($q_id, $tmp_user_answer, $tmp_user_mark, $status, &$tmp_mark, &$tmp_user_mark_array, $userID, $tmp_array) {
    $tmp_exclude = $this->exclusions->get_exclusions_by_qid($q_id);

    $multi_part_qns = array('extmatch'=>1, 'matrix'=>1, 'blank'=>1, 'dichotomous'=>1, 'labelling'=>1, 'hotspot'=>1);

    if ($status == 'Experimental') $exclude[$q_id] = '1111111111111111111111111111111111111111';

    $skip_random = false;
    if (!isset($this->paper_buffer[$q_id])) {
      $tmp_q = array();
      $tmp_q_id = $this->find_random_question($q_id, $tmp_q);
      if ($tmp_q_id == -1) {
        $skip_random = true;
      } else {
        $question = $tmp_q;
        if ($question['q_type'] == 'blank') {
          $question['correct'] = $this->extract_blank_correct($question['option_text'][0], $question['display_method'], $tmp_q_id);
        }
      }
    } else {
      $question = $this->paper_buffer[$q_id];
    }

    if (!$skip_random and $question['score_method'] != 'Mark per Question' and isset($multi_part_qns[$question['q_type']])) {
      if ($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') {
        if (!isset($tmp_user_mark_array[$q_id])) $tmp_user_mark_array[$q_id] = array();
        $paper_answers = explode('|', $question['correct'][0]);
        $user_answers = explode('|', $tmp_user_answer);
        $section = 0;
        $count_paper_answers = count($paper_answers);
        for ($a=0; $a<$count_paper_answers; $a++) {
          if ($paper_answers[$a] != '') {
            $answers_correct = 0;
            $answers_incorrect = 0;
            if ($question['q_type'] == 'extmatch') {
              $sub_paper_answers = explode('$', $paper_answers[$a]);
              if (isset($user_answers[$a])) {
                $sub_user_answers = explode('$', $user_answers[$a]);
              } else {
                $sub_user_answers = array();
              }
              $exclude_on = true;
              $count_sub_paper_answers = count($sub_paper_answers);
              for ($c=0; $c<$count_sub_paper_answers; $c++) {
                if ($tmp_exclude{$section} == '0') {
                  if (!isset($sub_user_answers[$c]) or $sub_user_answers[$c] == '' or $sub_user_answers[$c] == 'u') {
                    // Do nothing
                  } elseif (in_array($sub_user_answers[$c], $sub_paper_answers)) {
                    $answers_correct++;
                  } else {
                    $answers_incorrect++;
                  }
                  $exclude_on = false;
                }
                $section++;
              }

              $section_mark = ($answers_correct * $question['marks_correct']) + ($answers_incorrect * $question['marks_incorrect']);
              $tmp_mark += $section_mark;
              // This is excluding whole question parts (not answers/marks).
              if ($exclude_on == false) $tmp_user_mark_array[$q_id][] = $section_mark;
            } else {   // Matrix
              if ($tmp_exclude{$a} == '0') {
                if ($paper_answers[$a] == $user_answers[$a]) {
                  $tmp_mark += $question['marks_correct'];
                  $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
                } elseif ($user_answers[$a] != 'u' and $user_answers[$a] != '') {
                  $tmp_mark += $question['marks_incorrect'];
                  $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
                } else {
                  $tmp_user_mark_array[$q_id][] = 0;
                }
              }
            }
          }
        }
      } elseif ($question['q_type'] == 'blank') {
        $user_answers = explode('|', $tmp_user_answer);
        $count_user_awnser = count($user_answers)-1;
        for ($a=0; $a<$count_user_awnser; $a++) {
          if ($tmp_exclude{$a} == '0') {
            if ($question['display_method'] == 'dropdown') {
              $student_answer = html_entity_decode(trim(str_replace('&nbsp;', ' ', $question['correct'][$a][0])));
              $correct_answer = html_entity_decode(trim(str_replace('&nbsp;', ' ', $user_answers[$a+1])));

              if ($student_answer == $correct_answer) {
                $tmp_mark += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
              } elseif ($user_answers[$a+1] != 'u' and $user_answers[$a+1] != '') {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              } else {
                $tmp_user_mark_array[$q_id][] = 0;
              }
            } else {
              $match = false;
              if (isset($question['correct'][$a])) {
                foreach ($question['correct'][$a] as $correct_alternative) {
                  if (strtolower(trim($user_answers[$a+1])) == strtolower(trim($correct_alternative))) {
                    $match = true;
                  }
                }
              }

              if ($match == true) {
                $tmp_mark += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
              } elseif ($user_answers[$a+1] != 'u' and $user_answers[$a+1] != '') {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              } else {
                $tmp_user_mark_array[$q_id][] = 0;
              }
            }
          }
        }
      } elseif ($question['q_type'] == 'dichotomous') {
        $count_question_correct = count($question['correct']);
        for ($a=0; $a<$count_question_correct; $a++) {
          if ($tmp_exclude{$a} == '0') {
            if ($question['correct'][$a] == $tmp_user_answer{$a}) {
              $tmp_mark += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
            } else {
              if ($tmp_user_answer{$a} == 'a' or $tmp_user_answer{$a} == 'u') {
                $tmp_user_mark_array[$q_id][] = 0;
              } else {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              }
            }
          }
        }
      } elseif ($question['q_type'] == 'hotspot') {
        $question_parts = explode('|', $question['correct'][0]);
        $user_answers = explode('|', $tmp_user_answer);
        $count_question_parts = count($question_parts);
        for ($i=0; $i<$count_question_parts; $i++) {
          if ($tmp_exclude{$i} == '0') {
            if (isset($user_answers[$i]) and substr($user_answers[$i],0,1) == '1') {
              $tmp_mark += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
            } elseif (isset($user_answers[$i]) and substr($user_answers[$i],0,1) == '0') {
              $tmp_mark += $question['marks_incorrect'];
              $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
            } else {
              $tmp_user_mark_array[$q_id][] = 0;
            }
          }
        }
      } elseif ($question['q_type'] == 'labelling') {
        $correct_labels = array();

        $tmp_first_split = explode(';', $question['correct'][0]);
        $tmp_second_split = explode('$', $tmp_first_split[11]);

        $label_count = 0;
        $placeholders = 0;
        $i = 0;
        $excluded_no = 0;
        $count_tmp_second_split = count($tmp_second_split);
        for ($label_no = 4; $label_no <= $count_tmp_second_split; $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
            $x = $tmp_second_split[$label_no-2];
            $y = $tmp_second_split[$label_no-1] - 25;
            $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
            if ($tmp_exclude{$i} == '0') {
              $placeholders++;
            } else {
              $excluded_no++;
            }
            $i++;
          }
        }

        // Strip out width and height for graphical labels.
        $i = 0;
        foreach ($correct_labels as $key=>$value) {
          $tmp_parts = explode('~', $value);
          $correct_labels[$key] = $tmp_parts[0];
          $correct_labels_pos[$key] = $i++;
        }

        if ($tmp_user_answer != '') {
          $user_split1 = explode(';', $tmp_user_answer);
          $user_split2 = explode('$', $user_split1[1]);

          $i = 0;
          $correct = 0;
          $count_user_split2 = count($user_split2)-3;
          for ($a=0; $a<$count_user_split2; $a+=4) {
            $x = $user_split2[$a];
            $y = $user_split2[$a+1];
            $index = 0;
            if (isset($correct_labels[$x . 'x' . $y])) $index = $correct_labels_pos[$x . 'x' . $y];
            if ($tmp_exclude{$index} == '0') {
              if (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] == $user_split2[$a+2]) {
                $tmp_mark += $question['marks_correct'];
                $correct += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][$index] = $question['marks_correct'];
              } elseif (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] != $user_split2[$a+2]) {
                $tmp_mark += $question['marks_incorrect'];
                $correct += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][$index] = $question['marks_incorrect'];
              } else {
                if (isset($index) && $index!='') $tmp_user_mark_array[$q_id][$index] = 0;
              }
              $i++;
            }
          }
        }

        if (isset($tmp_user_mark_array[$q_id])) ksort($tmp_user_mark_array[$q_id]);
        //create shortened array
        $i = 0;
        $correct_labels_exc = array();
        foreach ($correct_labels as $cli => $clv) {
          if ($tmp_exclude{$i++} == '0') $correct_labels_exc[$cli] = $clv;
        }
        // If the user has not answered the question pad out with zero marks.
        if (isset($tmp_user_mark_array[$q_id])) {
          $user_answer_no = count($tmp_user_mark_array[$q_id]);
        } else {
          $user_answer_no = 0;
        }
        if ($user_answer_no < count($correct_labels_exc)) {
          for ($i=$user_answer_no; $i<count($correct_labels_exc); $i++) {
            $tmp_user_mark_array[$q_id][] = 0;
          }
        }

        $question['correct_labels'] = $correct_labels_exc;
      }

      $this->q_medians[$q_id][] = $tmp_user_mark;
    } else {
      // Marking per Question, or all other question types, simply return the original mark.
      if ($tmp_exclude{0} == '0') {
        $round_tmp_user_mark = round($tmp_user_mark, 2);
        $tmp_mark += $round_tmp_user_mark;
        $tmp_user_mark_array[$q_id] = $round_tmp_user_mark;
      }

      $this->q_medians[$q_id][] = $tmp_user_mark;
    }
  }

  public function formatsec($seconds) {
    $diff_hour = ($seconds / 60) / 60;
    $tmp_position = strpos($diff_hour, '.');
    if ($tmp_position > 0) $diff_hour = substr($diff_hour, 0, $tmp_position);
    if ($diff_hour > 0) $seconds -= ($diff_hour * 60) * 60;
    $diff_min = $seconds / 60;
    $tmp_position = strpos($diff_min, '.');
    if ($tmp_position > 0) $diff_min = substr($diff_min, 0, $tmp_position);
    if ($diff_min > 0) $seconds -= $diff_min * 60;
    $diff_sec = $seconds;
    $timestring = '';
    if ($diff_hour < 10) $timestring = '0';
    $timestring .= "$diff_hour:";
    if ($diff_min < 10) $timestring .= '0';
    $timestring .= "$diff_min:";
    if ($diff_sec < 10) $timestring .= '0';
    $timestring .= $diff_sec;

    return $timestring;
  }

  /**
   * Check if a record exists in the student cohort array created when showing absent candidates. If found, remove the entry
   * @param  array  $student_cohort All (remaining) students in the cohort
   * @param  string $username       Username to look for
   * @return array                  Cohort array with current user removed if found
   */
  private function check_and_clear_cohort($username) {
    $tmp_cohort_size = count($this->student_cohort);
    for ($i=0; $i < $tmp_cohort_size; $i++) {
      if ($this->student_cohort[$i]['username'] == $username) {
        unset($this->student_cohort[$i]);
        break;
      }
    }
    $this->student_cohort = array_values($this->student_cohort);
  }

  public function load_answers() {
    $question_no      = 0;
    $old_q_id         = 0;
    $stems            = 0;
    $old_correct      = array();
    $old_option_text  = array();

    $this->total_marks = 0;
    $this->orig_total_marks = 0;
    $this->total_random_mark = 0;
    $this->display_excluded = '';
    $this->display_experimental = '';

    // Load the correct answers into 'paper_buffer' array.
    $result = $this->db->prepare("SELECT q_id, marks_correct, marks_incorrect, display_method, score_method, q_media_height, q_media_width, q_type, correct, score_method, option_text, status, display_pos FROM (papers, questions, options) WHERE papers.question = questions.q_id AND papers.paper = ? AND questions.q_id = options.o_id AND q_type != 'info' ORDER BY screen, display_pos, id_num");
    $result->bind_param('i', $this->paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $marks_correct, $marks_incorrect, $display_method, $score_method, $q_media_height, $q_media_width, $q_type, $correct, $score_method, $option_text, $status, $display_pos);
    while ($result->fetch()) {
      if ($q_id != $old_q_id or $old_display_pos != $display_pos) {
        if ($old_q_id != 0) {
          if ($old_status != 'Experimental') {
            $tmp_exclude = $this->exclusions->get_exclusions_by_qid($old_q_id);
            if ($old_q_type == 'random') {
              $tmp_id = $this->getRandomDetails($old_q_id);

              $last_random = $this->paper_buffer[$old_q_id]['random_questions'][$tmp_id];

              $old_q_type = $last_random['q_type'];
              $old_marks_correct = $last_random['marks_correct'];
              $old_option_text = $last_random['option_text'];
              $old_correct = $last_random['correct'];
              $old_display_method = $last_random['display_method'];
              $old_score_method = $last_random['score_method'];
              $this->total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
              $this->orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
            } else {
              $this->total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
              $this->orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
              $this->total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
              $this->checkDisplayExcluded($tmp_exclude, $question_no);
            }
          } else {
            $this->displayExperimental($question_no);
          }
          $stems = 0;
          $old_marks_correct = 0;
        }
        $question_no++;
        $option_no        = 0;
        $old_correct      = array();
        $old_option_text  = array();
        $question_id      = $q_id;
        $this->paper_buffer[$question_id]['q_type']           = $q_type;
        $this->paper_buffer[$question_id]['score_method']     = $score_method;
        $this->paper_buffer[$question_id]['display_method']   = $display_method;
        $this->paper_buffer[$question_id]['marks_correct']    = $marks_correct;
        $this->paper_buffer[$question_id]['marks_incorrect']  = $marks_incorrect;

        if ($q_type == 'blank') {
          $this->paper_buffer[$question_id]['correct'] = $this->extract_blank_correct($option_text, $score_method, $question_id);
        } else {
          $this->paper_buffer[$question_id]['correct'][$option_no] = $correct;
        }
      } else {
        $this->paper_buffer[$question_id]['correct'][$option_no] = $correct;
      }
      $option_no++;
      $stems++;

      $old_q_id             = $q_id;
      $old_display_pos      = $display_pos;
      $old_q_type           = $q_type;
      $old_display_method   = $display_method;
      $old_score_method     = $score_method;
      $old_correct[]        = $correct;
      $old_q_media_width    = $q_media_width;
      $old_q_media_height   = $q_media_height;
      $old_option_text[]    = $option_text;
      $old_marks_correct    = $marks_correct;
      $old_marks_incorrect  = $marks_incorrect;
      $old_status           = $status;
    }
    $result->close();

    if ($old_status != 'Experimental') {
      $tmp_exclude = $this->exclusions->get_exclusions_by_qid($old_q_id);

      if ($old_q_type == 'random') {
        $tmp_id = $this->getRandomDetails($old_q_id);

        $last_random = $this->paper_buffer[$old_q_id]['random_questions'][$tmp_id];

        $old_q_type = $last_random['q_type'];
        $old_marks_correct = $last_random['marks_correct'];
        $old_option_text = $last_random['option_text'];
        $old_correct = $last_random['correct'];
        $old_display_method = $last_random['display_method'];
        $old_score_method = $last_random['score_method'];
        $this->total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $this->orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      } else {
        $this->total_marks += qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $this->orig_total_marks += qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $this->total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        $this->checkDisplayExcluded($tmp_exclude, $question_no);
      }
    } else {
      $this->displayExperimental($question_no);
    }

    $this->question_no = $question_no;
  }

  private function set_log_late() {
    // Check log_late for any records
    $late_ts = strtotime($this->enddate) + 7200;
    $late_end = date('Y-m-d H:i:s', $late_ts);

    $result = $this->db->prepare("SELECT DISTINCT userID, title, surname, first_names, DATE_FORMAT(started, '" . $this->config->get('cfg_long_date_time') . "') AS display_started, started FROM log_late, log_metadata, users WHERE log_late.metadataID = log_metadata.id AND log_metadata.userID = users.id AND paperID = ? AND started >= ? AND started <= ? ORDER BY surname, initials");
    $result->bind_param('iss', $this->paperID, $this->startdate, $late_end);
    $result->execute();
    $result->bind_result($userID, $title, $surname, $first_names, $display_started, $started);
    while ($result->fetch()) {
      $this->log_late[$userID] = $title . ' ' .  $surname . ', ' . $first_names;
      $this->log_late_missing_users[$userID]['display_started'] = $display_started;
      $this->log_late_missing_users[$userID]['started'] = $started;
    }
    $result->close();
  }

  private function load_absent() {
    if ($this->absent == 1) {
      // Get students in the cohort.

      $this->student_cohort = array();
      $i = 0;

      $sql = "SELECT DISTINCT
                             users.id
                           , users.username
                           , title
                           , surname
                           , first_names
                           , initials
                           , student_id
                           , grade
                           , moduleid
                           , gender
              FROM
                    (modules_student, modules, users, sid)
              WHERE
                    modules_student.userID = sid.userID
              AND
                    modules_student.userID = users.id
              AND
                    modules_student.idMod = modules.id
              AND
                    users.roles
              LIKE
                    '%Student%'
              AND
                    idMod
              IN
                    ($this->moduleID_in)
              AND
                    calendar_year = ?";

      $result = $this->db->prepare($sql);
      $result->bind_param('s', $this->calendar_year);
      $result->execute();
      $result->bind_result($userID, $tmp_username, $title, $surname, $first_names, $initials, $student_id, $grade, $moduleid, $gender);
      while ($result->fetch()) {
        $this->student_cohort[$i]['username']      = $tmp_username;
        $this->student_cohort[$i]['userID']        = $userID;
        $this->student_cohort[$i]['name']          = trim(str_replace("'","",$surname) . ',' . $first_names);
        $this->student_cohort[$i]['title']         = $title;
        $this->student_cohort[$i]['surname']       = demo_replace($surname, $this->demo);
        $this->student_cohort[$i]['first_names']   = demo_replace($first_names, $this->demo);
        $this->student_cohort[$i]['initials']      = demo_replace($initials, $this->demo);
        $this->student_cohort[$i]['student_id']    = demo_replace_number($student_id, $this->demo);
        $this->student_cohort[$i]['student_grade'] = $grade;
        $this->student_cohort[$i]['module']        = $moduleid;
        $this->student_cohort[$i]['gender']        = $gender;

        $i++;
      }
      $result->close();
    }
  }

  private function find_users() {     // Get all the users on the module(s) the paper is on.
    $this->user_modules = array();
    if ($this->repmodule != '') {
      $tmp_moduleID_in = $this->repmodule;
    } else {
      $tmp_moduleID_in = $this->moduleID_in;
    }

    if ($this->calendar_year == '') {
      $mod_query = $this->db->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in)");
    } else {
      $mod_query = $this->db->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in) AND calendar_year = ?");
      $mod_query->bind_param('s', $this->calendar_year);
    }
    $mod_query->execute();
    $mod_query->bind_result($idMod, $userID, $tmp_moduleid);
    $mod_query->store_result();
    while ($mod_query->fetch()) {
      $this->user_modules[$userID]['idMod'] = $idMod;
    }
    $mod_query->close();
  }

  private function load_metadata() {
    $this->metadata_array = array();

    if ($this->calendar_year == '') {
      $stmt = $this->db->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN ($this->moduleID_in)");
    } else {
      $stmt = $this->db->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN ($this->moduleID_in) AND calendar_year = ?");
      $stmt->bind_param('s', $this->calendar_year);
    }
    $stmt->execute();
    $stmt->bind_result($student_userID, $type, $value);
    while ($stmt->fetch()) {
      $this->metadata_array['students'][$student_userID][$type] = $value;
      $this->metadata_array['types'][$type] = $type;
    }
    $stmt->close();
  }

  private function load_results() {
    if ($this->studentsonly == 0) {
      $roles_sql = '';
    } else {
      $roles_sql = " AND (users.roles='Student' OR users.roles='graduate')";
    }

    $user_no              = 0;
    $tmp_mark             = 0;
    $user_duration        = 0;
    $marking_complete     = 1;
    $old_screen           = 0;
    $old_duration         = 0;
    $old_userID           = '';
    $old_started          = '';
    $tmp_user_mark_array  = array();
    $tmp_array            = array();
    $this->user_results   = array();

    // Get log data.
    if ($this->paper_type == '0') {
      $sql = '(SELECT log0.q_id
                        , 0 AS paper_type
                        , grade
                        , roles
                        , screen
                        , duration
                        , started AS order_started
                        , user_answer
                        , DATE_FORMAT(started, "' . $this->config->get('cfg_long_date_time') . '") AS display_started
                        , year
                        , title
                        , surname
                        , initials
                        , first_names
                        , email
                        , gender
                        , ipaddress
                        , lab_name
                        , username
                        , users.id
                        , student_id
                        , user_answer
                        , q_type
                        , log_metadata.userID
                        , mark
                        , status
                        , attempt
                      FROM
                          log0, log_metadata, questions, users
                      LEFT JOIN
                          sid
                      ON
                          users.id = sid.userID
                      WHERE
                          log_metadata.userID = users.id
                      AND
                          log0.metadataID = log_metadata.id
                      AND
                          log0.q_id    = questions.q_id
                      AND
                          paperID = ?
                      AND
                          grade LIKE ?
                      ' . $roles_sql . '
                      AND
                          started >= ?
                      AND
                          started <= ? )
                UNION ALL
                    ( SELECT
                          log1.q_id
                        , 1 AS paper_type
                        , grade
                        , roles
                        , screen
                        , duration
                        , started AS order_started
                        , user_answer
                        , DATE_FORMAT(started, "' . $this->config->get('cfg_long_date_time') . '" ) AS display_started
                        , log_metadata.year
                        , title
                        , surname
                        , initials
                        , first_names
                        , email
                        , gender
                        , ipaddress
                        , lab_name
                        , username
                        , users.id
                        , student_id
                        , user_answer
                        , q_type
                        , log_metadata.userID
                        , mark
                        , status
                        , attempt
                      FROM
                        (log1, log_metadata, questions, users)
                      LEFT JOIN
                            sid
                      ON
                            users.id = sid.userID
                      WHERE
                            log_metadata.userID = users.id
                      AND
                            log1.metadataID = log_metadata.id
                      AND
                            log1.q_id    = questions.q_id
                      AND
                            paperID = ?
                      AND
                            grade LIKE ?
                      '. $roles_sql . '
                      AND
                          started >= ?
                      AND
                          started <= ?
                        )
                      ORDER BY
                          userID
                        , order_started
                        , screen';

      $log_query = $this->db->prepare($sql);
      $log_query->bind_param('isssisss', $this->paperID, $this->repcourse, $this->startdate, $this->enddate, $this->paperID, $this->repcourse, $this->startdate, $this->enddate);
    } elseif ($this->paper_type == '2') {
      $log_query = $this->db->prepare("SELECT log2.q_id, 2 AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$this->config->get('cfg_long_date_time')}') AS display_started, year, title, surname, initials, first_names, email, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log2, log_metadata, questions, users ) LEFT JOIN sid ON users.id = sid.userID WHERE log_metadata.userID = users.id AND log2.metadataID = log_metadata.id AND log2.q_id = questions.q_id AND paperID = ? AND grade LIKE ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY userID, started, screen");
      $log_query->bind_param('isss', $this->paperID, $this->repcourse, $this->startdate, $this->enddate);
    } elseif ($this->paper_type == '5') {
      $log_query = $this->db->prepare("SELECT log5.q_id, 5 AS paper_type, grade, roles, 1 AS screen, 0 AS duration, started, NULL AS user_answer, DATE_FORMAT(started, '{$this->config->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, email, gender, NULL AS ipaddress, lab_name, username, users.id, student_id, NULL AS user_answer, q_type, log_metadata.userID, mark, status, 1 AS attempt FROM (log5, log_metadata, questions, users ) LEFT JOIN sid ON users.id=sid.userID WHERE log_metadata.userID = users.id AND log5.metadataID = log_metadata.id AND log5.q_id = questions.q_id AND paperID = ? AND grade LIKE ? $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY userID, started, screen");
      $log_query->bind_param('isss', $this->paperID, $this->repcourse, $this->startdate, $this->enddate);
    } else {
      $log_query = $this->db->prepare("SELECT log$this->paper_type.q_id, $this->paper_type AS paper_type, grade, roles, screen, duration, started, user_answer, DATE_FORMAT(started, '{$this->config->get('cfg_long_date_time')}') AS display_started, log_metadata.year, title, surname, initials, first_names, email, gender, ipaddress, lab_name, username, users.id, student_id, user_answer, q_type, log_metadata.userID, mark, status, attempt FROM (log$this->paper_type, log_metadata, questions, users ) LEFT JOIN sid ON users.id=sid.userID WHERE log_metadata.userID = users.id AND log$this->paper_type.metadataID = log_metadata.id AND log$this->paper_type.q_id = questions.q_id AND paperID = ? AND users.id=log_metadata.userID AND grade LIKE ? $roles_sql AND started>=? AND started<=? ORDER BY userID, started, screen");
      $log_query->bind_param('isss', $this->paperID, $this->repcourse, $this->startdate, $this->enddate);
    }
    $log_query->execute();
    $log_query->bind_result($q_id, $paper_type, $grade, $tmp_roles, $screen, $duration, $started, $user_answer, $display_started, $year, $title, $surname, $initials, $first_names, $email, $gender, $ipaddress, $lab_name, $username, $userID, $student_id, $user_answer, $q_type, $userID, $mark, $status, $attempt);

    while ($log_query->fetch() !== null) {
      if ($this->repmodule != '' and !isset($this->user_modules[$userID]['idMod'])) {
        continue; //this user is not on the module set in repmodule so dont put them in the array
      }

      // Check if user is in log late so that we can catch any users who ONLY have log_late records
      if (in_array($userID, array_keys($this->log_late_missing_users))) {
        unset($this->log_late_missing_users[$userID]);
      }

      if ($old_screen != $screen or $old_userID != $userID or $old_started != $started) {
        $user_duration += $old_duration;
      }
      if (($old_userID != $userID) or ($old_started != $started)) {
        if ($old_userID != '') {
          $this->writeUserResults($tmp_array, $user_no, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete);
          $tmp_mark = 0;
          $tmp_user_mark_array = array();
          $user_duration = 0;
          $marking_complete = 1;
        }
        $tmp_array = array();
        $tmp_array['started']   = $started;
        $tmp_array['username']  = $username;
        $tmp_array['userID']    = $userID;
        $tmp_array['questions'] = 0;

        if (isset($this->user_modules[$userID]['idMod'])) {
          $tmp_array['module'] = $this->user_modules[$userID]['idMod'];
        } else {
          $tmp_array['module'] = '';
        }

        $tmp_array['attempt']         = $attempt;
        $tmp_array['student_grade']   = $grade;
        $tmp_array['roles']           = $tmp_roles;
        $tmp_array['display_started'] = $display_started;
        $tmp_array['title']           = $title;
        $tmp_array['name']            = trim(str_replace("'","",$surname) . ',' . $first_names);
        $tmp_array['surname']         = $surname;
        $tmp_array['initials']        = $initials;
        $tmp_array['first_names']     = $first_names;
        $tmp_array['email']           = $email;
        $tmp_array['student_id']      = $student_id;
        $tmp_array['gender']          = $gender;
        $tmp_array['ipaddress']       = $ipaddress;
        $tmp_array['room']            = $lab_name;
        if ($tmp_array['room'] == '') {
          $tmp_array['room'] = '<span style="color:#808080">&lt;unknown&gt;</span>';
        }
        $tmp_array['duration']        = $user_duration;
        $tmp_array['paper_type']      = $paper_type;
      }
      $this->getUserMark($q_id, $user_answer, $mark, $status, $tmp_mark, $tmp_user_mark_array, $userID, $tmp_array);

      if ($q_type == 'textbox' and !is_numeric($mark)) $marking_complete = 0;
      $tmp_array['questions']++;
      $old_userID   = $userID;
      $old_started  = $started;
      $old_duration = $duration;
      $old_screen   = $screen;
    }

    if (!empty($tmp_array)) {
      if ($this->repmodule == '' or (isset($this->user_modules[$old_userID]['idMod']) and $this->user_modules[$old_userID]['idMod'] == $this->repmodule)) {
        $user_duration += $old_duration;
        $log_query->free_result();
        $log_query->close();
        $this->writeUserResults($tmp_array, $user_no, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete);
      }
    }
  }

  private function load_late_cohort() {
    $this->late_cohort = array();
    $i = 0;
    if (count($this->log_late_missing_users) > 0) {
      // Get students who don't have records in main log
      $late_id_list = implode(',', array_keys($this->log_late_missing_users));

      $sql = <<< SQL
    SELECT DISTINCT u.id, u.username, u.title, u.surname, u.first_names, u.initials, sid.student_id, u.grade, '', u.gender
    FROM users u INNER JOIN sid ON u.id = sid.userID
    WHERE u.id IN ($late_id_list)
SQL;

      $result = $this->db->prepare($sql);
      $result->execute();
      $result->store_result();
      $result->bind_result($userID, $tmp_username, $title, $surname, $first_names, $initials, $student_id, $grade, $moduleid, $gender);
      while ($result->fetch()) {
        $this->late_cohort[$i]['username']         = $tmp_username;
        $this->late_cohort[$i]['userID']           = $userID;
        $this->late_cohort[$i]['name']             = trim(str_replace("'","",$surname) . ',' . $first_names);
        $this->late_cohort[$i]['title']            = $title;
        $this->late_cohort[$i]['surname']          = demo_replace($surname, $this->demo);
        $this->late_cohort[$i]['first_names']      = demo_replace($first_names, $this->demo);
        $this->late_cohort[$i]['initials']         = demo_replace($initials, $this->demo);
        $this->late_cohort[$i]['student_id']       = demo_replace_number($student_id, $this->demo);
        $this->late_cohort[$i]['student_grade']    = $grade;
        $this->late_cohort[$i]['module']           = $moduleid;
        $this->late_cohort[$i]['gender']           = $gender;
        $this->late_cohort[$i]['display_started']  = $this->log_late_missing_users[$userID]['display_started'];
        $this->late_cohort[$i]['started']          = $this->log_late_missing_users[$userID]['started'];

        $i++;
      }
      $result->close();
    }
  }

  private function add_rank() {
    // Put the whole array in marks order.
    $sortby = 'mark';
    $ordering = 'desc';
    $this->user_results = array_csort($this->user_results, $sortby, $ordering, SORT_NUMERIC);

    $display_rank = 1;
    $global_rank  = 1;
    $old_mark     = 0;

    $result_no = count($this->user_results);

    for ($i=0; $i<$result_no; $i++) {
      if ($this->user_results[$i]['mark'] != $old_mark) {
        $display_rank = $global_rank;
      }
      $this->user_results[$i]['rank'] = $display_rank;
      $old_mark = $this->user_results[$i]['mark'];
      $global_rank++;
    }
  }

  private function flag_subpart() {
    $user_no = count($this->user_results);
    if ($this->percent < 100) {
      // Sort by mark order.
      $sortby = 'mark';
      $this->user_results = array_csort($this->user_results, $sortby, $this->ordering, SORT_NUMERIC);
      $this->cohort_size = round(($user_no/100) * $this->percent);

      // Set visible/invisible flag where necessary.
      for ($i=0; $i<$user_no; $i++) {
        if ($i >= $this->cohort_size) {
          $this->user_results[$i]['visible'] = false;
        }
      }
    } else {
      $this->cohort_size = $user_no;
    }
  }

  private function add_late_students() {
    $user_no = count($this->user_results);

    $late_cohort_size = count($this->late_cohort);
    if ($late_cohort_size > 0) {
      for ($i=0; $i < $late_cohort_size; $i++) {
        $late_user = $this->late_cohort[$i];
        if ($this->absent == 1) {
          //check_and_clear_cohort($late_user['username']);
        }
        $this->user_results[$user_no]['name']             = $late_user['name'];
        $this->user_results[$user_no]['mark']             = 0;
        $this->user_results[$user_no]['percent']          = 0;
        $this->user_results[$user_no]['started']          = '';
        $this->user_results[$user_no]['username']         = $late_user['username'];
        $this->user_results[$user_no]['userID']           = $late_user['userID'];
        $this->user_results[$user_no]['student_grade']    = $late_user['student_grade'];
        $this->user_results[$user_no]['module']           = $late_user['module'];
        $this->user_results[$user_no]['display_started']  = $late_user['display_started'];
        $this->user_results[$user_no]['started']          = $late_user['started'];
        $this->user_results[$user_no]['attempt']          = 1;
        $this->user_results[$user_no]['marking_complete'] = 0;
        $this->user_results[$user_no]['title']            = $late_user['title'];
        $this->user_results[$user_no]['surname']          = $late_user['surname'];
        $this->user_results[$user_no]['initials']         = $late_user['initials'];
        $this->user_results[$user_no]['first_names']      = $late_user['first_names'];
        $this->user_results[$user_no]['student_id']       = $late_user['student_id'];
        $this->user_results[$user_no]['gender']           = $late_user['gender'];
        $this->user_results[$user_no]['ipaddress']        = '';
        $this->user_results[$user_no]['duration']         = 0;
        $this->user_results[$user_no]['questions']        = '';
        $this->user_results[$user_no]['paper_type']       = $this->paper_type;
        $this->user_results[$user_no]['room']             = '';
        $this->user_results[$user_no]['visible']          = true;    // Default to visible unless switched off below.
        $this->user_results[$user_no]['roles']            = 'Student';
        $this->user_results[$user_no]['rank']             = 99999999999;
        $user_no++;
      }
    }
  }

  private function add_absent_students() {
    $user_no = count($this->user_results);

    if ($this->absent == 1) {
      // Add in students in cohort who haven't taken the exam.
      $count_student_cohort = count($this->student_cohort);

      for ($i=0; $i < $count_student_cohort; $i++) {
        $this->user_results[$user_no]['name']             = $this->student_cohort[$i]['name'];
        $this->user_results[$user_no]['mark']             = 0;
        $this->user_results[$user_no]['percent']          = 0;
        $this->user_results[$user_no]['started']          = '';
        $this->user_results[$user_no]['username']         = $this->student_cohort[$i]['username'];
        $this->user_results[$user_no]['userID']           = $this->student_cohort[$i]['userID'];
        $this->user_results[$user_no]['student_grade']    = $this->student_cohort[$i]['student_grade'];
        $this->user_results[$user_no]['module']           = $this->student_cohort[$i]['module'];
        $this->user_results[$user_no]['display_started']  = '';
        $this->user_results[$user_no]['title']            = $this->student_cohort[$i]['title'];
        $this->user_results[$user_no]['surname']          = $this->student_cohort[$i]['surname'];
        $this->user_results[$user_no]['initials']         = $this->student_cohort[$i]['initials'];
        $this->user_results[$user_no]['first_names']      = $this->student_cohort[$i]['first_names'];
        $this->user_results[$user_no]['student_id']       = $this->student_cohort[$i]['student_id'];
        $this->user_results[$user_no]['gender']           = $this->student_cohort[$i]['gender'];
        $this->user_results[$user_no]['ipaddress']        = '';
        $this->user_results[$user_no]['duration']         = 0;
        $this->user_results[$user_no]['questions']        = '';
        $this->user_results[$user_no]['paper_type']       = '';
        $this->user_results[$user_no]['room']             = '';
        $this->user_results[$user_no]['visible']          = true;    // Default to visible unless switched off below.
        $this->user_results[$user_no]['roles']            = 'Student';
        $this->user_results[$user_no]['late']             = true;
        $this->user_results[$user_no]['rank']             = 99999999999;
        $user_no++;
      }
    }
  }

  private function convert_moduleIDs() {
    $result_no = count($this->user_results);
    $moduleIDs = array();

    // Build up an array of IDs to module codes.
    for ($i=0; $i<$result_no; $i++) {
      $id = $this->user_results[$i]['module'];
      if ($id != '') {
        if (!isset($moduleIDs[$id])) {
          $moduleIDs[$id] = module_utils::get_moduleid_from_id($id, $this->db);
        }
      }
    }

    // Loop around the results array and convert to codes.
    for ($i=0; $i<$result_no; $i++) {
      if (isset($moduleIDs[$this->user_results[$i]['module']])) {
        $this->user_results[$i]['module'] = $moduleIDs[$this->user_results[$i]['module']];
      }
    }
  }

  private function set_user_no() {
    $this->user_no = count($this->user_results);
  }

  public function generate_stats() {
    // Generate summary statistics.
    $this->set_user_no();
    $mark_total    = 0;
    $percent_total = 0;

    $this->stats['sum_of_marks']  = 0;
    $this->stats['out_of_range']  = 0;
    $this->stats['completed_no']  = 0;
    $this->stats['failures']      = 0;
    $this->stats['passes']        = 0;
    $this->stats['honours']       = 0;
    $this->stats['total_time']    = 0;
    $this->stats['max_mark']      = 0;
    $this->stats['min_mark']      = 9999;
    $this->stats['max_percent']   = 0;
    $this->stats['min_percent']   = 100;

    $median_mark_array    = array();
    $median_percent_array = array();
    $marks_data           = array();

    for ($i=0; $i<$this->user_no; $i++) {
      if (isset($this->user_results[$i]['percent']) and $this->user_results[$i]['questions'] >= $this->question_no and $this->user_results[$i]['visible']) {
        $this->stats['completed_no']++;
        $median_mark_array[] = $this->user_results[$i]['mark'];
        $median_percent_array[] = $this->user_results[$i]['percent'];

        $mark_total += $this->user_results[$i]['mark'];
        $percent_total += $this->user_results[$i]['percent'];
      }
      if (isset($this->user_results[$i]['mark']) and $this->user_results[$i]['visible']) {
        if (isset($this->user_results[$i]['percent'])) {
          $marks_data[] = $this->user_results[$i]['percent'];
          if ($this->user_results[$i]['percent'] < $this->pass_mark) $this->stats['failures']++;
          if ($this->user_results[$i]['percent'] < $this->stats['min_percent']) $this->stats['min_percent'] = $this->user_results[$i]['percent'];
          if ($this->user_results[$i]['percent'] > $this->stats['max_percent']) $this->stats['max_percent'] = $this->user_results[$i]['percent'];
        }
        if ($this->user_results[$i]['percent'] >= $this->pass_mark and $this->user_results[$i]['percent'] < $this->distinction_mark) $this->stats['passes']++;
        if ($this->user_results[$i]['percent'] >= $this->distinction_mark) $this->stats['honours']++;
        if ($this->user_results[$i]['mark'] < $this->stats['min_mark']) $this->stats['min_mark'] = $this->user_results[$i]['mark'];
        if ($this->user_results[$i]['mark'] > $this->stats['max_mark']) $this->stats['max_mark'] = $this->user_results[$i]['mark'];
        $this->stats['sum_of_marks'] += $this->user_results[$i]['mark'];
      }

      if ($this->user_results[$i]['visible']) {
        $this->stats['total_time'] += $this->user_results[$i]['duration'];
      } else {
        $this->stats['out_of_range']++;
      }
    }

    if ($this->stats['min_mark'] == 9999) $this->stats['min_mark'] = 0;    // Reset back to zero if still on default.
    $this->stats['range'] = $this->stats['max_mark'] - $this->stats['min_mark'];
    $this->stats['range_percent'] = $this->stats['max_percent'] - $this->stats['min_percent'];

    // Calculate StdDev.
    $xmean_total = 0;
    $xmean_percent_total = 0;
    for ($i=0; $i<$this->user_no; $i++) {
      if (isset($this->user_results[$i]['questions']) and $this->user_results[$i]['questions'] >= $this->question_no and $this->user_results[$i]['visible'] and $this->stats['completed_no'] > 0) {
        $xmean_total += (($this->user_results[$i]['mark'] - ($mark_total / $this->stats['completed_no'])) * ($this->user_results[$i]['mark'] - ($mark_total / $this->stats['completed_no'])));
        $xmean_percent_total += (($this->user_results[$i]['percent'] - ($percent_total / $this->stats['completed_no'])) * ($this->user_results[$i]['percent'] - ($percent_total / $this->stats['completed_no'])));
      }
    }
    if ($this->stats['completed_no'] > 1) {
      $this->stats['stddev_mark']    = sqrt($xmean_total / ($this->stats['completed_no'] - 1));
      $this->stats['stddev_percent'] = sqrt($xmean_percent_total / ($this->stats['completed_no'] - 1));
    } else {
      $this->stats['stddev_mark']    = 0;
      $this->stats['stddev_percent'] = 0;
    }

    if ($this->stats['completed_no'] > 0) {
      $this->stats['mean_mark']      = $mark_total / $this->stats['completed_no'];
      $this->stats['mean_percent']   = $percent_total / $this->stats['completed_no'];
      $this->stats['median_mark']    = MathsUtils::median($median_mark_array);
      $this->stats['median_percent'] = MathsUtils::median($median_percent_array);
    } else {
      $this->stats['mean_mark']      = 0;
      $this->stats['mean_percent']   = 0;
      $this->stats['median_mark']    = 0;
      $this->stats['median_percent'] = 0;
    }


    $this->stats['q1'] = MathsUtils::percentile($marks_data, 0.75);
    $this->stats['q2'] = MathsUtils::percentile($marks_data, 0.50);
    $this->stats['q3'] = MathsUtils::percentile($marks_data, 0.25);

    for ($i=1; $i<10; $i++) {
      $this->stats["decile$i"] = MathsUtils::percentile($marks_data, ($i / 10));
    }
  }

  private function add_deciles() {
    for ($student=0; $student<$this->user_no; $student++) {
      $this->user_results[$student]['decile'] = 10;  // Set all to 10 as a baseline

      for ($i=9; $i>0; $i--) {
        if ($this->user_results[$student]['percent'] >= $this->stats["decile$i"]) {
          $this->user_results[$student]['decile'] = $i;
        }
      }
    }
  }

  public function sort_results() {
    $tmp_sort = ($this->sortby != 'result' and $this->sortby != 'percent') ? $this->sortby : 'percent';
    if ($tmp_sort == 'adj_percent') $tmp_sort = 'mark';
    if ($tmp_sort == 'classification') $tmp_sort = 'mark';
    if ($tmp_sort == 'mark' or $tmp_sort == 'rank' or $tmp_sort == 'decile') {
      $method = SORT_NUMERIC;
    } else {
      $method = SORT_STRING;
    }
    $this->user_results = array_csort($this->user_results, $tmp_sort, $this->ordering, $method);
  }

}
?>