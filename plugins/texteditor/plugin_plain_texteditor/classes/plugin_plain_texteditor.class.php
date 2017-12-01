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

namespace plugins\texteditor\plugin_plain_texteditor;
/**
* Text editor plugin helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * SMS import plugin.
 */
class plugin_plain_texteditor extends \plugins\plugins_texteditor {
  /**
   * Name of the plugin;
   * @var string
   */
  protected $plugin = 'plugin_plain_texteditor';

  /**
   * Lang pack strings.
   * @var string
   */
  private $strings;

  /**
   * Language pack component.
   * @var string
   */
  protected $langcomponent = 'plugins/texteditor/plugin_plain_texteditor/plugin_plain_texteditor';

  /**
    * Set the available land pack strings for the plugin
    */
  private function set_lang_strings() {
    $langpack = new \langpack();
    $this->strings = $langpack->get_all_strings($this->langcomponent);
  }

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->set_lang_strings();
  }

  /**
   * Get text editor javascript
   * @param array $configfile config file
   */
  public function get_javascript($configfile) {
    // Nothing to do.
  }

  /**
   * Get text editor textarea.
   * @param string $name editor name
   * @param string $id editor id
   * @param string $content editor content
   * @param string $type type of editor i.e. i.e. standard, simple, etc
   * @param string $styleoverwrite overwrite base styling
   */
  public function get_textarea($name, $id, $content, $type, $styleoverwrite = '') {
    // Reneder mathjax utils.
    $data['mathjax'] = false;
    if ($type == \plugins\plugins_texteditor::type_mathjax and $this->config->get_setting($this->plugin, 'supports_mathjax')) {
      $render = new \render($this->config);
      $render->render(array('id' => $id), null, 'mathjaxpreview.html');
      $data['mathjax'] = true;
    }
    // Render textarea.
    $render = new \render($this->config, $this->get_path() . DIRECTORY_SEPARATOR . 'templates');
    $data['id'] = $id;
    $data['name'] = $id;
    $data['content'] = $content;
    $data['style'] = $styleoverwrite;
    $render->render($data, $this->strings, 'plain_textarea.html');
  }

  /**
   * Prints trigger save js.
   */
  public function get_trigger_save() {
    // Nothing to do.
  }
}