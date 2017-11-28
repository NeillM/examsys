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
   * Constructor
   * @param mysqli $mysqli db connection
   */
  public function __construct($mysqli) {
    parent::__construct($mysqli);
  }

  /**
   * Get text editor javascript
   * @param array $configfile config file
   */
  public function get_javascript($configfile) {
    $render = new \render($this->config, $this->get_path() . DIRECTORY_SEPARATOR . 'templates');
    $tinmymcedata['file'] = 'tiny_' . $configfile;
    $render->render($tinmymcedata, null, 'tinymce3.html');
  }

  /**
   * Get maths equation editor display javascript
   */
  public function get_mee_javascript() {
    $render = new \render($this->config, $this->get_path() . DIRECTORY_SEPARATOR . 'templates');
    $tinmymcedata = array();
    $render->render($tinmymcedata, null, 'mee.html');
  }

  /**
   * Get text editor textarea.
   * @param string $name editor name
   * @param string $id editor id
   * @param string $content editor content
   * @param string $type type of editor i.e. standard, simple, etc
   * @raturn string
   */
  public function get_textarea($name, $id, $content, $type) {
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
    $render = new \render($this->config, $this->get_path() . DIRECTORY_SEPARATOR . 'templates');
    $tinmymcedata = array(
        'type' => $type,
        'id' => $id,
        'name' => $id,
        'content' => $content);
    $render->render($tinmymcedata, null, 'tinymce3_textarea.html');
  }
}