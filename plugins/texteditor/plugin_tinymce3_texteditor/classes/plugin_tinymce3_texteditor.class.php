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

namespace plugins\texteditor\plugin_tinymce3_texteditor;
/**
* Text editor plugin helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * SMS import plugin.
 */
class plugin_tinymce3_texteditor extends \plugins\plugins_texteditor {
  /**
   * Name of the plugin;
   * @var string
   */
  protected $plugin = 'plugin_tinymce3_texteditor';

  /**
   * Language pack component.
   * @var string
   */
  public $langcomponent = 'plugins/texteditor/plugin_tinymce3_texteditor/plugin_tinymce3_texteditor';

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Get text editor base file
   * @return string
   */
  public function get_header_file() {
    return 'tinymce3.html';
  }

  /**
   * Get text editor javascript
   * @param array $configfile config file
   */
  public function get_javascript_config($configfile = '') {
    $render = new \render($this->config, $this->get_path() . DIRECTORY_SEPARATOR . 'templates');
    $tinmymcedata['file'] = 'tinymce3_' . $configfile;
    if ($tinmymcedata['file'] != 'tinymce3_') {
      $render->render($tinmymcedata, null, 'tinymce3_config.html');
    }
  }

  /**
   * Get text editor textarea.
   * @param string $name editor name
   * @param string $id editor id
   * @param string $content editor content
   * @param string $type type of editor i.e. standard, simple, etc
   * @param string $styleoverwrite overwrite base styling
   * @raturn string
   */
  public function get_textarea($name, $id, $content, $type, $styleoverwrite = '') {
    $type = $this->get_type($type);
    $render = new \render($this->config, $this->get_path() . DIRECTORY_SEPARATOR . 'templates');
    $tinmymcedata = array(
        'type' => $type,
        'id' => $id,
        'name' => $id,
        'content' => $content,
        'style' => $styleoverwrite);
    $render->render($tinmymcedata, null, 'tinymce3_admin_textarea.html');
  }

  /**
   * Return editor specific type class
   * @param string $type generic type
   * @return string
   */
  public function get_type($type) {
    switch ($type) {
      case \plugins\plugins_texteditor::type_simple:
        $type = 'editorSimple';
        break;
      case \plugins\plugins_texteditor::type_basic:
        $type = 'editorBasic';
        break;
      default:
        $type = 'editorStandard';
        break;
    }
    return $type;
  }

  /**
   * Replace <div class="mee"></div> tags with [tex][/tex]
   * before it is saved to the database
   *
   * @param string $text the text to be processed
   */
  public function prepare_text_for_save($text) {
    preg_match_all("#<div class=\"mee\">(.*?)\</div>#si",$text,$tex_matches);
    if (count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('<div class="mee">','</div>'),array('[tex]','[/tex]'),$m);
        $text = str_replace($m, $new, $text);
      }
    }
    preg_match_all("#<span class=\"mee\">(.*?)\</span>#si",$text,$tex_matches);
    if (count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('<span class="mee">','</span>'),array('[texi]','[/texi]'),$m);
        $text = str_replace($m, $new, $text);
      }
    }
    return $text;
  }

  /**
   * Replace [tex][/tex] tags with <div class="mee"></div>
   * before it is displayed in the editor
   *
   * @param string $text the text to be processed
   */
  public function get_text_for_display($text) {
    preg_match_all("#\[tex\](.*?)\[/tex\]#si",$text,$tex_matches);
    if (count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('[tex]','[/tex]'),array('<div class="mee">','</div>'),$m);
        $text = str_replace($m, $new, $text);
      }
    }
    preg_match_all("#\[texi\](.*?)\[/texi\]#si",$text,$tex_matches);
    if (count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('[texi]','[/texi]'),array('<span class="mee">','</span>'),$m);
        $text = str_replace($m, $new, $text);
      }
    } 
    return $text;
  }

  /**
   * Leadin clean function check
   * @param $leadin
   * @return boolean
   */
  public function clean_leadin($leadin) {
    if (strpos($leadin, 'class="mee"') === false AND strpos($leadin, 'class=mee') === false) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Get path to render templates
   * @return array
   */
  public function get_render_paths() {
    $renderpath = parent::get_render_paths();
    $renderpath[] = $this->get_header_path();
    return $renderpath;
  }
}