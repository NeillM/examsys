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
  protected $config;

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
   * Question settings
   * @var json
   */
  private $settings;
  
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
  private $qmedia;

  /**
   * Question media height
   * @var string
   */
  private $qmediaheight;

  /**
   * Question media width
   * @var string
   */
  private $qmediawidth;

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
   * @var integer
   */
  private $optionnumber;

  /**
   * Option id
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
  private $leadin;

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
   * Question b available marks
   * @var float
   */
  private $marks;
  
  /**
   * Order of question options
   * @var string 
   */
  private $optionorder;

  /**
   * Question object name
   * @var string 
   */
  private $object;

  /**
   * The current question
   * @var array 
   */
  private $question;

  /**
   * User answers
   * @var array 
   */
  private $useranswers;

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
    $this->set('unanswered', true);
    $this->set('labelcolour', '#C00000');
    $this->set('displaycalc', true);
    $this->set('displayprologue', false);
    $this->set('displaytheme', false);
    $this->set('displaymedia', false);
    $this->set('displayscenario', false);
    $this->set('displaynotes', false);
    $this->set('displayleadin', false);
    $this->set('displaydefault', false);
    $this->set('negativemarking', false);
    $this->set('displaymethod', '');
    $this->set('displayoptionmedia', false);
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
   * @param integer $allowed_responses number of answers that can be provided to a question
   */
  abstract public function set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $allowed_responses = 1);

  /**
   * Abstract function to set question options
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  abstract public function set_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted);

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswerid id of option user selected
   * @param integer $user_dismissid id of option user dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  abstract public function set_additional_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted);

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
    if (!isset($attribute)) {
      return null;
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
   * @global type $language system language
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
    $this->set('optionnumber', $option_no);
    // Determine if negative marking is used.
    $neg_marking = false;
    if (isset($question['object']) and method_exists($question['object'], 'is_negative_marked')) {
      $neg_marking = $question['object']->is_negative_marked();
    } else {
      foreach ($question['options'] as $tmp_option) {
        if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
      }
    }
    $this->set('negativemarking', $neg_marking);

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

    if ($question['theme'] != '') {
      $this->set('theme', $question['theme']);
      $this->set('displaytheme', true);
    }

    $this->set('optionno', $option_no);
    $this->set('papertype', $paper_type);
    $this->set('assignednumber',  $question['assigned_number']);
    $this->set('scenario', $question['scenario']);
    $this->set('notes', $question['notes']);
    $this->set('qmedia', $question['q_media']);
    $this->set('qmediawidth', $question['q_media_width']);
    $this->set('qmediaheight', $question['q_media_height']);
    $this->set('leadin', $question['leadin']);
    $this->set('language', $language);
    $this->set('settings', $question['settings']);
    if (isset($question['object'])) {
      $this->set('object', $question['object']);
    }
    $this->set('question', $question);
    $this->set('useranswers', $user_answers);
    $this->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');

    // Set question header.
    $this->set_question_head();

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
    $this->set('scoremethod', $question['score_method']);
    switch ($question['q_type']) {
      case 'mrq':
        $mrq_correct = 0;
        if ($question['score_method'] == 'Mark per Question') {
          $mrq_correct = $option_no;
        } else {
          for ($i=0; $i<$option_no; $i++) {
            if ($question['options'][$i]['correct'] == 'y') $mrq_correct++;
          }
        }
        $this->set_question($screen_pre_submitted, $useranswerid, $user_dismissid, $mrq_correct);
        break;
      case 'sct':
        $this->set_question($screen_pre_submitted, $useranswerid, $user_dismissid);
        $marks = 1;
        break;
      default:
        $this->set_question($screen_pre_submitted, $useranswerid, $user_dismissid);
        break;
    }

    // Processing for each stem.
    $this->set('options', array());
    $this->set('marks', $marks);
    foreach ($question['options'] as $display_option) {
      $part_id++;
      $this->set('partid', $part_id);
      $tmp_part_id = $question['option_order'][$part_id-1] + 1;
      $this->set_opt($part_id, array(
          'optiontext' => $display_option['option_text'],
          'omedia' => $display_option['o_media'],
          'markscorrect' => $display_option['marks_correct'],
          'marksincorrect' => $display_option['marks_incorrect'],
          'correct' => $display_option['correct'],
          'optionno' => 'q' . $this->get('questionno') . '_' . $tmp_part_id,
          'tmppartid' => $tmp_part_id
      ));
      $this->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '', -1, false, $part_id);

      // Set question options.
      $this->set_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted);
    }

    $this->set('optionorder', implode(',', $question['option_order']));

    if ($question['q_type'] == 'matrix') {
      $part_id = 1;
      $this->set_additional_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted);
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      } else {
        $marks = $part_id - 1;
      }
    } else {
      $this->set_additional_option($part_id, $useranswerid, $user_dismissid, $screen_pre_submitted);
    }
    if (in_array($question['q_type'], array('mcq', 'mrq', 'dichotomous', 'rank', 'extmatch'))) {
      $marks = $this->get('marks');
      if ($question['score_method'] == 'Mark per Question') {
        $marks = $display_option['marks_correct'];
      }
    } else {
      $marks = $this->get('marks');
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
  protected function set_media($filename, $width, $height, $border_color, $imageid=-1, $locked=false, $part_id=null) {

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
          'mediafile' => $filename,
          'mediawidth'=> $width,
          'mediaheight'=> $height,
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