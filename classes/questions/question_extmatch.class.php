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
 * Class for Extended Matching questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

Class QuestionEXTMATCH extends QuestionEdit {
  /** @var string[] The stem text for the scenarios. */
  protected $stems = array();

  /**
   * An array of the names of the media files used in the question.
   *
   * The first entry is the leadin media, the rest of the entries are for the scenarios.
   *
   * @var string[]
   */
  protected $all_media_names = array();

  /**
   * An array of the heights of the media files used in the question.
   *
   * The first entry is the leadin media, the rest of the entries are for the scenarios.
   *
   * @var int[]
   */
  protected $all_media_heights = array();

  /**
   * An array of the widths of the media files used in the question.
   *
   * The first entry is the leadin media, the rest of the entries are for the scenarios.
   *
   * @var int[]
   */
  protected $all_media_widths = array();

  /** @var string[] The feedback given when answered correctly for the scenarios. */
  protected $all_feedback = array();
  
  /** @var string[] The feedback given when answered incorrectly for the scenarios. */
  protected $_answer_negative = array();

  /** @var int The maximum number of options in the question. */
  public $max_options = 23;

  /** @var int The minimum number of options in the question. */
  protected $min_options = 3;

  /** @var int The maximum number of scenarios in the question. */
  public $max_stems = 10;
  
  protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');
  protected $_fields_editable = array('theme', 'leadin', 'notes', 'score_method', 'option_order', 'bloom', 'status');
  protected $_fields_compound = array('stem', 'media', 'correct_fback');
  
  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);
    
    // 'correct' is not a unified field for Extmatch because it is compound
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
  }

  // ACCESSORS
  
  /**
   * Get an array of stems for the compounded scenarios
   * @return multitype:
   */
  public function get_all_stems() {
    $this->get_scenario();
    return $this->stems;
  }
  
  /**
   * Compound the stems into a single string and set as the scenario
   * @return multitype:
   */
  public function set_all_stems($value) {
    $this->stems = $value;
    $this->set_scenario('dummy');
  }
  
  /**
   * Get the question media as an array containing filename, width and height
   * @return array
   */
  public function get_all_media() {
    $this->get_media();
    return array('filenames' => $this->all_media_names, 'widths' => $this->all_media_widths, 'heights' => $this->all_media_heights);
  }
  
  /**
   * Get the question media as an array containing filename, width and height
   * @return array
   */
  public function set_all_media($value) {
    $this->set_all_medias($value['filenames']);
    $this->set_all_media_widths($value['widths']);
    $this->set_all_media_heights($value['heights']);
  }
  
  /**
   * Get the question media filenames as an array
   * @return array
   */
  public function get_all_medias() {
    $this->get_media();
    return $this->all_media_names;
  }
  
  /**
   * Compound the media filenames into a single string and set as the media
   * @return multitype:
   */
  public function set_all_medias($value) {
    $this->all_media_names = $value;
    $this->set_media('dummy');
  }
  
  /**
   * Get the question media widths as an array
   * @return array
   */
  public function get_all_media_widths() {
    $this->get_media();
    return $this->all_media_widths;
  }
  
  /**
   * Compound the media widths into a single string and set as the media
   * @return multitype:
   */
  public function set_all_media_widths($value) {
    $this->all_media_widths = $value;
    $this->set_media('dummy');
  }
  
  /**
   * Get the question media heights as an array
   * @return array
   */
  public function get_all_media_heights() {
    $this->get_media();
    return $this->all_media_heights;
  }
  
  /**
   * Compound the media heights into a single string and set as the media
   * @return multitype:
   */
  public function set_all_media_heights($value) {
    $this->all_media_heights = $value;
    $this->set_media('dummy');
  }
  
  /**
   * Get the question feedbacks as an array
   * @return array
   */
  public function get_all_correct_fbacks() {
    $this->get_correct_fback();
    return $this->all_feedback;
  }
    
  /**
   * Compound the question feedbacks into a single string and set as the correct feedback
   * @return multitype:
   */
  public function set_all_correct_fbacks($value) {
    $this->all_feedback = $value;
    $this->set_correct_fback('dummy');
  }
  
  /**
   * Get the question media as an array containing filename, width and height
   * @return array
   */
  public function get_media() {
    if ($this->media != '') {
      $this->all_media_names = explode('|', $this->media);
      $this->all_media_widths = explode('|', $this->media_width);
      $this->all_media_heights = explode('|', $this->media_height);
    } else {
      $this->all_media_names = $this->all_media_widths = $this->all_media_heights = array_fill (0, 11, '');
    }
          
    return $this->media;
  }
  
  /**
   * Set the question scenario
   * @param string $value
   */
  public function set_media($value) {
    $this->media = implode('|', $this->all_media_names);
    $this->media_width = implode('|', $this->all_media_widths);
    $this->media_height = implode('|', $this->all_media_heights);
  }

  /**
   * Get the question scenario
   * @return string
   */
  public function get_scenario() {
    if ($this->scenario != '') {
      $this->stems = explode('|', $this->scenario);
    }
    return $this->scenario;
  }

  /**
   * Set the question scenario
   * @param string $value
   */
  public function set_scenario($value) {
    $this->scenario = implode('|', $this->stems);
  }
  
  /**
   * Get the question correct feedback
   * @return string
   */
  public function get_correct_fback() {
    if ($this->correct_fback != '') {
      $this->all_feedback = explode('|', $this->correct_fback);
    }
    return $this->correct_fback;
  }
  
  /**
   * Set the question correct feedback
   * @param string $value
   */
  public function set_correct_fback($value) {
    $this->correct_fback = implode('|', $this->all_feedback);
  }
}

