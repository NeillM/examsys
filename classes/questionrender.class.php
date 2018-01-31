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
* Question Render package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2018 onwards The University of Nottingham
*/

/**
 * Question rendering helper class.
 */
abstract class questionrender {

  /**
   * DB connection
   * @var mysqli 
   */
  private $db;

  /**
   * Config object
   * @var object
   */
  private $config;

  /**
   * Question answered state
   * @var boolean
   */
  private $unanswered;

  /**
   * Colour of 'labels' in paper
   * @var string
   */
  private $labelcolour;

  /**
   * Calculator state of question
   * @var boolean
   */
  private $displaycalc;

  /**
   * Prologue state in paper
   * @var boolean
   */
  private $displayprologue;

  /**
   * Theme state of question
   * @var boolean
   */
  private $displaytheme;

  /**
   * Media state of question
   * @var boolean
   */
  private $displaymedia;

  /**
   * Scenario state of question
   * @var boolean
   */
  private $displayscenario;

  /**
   * Notes state of question
   * @var boolean
   */
  private $displaynotes;

  /**
   * Leadin state of question
   * @var boolean
   */
  private $displayleadin;

  /**
   * Question header state
   * @var boolean
   */
  private $displaydefault;

  /**
   * Info block flag
   * @var boolean
   */
  private $displayinfo;

  /**
   * Negative marking state of question
   * @var boolean
   */
  private $negativemarking;

  /**
   * Display method used by question
   * @var string
   */
  private $displaymethod;

  /**
   * Display state of option media
   * @var boolean
   */
  private $displayoptionmedia;

  /**
   * Question scenario
   * @var string
   */
  private $scenario;

  /**
   * Question notes
   * @var string
   */
  private $notes;

  /**
   * Question media
   * @var string
   */
  private $q_media;

  /**
   * Question type
   * @var string
   */
  private $questiontype;

  /**
   * Question options
   * @var array
   */
  private $options;

  /**
   * Paper prologue
   * @var string
   */
  private $prologue;

  /**
   * Question theme
   * @var string
   */
  private $theme;

  /**
   * Question number of options
   * @var string
   */
  private $optionno;

  /**
   * Paper type
   * @var string
   */
  private $papertype;

  /**
   * Question leadin
   * @var string
   */
  private  $leadin;

  /**
   * Question langague
   * @var string
   */
  private $language;

  /**
   * Question assigned display number
   * @var boolean
   */
  private $assignednumber;

  /**
   * Question media id
   * @var integer
   */
  private $mediaid;

  /**
   * Question media filename
   * @var string
   */
  private $mediafile;

  /**
   * Question media width
   * @var integer
   */
  private $mediawidth;
  /**
   * Question media height
   * @var integer
   */

  private $mediaheight;

  /**
   * Question media url
   * @var string
   */
  private $mediaurl;

  /**
   * Question media url
   * @var string
   */
  private $mediatype;

  /**
   * Question media border state
   * @var boolean
   */
  private $mediaborder;

  /**
   * Question media border colour
   * @var string
   */
  private $mediabordercolour;

  /**
   * Question media edit state
   * @var boolean
   */
  private $mediaedit;

  /**
   * Question media delete state
   * @var boolean
   */
  private $mediadelete;

  /**
   * Question display number
   * @var integer
   */
  private $questionno;

  /**
   * Question part id
   * @var integer
   */
  private $partid;

  /**
   * Marks for question
   * @var float
   */
  private $finalmarks;

  /**
   * Question score method
   * @var string
   */
  private $scoremethod;
  
  /**
   * Question bonus type
   * @var string
   */
  private $bonus;

  /**
   * Order of question options
   * @var string 
   */
  private $optionorder;

  /**
   * Default unanswered state
   */
  const default_unanswered = true;

  /**
   * Default label colour
   */
  const default_labelcolour = '#C00000';

  /**
   * Default calculator state
   */
  const default_displaycalc = true;

  /**
   * Default prologue state
   */
  const default_displayprologue = false;

  /**
   * Default theme state
   */
  const default_displaytheme = false;

  /**
   * Default media state
   */
  const default_displaymedia = false;

  /**
   * Default media state
   */
  const default_displayscenario = false;

  /**
   * Default notes state
   */
  const default_displaynotes = false;

  /**
   * Default leadin state
   */
  const default_displayleadin = false;

  /**
   * Default display default question header state
   */
  const default_displaydefault = false;

  /**
   * Default negative marking state
   */
  const defatul_negativemarking = false;

  /**
   * Default question display method
   */
  const default_displaymethod = '';

  /**
   * Default info block flag
   */
  const default_displayinfo = false;

  /**
   * Default option media display state
   */
  const default_displayoptionmedia = false;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  /**
   * Constructor
   */
  function __construct() {
    $this->config = Config::get_instance();
    $this->db = $this->config->db;
  }

  /**
   * Abstract function to set quetion header
   */
  abstract public function set_question_head();

  /**
   * Abstract function to set question
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param integer $useranswerid id of user answer
   * @param integer $user_dismissid id of option user dismissed
   */
  abstract public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid);

  /**
   * Abstract function to set question options
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param integer $marks reference to marks available for question
   * @param float $marks_incorrect marks for incorrect answer
   */
  abstract public function set_option($part_id, $useranswerid, $user_dismissid, &$marks, $marks_incorrect);

  /**
   * Set an attribute
   * @param string $attribute
   * @param mixed $value
   */
  public function set($attribute, $value) {
    $this->$attribute = $value;
  }
  /**
   * Get an attribute
   * @param string $attribute
   * @return mixed
   */
  public function get($attribute) {
    if (empty($attribute)) {
      if (empty(constant('default_' . $attribute))) {
        return null;
      } else {
        return constant('default_' . $attribute);
      }
    } else {
      return $this->$attribute;
    }
  }

  /**
   * Get options
   * @param integer $id option id
   * @return array
   */
  public function get_opt($id) {
    return $this->options[$id];
  }

  /**
   * Set options
   * @param integer $id option id
   * @param array $opt options
   */
  public function set_opt($id, $opt) {
    $this->options[$id] = $opt;
  }

  /**
   * Render question
   * @global array $used_questions user log data for questions
   * @global type $user_dismiss user dismiss data for questions
   * @global type $user_order the order the user gets the question options
   * @global type $language system langauge
   * @param type $screen_pre_submitted has the user been on this screen before
   * @param type $q_displayed loop id of question
   * @param type $string language strings
   * @param type $question question data
   * @param type $pid paper id
   * @param type $current_screen current screen id
   * @param type $question_no current question number
   * @param type $user_answers users answers
   */
  public function display_question($screen_pre_submitted, $q_displayed, $string, &$question, $pid, $current_screen, &$question_no, $user_answers) {
    global $used_questions, $user_dismiss, $user_order, $language;
 
    $propertyObj = PaperProperties::get_paper_properties_by_id($pid, $this->db, $string, true);
    $paper_type = $propertyObj->get_paper_type();
    $render = new render($this->config);

    if ($screen_pre_submitted == 1 and $q_displayed == 0) {
      $this->set('unanswered', true);
    } else {
      $this->set('unanswered', false);
    }

    // Attempt to display paper prolog
    if ($q_displayed == 0 and $current_screen == 1 and $propertyObj->get_paper_prologue() != '') {
      $this->set('prologue', $propertyObj->get_paper_prologue());
      $this->set('displayprologue', true);
    }

    // Get the media directory object.
    $mediadirectory = rogo_directory::get_directory('media');

    $q_id = $question['q_id'];
    $option_no = count($question['options']);

    // Determine if negative marking is used.
    $neg_marking = false;
    if (isset($question['object']) and method_exists($question['object'], 'is_negative_marked')) {
      $neg_marking = $question['object']->is_negative_marked();
    } else {
      foreach ($question['options'] as $tmp_option) {
        if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
      }
    }

    // Process the order
    $question['option_order'] = array();
    if (isset($question['q_option_order']) and ($question['q_option_order'] == 'random' or $question['q_option_order'] == 'alphabetic')) {
      if (!isset($user_order[$current_screen][$q_id]) or $user_order[$current_screen][$q_id] == '') {
        if ($question['q_option_order'] == 'random') {
          for ($i=0; $i<$option_no; $i++) {
            $question['option_order'][$i] = $i;
          }
          shuffle($question['option_order']);
        } elseif ($question['q_option_order'] == 'alphabetic') {
          $tmp_order_array = array();
          for ($i=0; $i<$option_no; $i++) {
            $tmp_order_array[$i] = strtolower($question['options'][$i]['option_text']);
          }
          asort($tmp_order_array);
          foreach ($tmp_order_array as $key => $value) {
            $question['option_order'][]= $key;
          }
        } else {
          // Make up the order array in the existing order
          for ($i=0; $i<$option_no; $i++) {
            $question['option_order'][$i] = $i;
          }
        }
      } else {
        // Set the order array to what is stored in the users log record
        $question['option_order'] = explode(',', $user_order[$current_screen][$q_id]);
      }

      // Re-arrange the options array
      $new_options = array();
      for ($i=0; $i<$option_no; $i++) {
        $new_options[$i] = $question['options'][$question['option_order'][$i]];
      }
      $question['options'] = $new_options;
    } else {
      // Make up the order array in the existing order
      for ($i=0; $i<$option_no; $i++) {
        $question['option_order'][$i] = $i;
      }
    }

    $question_no++;
    $textboxes_seen = array();

    if ($question['theme'] != '') {
      $this->set('theme', $question['theme']);
      $this->set('displaytheme', true);
    }

    $this->set('optionno', $option_no);
    $this->set('papertype', $paper_type);
    $this->set('assignednumber',  $question['assigned_number']);
    $this->set('scenario', $question['scenario']);
    $this->set('notes', $question['notes']);
    $this->set('q_media', $question['q_media']);
    $this->set('leadin', $question['leadin']);
    $this->set('language', $language);
    $this->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');

    if ($question['q_type'] == 'mcq') {
      
      $this->set_question_head();
    } else {
      if ($question['q_type'] != 'info' and $question['q_type'] != 'sct') {
        if ($question['scenario'] != '' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'matrix' and $question['q_type'] != 'likert' and $question['q_type'] != 'enhancedcalc') {
          $this->set('displaydefault', true);
          if ($question['notes'] != '') {
            $this->set('displaynotes', true);
          }
          if ($question['scenario'] != '') {
            $this->set('displayscenario', true);
          }
        }
        if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'area' and $question['q_type'] != 'enhancedcalc') {
          $this->set('displaydefault', true);
          $this->set('displaymedia', true);
        }
        if ($question['q_type'] != 'likert') {
          $this->set('displaydefault', true);
          if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and in_array($question['q_type'], array('extmatch', 'matrix', 'enhancedcalc')))) {
            $this->set('displaynotes', true);
          }
          if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'enhancedcalc') {
            $this->set('displayleadin', true);
          }
        }
      }
    }

    if ($question['q_type'] == 'info') {
      // Special processing of Information Blocks.
      $this->set('displayinfo', true);
      if ($question['q_media'] != '') {
        $this->set('displaymedia', true);
      }
      $this->set('displayleadin', true);
      $question_no--;
    }

    $part_id = 0;
    $marks = 0;

    $render->render($this, $string, 'paper/question_header.html');

    // What is the users current answer.
    if (isset($user_answers[$current_screen][$q_id])) {
      $useranswerid = $user_answers[$current_screen][$q_id];
    } else {
      $useranswerid = null;
    }

    // What is the users current dismissed.
    if (isset($user_dismiss[$current_screen][$q_id])) {
      $user_dismissid = $user_dismiss[$current_screen][$q_id];
    } else {
      $user_dismissid = null;
    }

    // Pre-question processing
    $this->set('questionno', $question_no);
    $this->set('displaymethod', $question['display_method']);
    switch ($question['q_type']) {
      case 'enhancedcalc':
        if (isset($user_answers[$current_screen][$q_id])) {
          $d = array();
          $d['useranswer'] = $user_answers[$current_screen][$q_id];
          $question['object']->load($d);
        }
        $question['object']->load_all_user_answers($user_answers);
        break;
      case 'dichotomous':
        break;
      case 'mcq':
        $this->set_question($screen_pre_submitted, $useranswerid, $user_dismissid);
        break;
      case 'mrq':
        $mrq_correct = 0;
        if ($question['score_method'] == 'Mark per Question') {
          $mrq_correct = $option_no;
        } else {
          for ($i=0; $i<$option_no; $i++) {
            if ($question['options'][$i]['correct'] == 'y') $mrq_correct++;
          }
        }
        if (isset($user_answers[$current_screen][$q_id])) {
          $answer_parts = explode(':', $user_answers[$current_screen][$q_id]);
          $len_answer = strlen($answer_parts[0]);
        } else {
          $len_answer = 0;
        }
        if (isset($answer_parts) and $answer_parts[0] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
          $this->set('unanswered', true);
        } else {
          $this->set('unanswered', false);
        }
        break;
      case 'rank':
        break;
      case 'likert':
        $questiondata['likertna'] = false;
        $na = false;
        $likert_display = explode('|',$question['display_method']);
        $likert_col_no = substr_count($question['display_method'],'|');
        if ($likert_display[$likert_col_no] == 'true') {
          $likert_col_no++;
          $na = true;
        }
        if ($question['notes'] != '') {
          $this->set('displaynotes', true);
          $questiondata['likertnotescolspan'] = $likert_col_no + 1;
        }
        if ($question['scenario'] != '') {
          $this->set('displayscenario', true);
          $questiondata['likertscenariocolspan'] = $likert_col_no + 2;
        }
        if ($na == true) {
          $questiondata['likertna'] = true;
        }
        $questiondata['likertdisplay'][0] = $likert_display[0];
        $temp_end = substr_count($question['display_method'],'|') - 1;
        for ($i=1; $i<=$temp_end; $i++) {
          $questiondata['likertdisplay'][$i] = $likert_display[$i];
        }
        break;
      case 'extmatch':
      case 'matrix':
        $matching_scenarios = explode('|', $question['scenario']);
        $matching_media = explode('|', $question['q_media']);
        $matching_media_width = explode('|', $question['q_media_width']);
        $matching_media_height = explode('|', $question['q_media_height']);
        $matching_options = array();
        if (isset($user_answers[$current_screen][$q_id])) {
          $matching_users_answers = explode('|', $user_answers[$current_screen][$q_id]);
        } else {
          $matching_users_answers = array();
        }
        $matching_answers = explode('|', $question['options'][0]['correct']);
        break;
      case 'sct':
        // SCT stalls vignette in scenario so must dispaly. 
        $this->set('displayscenario', true);
        if ($question['notes'] != '') {
          $this->set('displaynotes', true);
        }
        if ($question['q_media'] != '') {
          $this->set('displaymedia', true);
        }

        $sct_parts = explode('~',$question['leadin']);
        $sct_titles = array(1=>$string['hypothesis'], 2=>$string['investigation'], 3=>$string['prescription'], 4=>$string['intervention'], 5=>$string['treatment']);
        $questiondata['scttitle'] = $sct_titles[$question['display_method']];
        $questiondata['sctpart0'] = $sct_parts[0];
        $questiondata['sctpart1'] = $sct_parts[1];
        $questiondata['scttitlelower'] = $string['thenthis'] . " " . mb_strtolower($sct_titles[$question['display_method']], 'UTF-8') . " " . $string['is'] . ":";

        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0' and $screen_pre_submitted == 1) {
          $this->set('unanswered', true);
        } else {
          $this->set('unanswered', false);
        }
        $marks = 1;
        break;
    }

    // Processing for each stem.
    $this->set('options', array());
    foreach ($question['options'] as $display_option) {
      $part_id++;
      $this->set('partid', $part_id);
      $tmp_part_id = $question['option_order'][$part_id-1] + 1;
      $this->set_opt($part_id, array(
          'optiontext' => $display_option['option_text'],
          'omedia' => $display_option['o_media'],
          'markscorrect' => $display_option['marks_correct'],
          'correct' => $display_option['correct'],
          'optionno' => 'q' . $question_no . '_' . $tmp_part_id,
          'tmppartid' => $tmp_part_id
      ));
      $this->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '', -1, false, $part_id);

      switch ($question['q_type']) {
        case 'area':
          $default_ans  = '100,0,0,0,0,0';

          if (isset($user_answers[$current_screen][$q_id])) {
            $tmp_user_answer = $user_answers[$current_screen][$q_id];
          } else {
            $tmp_user_answer = $default_ans;
          }

          $answer_parts = explode(';', $tmp_user_answer);
          if (isset($answer_parts[1])) {
            $tmp_user_answer = substr($answer_parts[1], 0, -2);
            $full_user_ans = $user_answers[$current_screen][$q_id];
          } else {
            $tmp_user_answer = '';
            $full_user_ans = $default_ans;
          }

          if (isset($user_answers[$current_screen][$q_id]) and $tmp_user_answer == $default_ans and  $screen_pre_submitted == 1) {
            $this->set('unanswered', true);
          }

          $questiondata['mediawidth'] += 2;
          $questiondata['mediaheight'] += 27;
          $questiondata['areadisplay'] = $display_option['correct'];
          $questiondata['areauseranswer'] = $tmp_user_answer;
          $questiondata['areafulluseranswer'] = $full_user_ans;
          $marks += $display_option['marks_correct'];
          break;
        case 'dichotomous':
          if (isset($user_answers[$current_screen][$q_id])) {
            $tmp_user_answer = substr($user_answers[$current_screen][$q_id], $question['option_order'][$part_id-1], 1);
          } else {
            $tmp_user_answer = '';
          }
          if (($question['display_method'] == 'TF_NegativeAbstain') or ($question['display_method'] == "YN_NegativeAbstain")) {
            $abstain = true;
          } else {
            $abstain = false;
          }

          $questiondata['option'][$part_id]['unanswered'] = false;
          if ($tmp_user_answer == 'u' and $screen_pre_submitted == 1) {
            $this->set('unanswered', true);
            $questiondata['option'][$part_id]['unanswered'] = true;
          }
          $questiondata['option'][$part_id]['dichotomouspartid'] = $part_id;
          $questiondata['option'][$part_id]['dichotomoususeranswer'] = $tmp_user_answer;
          $questiondata['option'][$part_id]['dichotomousabstain'] = $abstain;
          if ($display_option['o_media'] != '') {
            $questiondata['option'][$part_id]['displayoptionmedia'] = true;
          }
          $marks += $display_option['marks_correct'];
          break;
        case 'enhancedcalc':
          // no options for enhanced calc now stored in settings

          $extra = array(
            'num_on_screen' => $question_no,
            'current_question' => $question,
          );

          $question['object']->render_paper($extra);
          $question['object']->load_all_user_answers($user_answers);

          $marks += $question['object']->calculate_question_mark();

          break;
        case 'likert':
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
            $this->set('unanswered', true);
          } else {
            $this->set('unanswered', false);
          }
          $scale_size = substr_count($question['display_method'],'|');
          $questiondata['likertscaledisplay'] = false;
          $questiondata['likertoptionid'] = $question_no . "_" . $part_id;
          if ($likert_display[$scale_size] == 'true') {
            $questiondata['likertscaledisplay'] = true;
            $questiondata['likertscalena'] = false;
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'n/a') {
              $questiondata['likertscalena'] = true;
            }
          }
          for ($i=1; $i<=$scale_size; $i++) {
            $optionID = 'q' . $question_no . '_' . $part_id;
            if (isset($user_answers[$current_screen][$q_id]) and $i == $user_answers[$current_screen][$q_id]) {
              $questiondata['likertscale'][$i] = true;
            } else {
              $questiondata['likertscale'][$i] = false;
            }
          }
          break;
        case 'mcq':
          $this->set_option($part_id, $useranswerid, $user_dismissid, $marks, $display_option['marks_incorrect']);
          break;
        case 'mrq':
          $questiondata['option'][$part_id]['mrq_correct'] = $mrq_correct;
          $questiondata['option'][$part_id]['optiontextdisplay'] = false;
          if ($display_option['option_text'] != '') {
            $questiondata['option'][$part_id]['optiontextdisplay'] = true;
          }
          if ($display_option['o_media'] != '') {
            $questiondata['option'][$part_id]['displayoptionmedia'] = true;
          }
          if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1) == 'y') {
            $questiondata['option'][$part_id]['selected'] = true;
          } else {
            $questiondata['option'][$part_id]['selected'] = false;
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$part_id-1,1) == '1') {
            $questiondata['option'][$part_id]['inact'] = true;
          } else {
            $questiondata['option'][$part_id]['inact'] = false;
          }
          if ($question['score_method'] == 'Mark per Option') {
            if ($display_option['correct'] == 'y') $marks += $display_option['marks_correct'];  // Mark for correct options only
          } elseif ($question['score_method'] == 'Mark per Question') {
            if ($part_id == 1) $marks += $display_option['marks_correct'];
          } else {
            $marks += $display_option['marks_correct'];  // Mark for each and every item
          }
          break;
        case 'extmatch':
        case 'matrix':
          $matching_options[] = $display_option['option_text'];
            break;
        case 'rank':
          if (isset($user_answers[$current_screen][$q_id])) {
            $rank_answers = explode(',', $user_answers[$current_screen][$q_id]);
          } else {
            $rank_answers = '';
          }
          $total_rank_no = 0;
          $require_na = false;
          for ($i=0; $i<$option_no; $i++) {
            if ($question['options'][$i]['correct'] != 0 or $paper_type == '3') {
              $total_rank_no++;
            }
            if ($question['options'][$i]['correct'] == 0) $require_na = true;
          }
          $tmp_user_answers = 0;

          if ($rank_answers != '') {
            for ($i=0; $i<count($rank_answers); $i++) {
              if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0 and $rank_answers[$i] != 'u') {
                $tmp_user_answers++;
              }
            }
          }

          if ($question['score_method'] == 'Mark per Option') {
            $answers_needed = $option_no;
          } else {
            $answers_needed = $total_rank_no;
          }
          $questiondata['option'][$part_id]['unans'] = false;
          if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == 'u' and $screen_pre_submitted == 1 and $tmp_user_answers < $answers_needed) {
            $questiondata['option'][$part_id]['unans'] = true;
            $this->set('unanswered', true);
          }
          $questiondata['option'][$part_id]['na'] = false;
          if ($require_na) {
            $questiondata['option'][$part_id]['na'] = true;
            if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == '0') {
              $questiondata['option'][$part_id]['selected'] = true;
            } else {
              $questiondata['option'][$part_id]['selected'] = false;
            }
          }
          $questiondata['option'][$part_id]['totalrank'] = $total_rank_no;
          for ($i=1; $i<=$total_rank_no; $i++) {
            if (isset($rank_answers[$tmp_part_id - 1]) and $i == $rank_answers[$tmp_part_id - 1]) {
              $questiondata['option'][$part_id]['selected'] = true;
            } else {
              $questiondata['option'][$part_id]['selected'] = false;
            }
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
            $questiondata['option'][$part_id]['inact'] = true;
          } else {
            $questiondata['option'][$part_id]['inact'] = false;
          }
          if ($display_option['correct'] != 0) {
            $marks += $display_option['marks_correct'];
          } elseif ($question['score_method'] == 'Mark per Option') {
            $marks += $display_option['marks_correct'];
          }
          break;
        case 'sct':
          $tmp_part_id = $question['option_order'][$part_id-1] + 1;
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            $questiondata['option'][$part_id]['selected'] = true;
          } else {
            $questiondata['option'][$part_id]['selected'] = false;
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
            $questiondata['option'][$part_id]['inact'] = true;
          } else {
            $questiondata['option'][$part_id]['inact'] = false;
          }
          $questiondata['option'][$part_id]['optiontextdisplay'] = false;
          if ($display_option['option_text'] != '') {
            $questiondata['option'][$part_id]['optiontextdisplay'] = true;
          }
          break;
        case 'true_false':
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
            $this->set('unanswered', true);
          } else {
            $this->set('unanswered', false);
          }

          $questiondata['trueselected'] = false;
          $questiondata['falseselected'] = false;
          $questiondata['abstainselected'] = false;
          if ($question['display_method'] == 'dropdown') {
            $this->set('displaymethod', 'dropdown');
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 't') {
              $questiondata['trueselected'] = true;
            }
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'f') {
              $questiondata['falseselected'] = true;
            }
          } else {
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 't') {
              $questiondata['trueselected'] = true;
            }
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'f') {
              $questiondata['falseselected'] = false;
            }

            if ($question['display_method'] == 'horizontal') {
              $this->set('displaymethod', 'horizontal');
            } elseif ($question['display_method'] == 'vertical') {
              $this->set('displaymethod', 'vertical');
            }
            $this->set('negativemarking', $neg_marking);
            if ($neg_marking) {
              if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
                $questiondata['abstainselected'] = true;
              }
            }
          }
          $marks = $display_option['marks_correct'];

          break;
        case 'blank':
          $ans = '';
          $blank_mark = array();
          $display_option['option_text'] = str_replace('&nbsp;',' ',$display_option['option_text']);
          $blank_details = preg_split("/\[blank|\[\/blank\]/", $display_option['option_text']);
          if (isset($user_answers[$current_screen][$q_id])) {
            $user_answers[$current_screen][$q_id] = str_replace('&nbsp;',' ',$user_answers[$current_screen][$q_id]);
          }

          if (isset($user_answers[$current_screen][$q_id])) {
            $blank_user_answers = json_decode($user_answers[$current_screen][$q_id]);
          } else {
            $blank_user_answers = array();
          }

          if ($question['display_method'] == 'textboxes') {
            $this->set('displaymethod', 'textboxes');
          }
          $count = 0;
          $itemcount = 1;
          for ($blank_count = 0; $blank_count < count($blank_details); $blank_count++) {
            if ($blank_details[$blank_count] === '') {
              continue;
            } else {
              $count++;
            }
            if (substr($blank_details[$blank_count], 0, 1) === ']') {
              $questiondata['option'][$count]['itemtype'] = 'blank';
              $questiondata['option'][$count]['itemcount'] = $itemcount;
              if ($question['display_method'] == 'textboxes') {
                $sizeresults = array();
                $not_used = preg_match("|size=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$sizeresults);
                if (isset($sizeresults[1]) and $sizeresults[1] != '') {
                  $blank_size[$blank_count] = $sizeresults[1];
                } else {
                  $blank_size[$blank_count] = 15;
                }
                $questiondata['option'][$count]['size'] = $blank_size[$blank_count];
                if ((isset($blank_user_answers[$itemcount - 1]) and $blank_user_answers[$itemcount - 1] == 'u') and (isset($screen_pre_submitted) and $screen_pre_submitted == 1)) {
                  $this->set('unanswered', true);
                  $questiondata['option'][$count]['unans'] = true;
                } else {
                  $questiondata['option'][$count]['unans'] = false;
                  if (isset($blank_user_answers[$itemcount - 1])) {
                    $ans = $blank_user_answers[$itemcount - 1];
                  }
                  $encoded_ans = htmlentities($ans, ENT_COMPAT | ENT_HTML5, Config::get_instance()->get('cfg_page_charset'), false);
                  $questiondata['option'][$count]['encoded_ans'] = $encoded_ans;
                }
              } else {
                $answer_list = explode(',', ltrim($blank_details[$blank_count], ']'));
                // Ensure that the correct answer is filtered in the same way as the user's answer.
                $answer_list = param::clean_array($answer_list, param::TEXT);
                shuffle($answer_list);            // Shuffle the answers up.
                for ($i=0; $i<count($answer_list); $i++) {
                  if (isset($answer_list[$i]) and isset($blank_user_answers[$itemcount - 1]) and html_entity_decode(trim($answer_list[$i])) == html_entity_decode(trim($blank_user_answers[$itemcount - 1]))) {
                    $questiondata['option'][$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => true);
                  } else {
                    $questiondata['option'][$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => false);
                  }
                }
                if (isset($blank_user_answers[$itemcount- 1]) and $blank_user_answers[$itemcount- 1] == 'u' and $screen_pre_submitted == 1) {
                  $questiondata['option'][$count]['unans'] = true;
                  $this->set('unanswered', true);
                } else {
                  $questiondata['option'][$count]['unans'] = false;
                }
              }
              $results=array();
              $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
              if (isset($results[1]) and $results[1] != '') {
                $blank_mark[$blank_count] = $results[1];
              } else {
                $blank_mark[$blank_count] = $display_option['marks_correct'];
              }
              $itemcount++;
            } else {
              $questiondata['option'][$count]['itemtype'] = 'blurb';
              $questiondata['option'][$count]['itemvalue'] = $blank_details[$blank_count];
            }
          }
          if ($question['score_method'] == 'Mark per Option') {
            if (count($blank_mark) > 0) {
              foreach ($blank_mark as $individual_mark) $marks += $individual_mark;
            }
          } else {
            $marks = $display_option['marks_correct'];
          }
          break;
        case 'textbox':
          if (!in_array($question_no, $textboxes_seen)) {
            $textboxes_seen[] = $question_no;
            $settings = json_decode($question['settings'], true);
            $questiondata['editorcolumns'] = $settings['columns'];
            $questiondata['editorrows'] = $settings['rows'];
            if (!isset($settings['editor']) or $settings['editor'] == 'plain' or $settings['editor'] == 'mathjax') {
              $questiondata['editor'] = 'plain';
              if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
                $questiondata['useranswer'] = $user_answers[$current_screen][$q_id];
                $this->set('unanswered', true);
              } else {
                $ans = '';
                if (isset($user_answers[$current_screen][$q_id])) {
                  $ans = $user_answers[$current_screen][$q_id];
                }
                $questiondata['useranswer'] = $ans;
                if ($settings['editor'] == 'mathjax') {
                  $questiondata['editormathjax'] = true;
                  $questiondata['id'] = 'q' . $question_no;
                }
              }
            } else {
              include_once('wysiwyg_editor.inc');
              $ans = '';
              if (isset($user_answers[$current_screen][$q_id])) {
                $ans = $user_answers[$current_screen][$q_id];
              }

              $textbox_width  = ( 40 + ( $settings['columns'] * 8 ) );
              $textbox_height = ( $settings['rows'] * 28 );

              $background_colour = '';

              if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
                $background_colour = 'background-color:red;';
              }
              ?>
              <textarea class="mceEditor" id="q<?php echo $question_no ?>" name="q<?php echo $question_no ?>" style="<?php echo $background_colour; ?>width:<?php echo $textbox_width ?>px; height:<?php echo $textbox_height ?>px"><?php echo $ans ?></textarea><?php echo "\n"; ?>
              <?php
            }
            $marks += $display_option['marks_correct'];
          }
          break;
        case 'hotspot':
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and  $screen_pre_submitted == 1) {
            $this->set('unanswered', true);
          }
          $hotspot_no = substr_count($question['options'][0]['correct'],'|') + 1;
          $tmp_height = $question['q_media_height'] + 30;
          if ($tmp_height < (($hotspot_no * 36) + 25)) $tmp_height = (($hotspot_no * 36) + 25);

          $tmp_correct = str_replace("'", "\'", trim($question['options'][0]['correct']));
          $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
          $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);

          $questiondata['tmpcorrect'] = $tmp_correct;
          $questiondata['mediawidth'] += 300;
          $questiondata['mediaheight'] = $tmp_height-29;

          if (isset($user_answers[$current_screen][$q_id])) {
            $questiondata['useranswer'] = trim($user_answers[$current_screen][$q_id]);
            $questiondata['screensubmitted'] = $screen_pre_submitted;
          }

          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] != '') {
            $this->set('unanswered', false);
          } else {
            $this->set('unanswered', true);
          }
          if ($question['score_method'] == 'Mark per Question') {
            $marks = $display_option['marks_correct'];
          } else {
            $marks = (substr_count($question['options'][0]['correct'],'|') + 1) * $display_option['marks_correct'];
          }
          break;
        case 'labelling':
          $tmp_labels = 0;
          $max_col1 = 0;
          $max_col2 = 0;
          $tmp_first_split = explode(';', $question['options'][0]['correct']);
          $tmp_second_split = explode('|', $tmp_first_split[11]);
          $label_width = $tmp_first_split[5];
          $label_height = $tmp_first_split[6];
          $hyphen = false;
          foreach ($tmp_second_split as $ind_label) {
            $label_parts = explode('$', $ind_label);
            if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
              if (mb_strstr($label_parts[4], '-') !== false) $hyphen = true;
              $tmp_labels++;
              if ($label_parts[2] > 219) $marks += $display_option['marks_correct'];
              if ($label_parts[0] < 10) {
                $max_col1 = $label_parts[0];
              } else {
                $max_col2 = $label_parts[0];
              }
            }
          }
          $max_col2-=10;
          $max_label = max($max_col1, $max_col2);

          if ($question['score_method'] == 'Mark per Question') {
            $marks = $display_option['marks_correct'];
          }
          if (($label_width < 80 and $hyphen) or ($label_width < 104 and !$hyphen)) {    // Two columns
            $computed_height = round(($label_height + 6) * ceil($tmp_labels / 2)) + 10;
            $tmp_height = max($question['q_media_height'], $computed_height);
          } else {                    // Single column
            $computed_height = round(($label_height + 6) * $tmp_labels) + 10;
            $tmp_height = max($question['q_media_height'], $computed_height);
          }

          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0$' . $marks . ';' and  $screen_pre_submitted == 1) {
            $this->set('unanswered', true);
          }
          $tmp_correct = trim($question['options'][0]['correct']);
          $tmp_correct = str_replace("'", "&#039;", $tmp_correct);

          $questiondata['mediawidth'] += 220;
          $questiondata['mediaheight'] = $tmp_height;
          $questiondata['tmpcorrect'] = $tmp_correct;
          $questiondata['marks'] = $marks;

          if (isset($user_answers[$current_screen][$q_id])) {
            $questiondata['useranswer'] = trim($user_answers[$current_screen][$q_id]);
            $questiondata['markscorrect'] = $display_option['marks_correct'];
            $questiondata['marks_incorrect'] = $display_option['marks_incorrect'];
            $questiondata['score_method'] = $question['score_method'];
          }

          if (!isset($user_answers[$current_screen][$q_id]) or $user_answers[$current_screen][$q_id] == '') {
            $this->set('unanswered', true);
          } else {
            $this->set('unanswered', false);
          }
          break;
        case 'flash':
          // Question type is deprecated. Rogo only supports pre-existing flash questions.
          if ($question['scenario'] != '') {
          $this->set('displayscenario', true);
          }
        $marks += $display_option['marks_correct'];
        break;
      }                  // End switch
    }                    // End foreach loop

    if (in_array($question['q_type'], array('dichotomous', 'mrq', 'rank'))) {
      $answer = (isset($user_answers[$current_screen][$q_id])) ? $user_answers[$current_screen][$q_id] : '';
      if ($question['display_method'] == 'other') {
        $this->set('displaymethod', 'other');
        $part_id++;
        $questiondata['part_id'] = $part_id;
        if ($answer != '' and substr($answer,($part_id - 1),1) == 'y') {
          $questiondata['otherselected'] = true;
        } else {
          $questiondata['otherselected'] = false;
        }
        $questiondata['other'] = substr($answer, $part_id);
      } elseif ($question['q_type'] == 'mrq' and $display_option['marks_incorrect'] < 0) {
        $this->set('negativemarking', true);
        // Include an abstain option if negative marking is used.
        if ($answer != '' and $answer == 'a') {
          $questiondata['abstainselected'] = true;
        } else {
          $questiondata['abstainselected'] = false;
        }
      }
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      }
    } elseif ($question['q_type'] == 'extmatch') {
      $questiondata['extmatch'] = true;
      $matching_answers = explode('|', $question['options'][0]['correct']);
      $questiondata['displayextmatchmedia'] = false;
      if ($matching_media[0] != '') {
        $questiondata['displayextmatchmedia'] = true;
        //$mediadata = $this->set_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
        $questiondata = array_merge($questiondata, $mediadata);
      }

      array_unshift($matching_scenarios, '');
      $max_scenarios = max(count($matching_scenarios), count($matching_media));
      $scenario_no = 0;
      for ($part_id = 1; $part_id < $max_scenarios; $part_id++) {
        if ((isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id],'<img>')) != '')
          or (isset($matching_media[$part_id]) and $matching_media[$part_id] != '')) {
          $scenario_no++;
        }
      }

      $col1_no = ceil(count($matching_options) / 2);
      $questiondata['extmatchsplit'] = $col1_no-1;
      for ($i=0; $i<count($matching_options); $i++) {
        $questiondata['extmatchoptions'][$i] = $matching_options[$i];
      }
      $questiondata['extmathmatching_options'] = count($matching_options);
      for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
        if(isset($matching_answers[$part_id-1])) {
          $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
          $marks += (substr_count($matching_answers[$part_id-1],'$') + 1) * $display_option['marks_correct'];
        } else {
          $answer_no = 0;
        }
        $questiondata['extmatchstem'][$part_id-1]['answer_no'] = $answer_no;
        if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') {
          $questiondata['extmatchstem'][$part_id-1]['scenario'] = $matching_scenarios[$part_id];
        }
        $questiondata['extmatchstem'][$part_id-1]['display'] = false;
        if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
          $questiondata['extmatchstem'][$part_id-1]['display'] = true;
          //$questiondata['extmatchstem'][$part_id-1]['media'] = $this->set_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '');
        }
        if(isset($matching_answers[$part_id-1])) {
          $sub_answers = explode('$', $matching_answers[$part_id - 1]);
        } else {
          $sub_answers = array();
        }
        $list_size = 10;
        if (count($matching_options) < 10) $list_size = count($matching_options);
        if ($answer_no == 1) {
          if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == 'u' and $screen_pre_submitted == 1) {
            $questiondata['extmatchstem'][$part_id-1]['unanswered'] = true;
            $this->set('unanswered', true);
          } else {
            $questiondata['extmatchstem'][$part_id-1]['unanswered'] = false;
          }
        } else {
          $questiondata['extmatchstem'][$part_id-1]['listsize'] = $list_size;
          $questiondata['extmatchstem'][$part_id-1]['sub_answers'] = count($sub_answers);
          $questiondata['extmatchstem'][$part_id-1]['matching_options'] = count($matching_options);
          if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
            $questiondata['extmatchstem'][$part_id-1]['unanswered'] = true;
            $this->set('unanswered', true);
          } else {
            $questiondata['extmatchstem'][$part_id-1]['unanswered'] = false;
          }
        }

        $multi_answers = array();
        if (isset($matching_users_answers[$part_id - 1])) {
          $multi_answers = explode('$', $matching_users_answers[$part_id - 1]);
        }

        $tmp_option_no = 0;
        for ($option_no=0; $option_no<count($matching_options); $option_no++) {
          $tmp_answer_match = false;
          foreach ($multi_answers as $separate_tmp_answer) {
            if ($separate_tmp_answer == ($question['option_order'][$tmp_option_no]+1)) {
              $tmp_answer_match = true;
            }
          }
          if ($tmp_answer_match == true) {
            $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['selected'] = true;
          } else {
            $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['selected'] = false;
          }
          $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['value'] = $question['option_order'][$option_no]+1;
          $questiondata['extmatchstem'][$part_id-1]['matching_option'][$option_no]['option'] = chr($option_no+65) . '. ' . $matching_options[$option_no];
          $tmp_option_no++;
        }
      }
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      }
    } elseif ($question['q_type'] == 'matrix') {
      $questiondata['matrix'] = true;
      $part_id = 1;
      foreach ($matching_options as $single_option) {
        $questiondata['matrixoptions'][]['option'] = $single_option;
      }
      foreach ($matching_scenarios as $single_scenario) {
        if (trim($single_scenario) != '') {
          if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
            $questiondata['matrixscenarios'][$part_id-1]['unanswered'] = true;
            $this->set('unanswered', true);
          } else {
            $questiondata['matrixoptions'][$part_id-1]['unanswered'] = false;
          }
          $questiondata['matrixscenarios'][$part_id-1]['id'] = chr(64 + $part_id);
          $questiondata['matrixscenarios'][$part_id-1]['value'] = $single_scenario;
          $answer_no = 1;
          foreach ($matching_options as $single_option) {
            $tmp_part_id = $question['option_order'][$answer_no-1] + 1;
            $questiondata['matrixoptions'][$part_id-1]['tmp_part_id'] = $tmp_part_id;
            if (isset($matching_users_answers[$part_id-1]) and $matching_users_answers[$part_id-1] == $tmp_part_id) {
              $questiondata['matrixoptions'][$part_id-1]['selected'] = true;
            } else {
              $questiondata['matrixoptions'][$part_id-1]['selected'] = false;
            }
            $answer_no++;
          }
          $part_id++;
        }
      }
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      } else {
        $marks = $part_id - 1;
      }
    }

    // Write out the hidden field for the dismiss facility.
    if (in_array($question['q_type'], array('mrq', 'rank'))) {
      if (isset($user_dismiss[$current_screen][$q_id]) and $user_dismiss[$current_screen][$q_id] != '') {
        $questiondata['dismiss'] = $user_dismiss[$current_screen][$q_id];
      } else {
        $questiondata['dismiss'] = str_repeat('0', count($question['options'])) ;
      }
    }

    // Display possible marks for question (if not Survey)
    $this->set('finalmarks', $marks);
    if ($paper_type < 3) {
      if ($marks != 0) {
        if ($question['score_method'] == 'Bonus Mark') {
          $this->set('scoremethod', 'bonus');
          $plural = ($display_option['marks_correct'] == 1) ?  $string['mark'] : $string['marks'];
          $this->set('bonus', sprintf($string['bonusmark'], $display_option['marks_correct'], $plural));  // Used on ranking questions
        }
      }
    }

    $this->set('optionorder', implode(',', $question['option_order']));
    $used_questions[$q_id] = $q_id;

    // Plugin question use there own templating.
    if ($question['q_type'] != 'enhancedcalc') {
      $render->render($this, $string, 'paper/question.html');
    }
  }

  /**
   * Set question media
   *
   * @param string $filename media file name
   * @param integer $width media width
   * @param integer $height media height
   * @param string $border_color media border colour
   * @param integer $imageid media id
   * @param boolean $locked is media locked
   * @param string $part_id option part id
   */
  private function set_media($filename, $width, $height, $border_color, $imageid=-1, $locked=false, $part_id=null) {

    $mediadirectory = rogo_directory::get_directory('media');
    $fn_parts = pathinfo($filename);
    $mediaedit = false;
    $mediadelete = false;
    $mediatype = null;
    $mediaborder = true;
    $url = $mediadirectory->url($filename);

    // Is the file an image or something else (e.g. RasMol)?
    if (!array_key_exists('extension', $fn_parts)) {
      $mediatype = 1;
    } elseif (array_key_exists('extension', $fn_parts) and in_array(strtolower($fn_parts['extension']), array('gif', 'jpg', 'jpeg', 'png'))) {
      $mediatype = 2;
      if ($border_color == '') {
        $mediaborder = false;
      }
    } elseif (in_array($fn_parts['extension'], array('wav', 'wma', 'mid'))) {
      $mediatype = 3;
    } elseif (in_array($fn_parts['extension'], array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf'))) {
      $mediatype = 4;
    } elseif ($fn_parts['extension'] == 'flv') {
      $mediatype = 5;
      if ($width == 0 or $height == 0) {
        $width = 320;
        $height = 260;
      }
      $url = $mediadirectory->url($filename, false, false, true);
    } elseif ($fn_parts['extension'] == 'mp3') {     // Embed MP3 using HTML5 audio tag.
      $mediatype = 6;
      if (strpos(Url::fromGlobals(),'/edit/') !== false or strpos(Url::fromGlobals(),'/add/') !== false) {  // Display filename if add or edit script
        $mediaedit = true;
      }
    } elseif ($fn_parts['extension'] == 'avi' or $fn_parts['extension'] == 'wmv') {
      $mediatype = 7;
    }
    if ($imageid > -1 and !$locked) {
      $mediadelete = true;
    }
    // Set option media ot question media.
    if (!is_null($part_id)) {
      $option = $this->get_opt($part_id);
      $option['optionmedia'] = array(
          'mediaid' => $imageid,
          'mediafile', $filename,
          'mediawidth', $width,
          'mediaheight', $height,
          'mediaurl' => $url,
          'mediadelete' => $mediadelete,
          'mediaedit' => $mediaedit,
          'mediatype' => $mediatype,
          'mediaborder' => $mediaborder,
          'mediabordercolour' => $border_color
      );
      $this->set_opt($part_id, $option);
    } else {
      $this->set('mediaid', $imageid);
      $this->set('mediafile', $filename);
      $this->set('mediawidth', $width);
      $this->set('mediaheight', $height);
      $this->set('mediaurl', $url);
      $this->set('mediadelete', $mediadelete);
      $this->set('mediaedit', $mediaedit);
      $this->set('mediatype', $mediatype);
      $this->set('mediaborder', $mediaborder);
      $this->set('mediabordercolour', $border_color);
    }
  }
}