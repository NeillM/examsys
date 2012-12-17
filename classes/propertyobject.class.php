<?php
/**
 *
 * PaperObject Class
 *
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */



class PropertyObject {

  private $property_id;
  private $paper_title;
  private $start_date;
  private $end_date;
  private $time_zone;
  private $paper_type;
  private $paper_prologue;
  private $paper_postscript;
  private $bgcolor;
  private $fgcolor;
  private $themecolor;
  private $labelcolor;
  private $fullscreen;
  private $marking;
  private $bidirectional;
  private $pass_mark;
  private $distinction_mark;
  private $paper_ownerID;
  private $folder;
  private $labs;
  private $rubric;
  private $calculator;
  private $externals;
  private $exam_duration;
  private $deleted;
  private $created;
  private $random_mark;
  private $total_mark;
  private $display_correct_answer;
  private $display_question_mark;
  private $display_students_response;
  private $display_feedback;
  private $hide_if_unanswered;
  private $calendar_year;
  private $internal_reviewers;
  private $external_review_deadline;
  private $internal_review_deadline;
  private $sound_demo;
  private $latex_needed;
  private $password;
  private $retired;
  private $crypt_name;

  /**
   * @return the $property_id
   */
  public function get_property_id( ) {
      return $this->property_id;
  }

  /**
   * @param field_type $property_id
   */
  public function set_property_id( $property_id ) {
      $this->property_id = $property_id;
  }

  /**
   * @return the $paper_title
   */
  public function get_paper_title( ) {
      return $this->paper_title;
  }

  /**
   * @param field_type $paper_title
   */
  public function set_paper_title( $paper_title ) {
      $this->paper_title = $paper_title;
  }

  /**
   * @return the $start_date
   */
  public function get_start_date( ) {
      return $this->start_date;
  }

  /**
   * @param field_type $start_date
   */
  public function set_start_date( $start_date ) {
      $this->start_date = $start_date;
  }

  /**
   * @return the $end_date
   */
  public function get_end_date( ) {
      return $this->end_date;
  }

  /**
   * @param field_type $end_date
   */
  public function set_end_date( $end_date ) {
      $this->end_date = $end_date;
  }

  /**
   * @return the $time_zone
   */
  public function get_time_zone( ) {
      return $this->time_zone;
  }

  /**
   * @param field_type $time_zone
   */
  public function set_time_zone( $time_zone ) {
      $this->time_zone = $time_zone;
  }

  /**
   * @return the $paper_type
   */
  public function get_paper_type( ) {
      return $this->paper_type;
  }

  /**
   * @param field_type $paper_type
   */
  public function set_paper_type( $paper_type ) {
      $this->paper_type = $paper_type;
  }

  /**
   * @return the $paper_prologue
   */
  public function get_paper_prologue( ) {
      return $this->paper_prologue;
  }

  /**
   * @param field_type $paper_prologue
   */
  public function set_paper_prologue( $paper_prologue ) {
      $this->paper_prologue = $paper_prologue;
  }

  /**
   * @return the $paper_postscript
   */
  public function get_paper_postscript( ) {
      return $this->paper_postscript;
  }

  /**
   * @param field_type $paper_postscript
   */
  public function set_paper_postscript( $paper_postscript ) {
      $this->paper_postscript = $paper_postscript;
  }

  /**
   * @return the $bgcolor
   */
  public function get_bgcolor( ) {
      return $this->bgcolor;
  }

  /**
   * @param field_type $bgcolor
   */
  public function set_bgcolor( $bgcolor ) {
      $this->bgcolor = $bgcolor;
  }

  /**
   * @return the $fgcolor
   */
  public function get_fgcolor( ) {
      return $this->fgcolor;
  }

  /**
   * @param field_type $fgcolor
   */
  public function set_fgcolor( $fgcolor ) {
      $this->fgcolor = $fgcolor;
  }

  /**
   * @return the $themecolor
   */
  public function get_themecolor( ) {
      return $this->themecolor;
  }

  /**
   * @param field_type $themecolor
   */
  public function set_themecolor( $themecolor ) {
      $this->themecolor = $themecolor;
  }

  /**
   * @return the $labelcolor
   */
  public function get_labelcolor( ) {
      return $this->labelcolor;
  }

  /**
   * @param field_type $labelcolor
   */
  public function set_labelcolor( $labelcolor ) {
      $this->labelcolor = $labelcolor;
  }

  /**
   * @return the $fullscreen
   */
  public function get_fullscreen( ) {
      return $this->fullscreen;
  }

  /**
   * @param field_type $fullscreen
   */
  public function set_fullscreen( $fullscreen ) {
      $this->fullscreen = $fullscreen;
  }

  /**
   * @return the $marking
   */
  public function get_marking( ) {
      return $this->marking;
  }

  /**
   * @param field_type $marking
   */
  public function set_marking( $marking ) {
      $this->marking = $marking;
  }

  /**
   * @return the $bidirectional
   */
  public function get_bidirectional( ) {
      return $this->bidirectional;
  }

  /**
   * @param field_type $bidirectional
   */
  public function set_bidirectional( $bidirectional ) {
      $this->bidirectional = $bidirectional;
  }

  /**
   * @return the $pass_mark
   */
  public function get_pass_mark( ) {
      return $this->pass_mark;
  }

  /**
   * @param field_type $pass_mark
   */
  public function set_pass_mark( $pass_mark ) {
      $this->pass_mark = $pass_mark;
  }

  /**
   * @return the $distinction_mark
   */
  public function get_distinction_mark( ) {
      return $this->distinction_mark;
  }

  /**
   * @param field_type $distinction_mark
   */
  public function set_distinction_mark( $distinction_mark ) {
      $this->distinction_mark = $distinction_mark;
  }

  /**
   * @return the $paper_ownerid
   */
  public function get_paper_ownerid( ) {
      return $this->paper_ownerid;
  }

  /**
   * @param field_type $paper_ownerid
   */
  public function set_paper_ownerid( $paper_ownerid ) {
      $this->paper_ownerid = $paper_ownerid;
  }

  /**
   * @return the $folder
   */
  public function get_folder( ) {
      return $this->folder;
  }

  /**
   * @param field_type $folder
   */
  public function set_folder( $folder ) {
      $this->folder = $folder;
  }

  /**
   * @return the $labs
   */
  public function get_labs( ) {
      return $this->labs;
  }

  /**
   * @param field_type $labs
   */
  public function set_labs( $labs ) {
      $this->labs = $labs;
  }

  /**
   * @return the $rubric
   */
  public function get_rubric( ) {
      return $this->rubric;
  }

  /**
   * @param field_type $rubric
   */
  public function set_rubric( $rubric ) {
      $this->rubric = $rubric;
  }

  /**
   * @return the $calculator
   */
  public function get_calculator( ) {
      return $this->calculator;
  }

  /**
   * @param field_type $calculator
   */
  public function set_calculator( $calculator ) {
      $this->calculator = $calculator;
  }

  /**
   * @return the $externals
   */
  public function get_externals( ) {
      return $this->externals;
  }

  /**
   * @param field_type $externals
   */
  public function set_externals( $externals ) {
      $this->externals = $externals;
  }

  /**
   * @return the $exam_duration
   */
  public function get_exam_duration( ) {
      return $this->exam_duration;
  }

  /**
   * @param field_type $exam_duration
   */
  public function set_exam_duration( $exam_duration ) {
      $this->exam_duration = $exam_duration;
  }

  /**
   * @return the $deleted
   */
  public function get_deleted( ) {
      return $this->deleted;
  }

  /**
   * @param field_type $deleted
   */
  public function set_deleted( $deleted ) {
      $this->deleted = $deleted;
  }

  /**
   * @return the $created
   */
  public function get_created( ) {
      return $this->created;
  }

  /**
   * @param field_type $created
   */
  public function set_created( $created ) {
      $this->created = $created;
  }

  /**
   * @return the $random_mark
   */
  public function get_random_mark( ) {
      return $this->random_mark;
  }

  /**
   * @param field_type $random_mark
   */
  public function set_random_mark( $random_mark ) {
      $this->random_mark = $random_mark;
  }

  /**
   * @return the $total_mark
   */
  public function get_total_mark( ) {
      return $this->total_mark;
  }

  /**
   * @param field_type $total_mark
   */
  public function set_total_mark( $total_mark ) {
      $this->total_mark = $total_mark;
  }

  /**
   * @return the $display_correct_answer
   */
  public function get_display_correct_answer( ) {
      return $this->display_correct_answer;
  }

  /**
   * @param field_type $display_correct_answer
   */
  public function set_display_correct_answer( $display_correct_answer ) {
      $this->display_correct_answer = $display_correct_answer;
  }

  /**
   * @return the $display_question_mark
   */
  public function get_display_question_mark( ) {
      return $this->display_question_mark;
  }

  /**
   * @param field_type $display_question_mark
   */
  public function set_display_question_mark( $display_question_mark ) {
      $this->display_question_mark = $display_question_mark;
  }

  /**
   * @return the $display_students_response
   */
  public function get_display_students_response( ) {
      return $this->display_students_response;
  }

  /**
   * @param field_type $display_students_response
   */
  public function set_display_students_response( $display_students_response ) {
      $this->display_students_response = $display_students_response;
  }

  /**
   * @return the $display_feedback
   */
  public function get_display_feedback( ) {
      return $this->display_feedback;
  }

  /**
   * @param field_type $display_feedback
   */
  public function set_display_feedback( $display_feedback ) {
      $this->display_feedback = $display_feedback;
  }

  /**
   * @return the $hide_if_unanswered
   */
  public function get_hide_if_unanswered( ) {
      return $this->hide_if_unanswered;
  }

  /**
   * @param field_type $hide_if_unanswered
   */
  public function set_hide_if_unanswered( $hide_if_unanswered ) {
      $this->hide_if_unanswered = $hide_if_unanswered;
  }

  /**
   * @return the $calendar_year
   */
  public function get_calendar_year( ) {
      return $this->calendar_year;
  }

  /**
   * @param field_type $calendar_year
   */
  public function set_calendar_year( $calendar_year ) {
      $this->calendar_year = $calendar_year;
  }

  /**
   * @return the $internal_reviewers
   */
  public function get_internal_reviewers( ) {
      return $this->internal_reviewers;
  }

  /**
   * @param field_type $internal_reviewers
   */
  public function set_internal_reviewers( $internal_reviewers ) {
      $this->internal_reviewers = $internal_reviewers;
  }

  /**
   * @return the $external_review_deadline
   */
  public function get_external_review_deadline( ) {
      return $this->external_review_deadline;
  }

  /**
   * @param field_type $external_review_deadline
   */
  public function set_external_review_deadline( $external_review_deadline ) {
      $this->external_review_deadline = $external_review_deadline;
  }

  /**
   * @return the $internal_review_deadline
   */
  public function get_internal_review_deadline( ) {
      return $this->internal_review_deadline;
  }

  /**
   * @param field_type $internal_review_deadline
   */
  public function set_internal_review_deadline( $internal_review_deadline ) {
      $this->internal_review_deadline = $internal_review_deadline;
  }

  /**
   * @return the $sound_demo
   */
  public function get_sound_demo( ) {
      return $this->sound_demo;
  }

  /**
   * @param field_type $sound_demo
   */
  public function set_sound_demo( $sound_demo ) {
      $this->sound_demo = $sound_demo;
  }

  /**
   * @return the $latex_needed
   */
  public function get_latex_needed( ) {
      return $this->latex_needed;
  }

  /**
   * @param field_type $latex_needed
   */
  public function set_latex_needed( $latex_needed ) {
      $this->latex_needed = $latex_needed;
  }

  /**
   * @return the $password
   */
  public function get_password( ) {
      return $this->password;
  }

  /**
   * @param field_type $password
   */
  public function set_password( $password ) {
      $this->password = $password;
  }

  /**
   * @return the $retired
   */
  public function get_retired( ) {
      return $this->retired;
  }

  /**
   * @param field_type $retired
   */
  public function set_retired( $retired ) {
      $this->retired = $retired;
  }

  /**
   * @return the $crypt_name
   */
  public function get_crypt_name( ) {
      return $this->crypt_name;
  }

  /**
   * @param field_type $crypt_name
   */
  public function set_crypt_name( $crypt_name ) {
      $this->crypt_name = $crypt_name;
  }





}
