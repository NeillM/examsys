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
 * Base object for questions
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */


require_once('question.interface.php');
define('QUESTION_ERROR', -1);

define('Q_MARKING_EXACT', 1);
define('Q_MARKING_FULL_TOL', 2);
define('Q_MARKING_PART_TOL', 3);
define('Q_MARKING_WRONG', 0);
define('Q_MARKING_UNMARKED', -1);


Class Question {

  public $id = -1;
  protected $excluded = '';
  protected $type = null;
  protected $theme = '';
  protected $scenario = '';
  protected $scenario_plain = '';
  protected $leadin = '';
  protected $leadin_plain = '';
  protected $notes = '';
  protected $correct_fback = '';
  protected $incorrect_fback = '';
  protected $score_method = 'Mark per Option';
  protected $display_method = '';
  protected $option_order = 'display order';
  protected $standards_setting = '';
  protected $bloom = null;
  protected $owner_id = null;
  protected $media = '';
  protected $media_width = 0;
  protected $media_height = 0;
  protected $teams = array();
  protected $checkout_time = null;
  protected $checkout_author_id = '';
  protected $created = null;
  protected $last_edited = null;
  protected $locked = null;
  protected $deleted = null;
  protected $status = 'Normal';
  protected $settings = '';
  public $options = array();

  //below are for support in question display etc

  public $error;
  public $useranswer = null;

  public $markinfo = null;
  public $qmark = null;

  public $q_media = '';
  public $q_media_height = '';
  public $q_media_width = '';


  public $std;

  function setsettings($settings) {
    $this->settings = $settings;
  }

  function load($array) {

    foreach ($array as $key => $value) {
     // if (isset($this->$key) ) {
      if (property_exists($this,$key) ) {
        $this->$key = $value;
      }
      if ($key == 'q_id') {
        $this->id = $value;
      } elseif($key=='user_answer') {
        $this->useranswer=$value;
      }

    }


    if (!is_array($this->options)) {
      //convert to objects!
    }
  }
}

?>
