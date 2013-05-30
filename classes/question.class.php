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

Class Question {
  
  public $id = -1;
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
  
}

?>
