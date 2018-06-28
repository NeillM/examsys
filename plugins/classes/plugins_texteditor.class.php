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
* Text editor plugin
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

namespace plugins;

/**
 * Abstract mapping class.
 * 
 * This class should be extend by classes used define text editor plugins.
 */
abstract class plugins_texteditor extends \plugins\plugins {
    /**
     * Type of the editor.
     * @var string
     */
    const type_standard = 'standard';

    /**
     * Type of the editor.
     * @var string
     */
    const type_simple = 'simple';

    /**
     * Type of the editor.
     * @var string
     */
    const type_basic = 'basic';

    /**
     * Type of the editor.
     * @var string
     */
    const type_mathjax = 'mathjax';

    /**
     * Editor for general screens
     * @var string
     */
    const config = 'config';

    /**
     * Editor for staff help screens
     * @var string
     */
    const help_staff = 'config_help_staff';

    /**
     * Editor for student help screens
     * @var string
     */
    const help_student = 'config_help_student';

    /**
     * Editor for announements screens
     * @var string
     */
    const announcements = 'config_announcements';

    /**
     * Editor for paper properties screen
     * @var string
     */
    const properties = 'config_properties';

    /**
     * Editor for unanswered questions
     * @var string
     */
    const unanswered = 'config_unanswered';

    /**
     * Editor for answered questions
     * @var string
     */
    const answered = 'config_answered';

    /**
     * Editor for external email screens
     * @var string
     */
    const external = 'config_externals_email';

    /**
     * Editor for email screens
     * @var string
     */
    const email = 'config_email';

    /**
     * Editor for question editing screens
     * @var string
     */
    const question = 'config_question_editor';

    /**
     * Type of the plugin.
     * @var string
     */
    protected $plugin_type = 'texteditor';

    /**
     * Get text editor header file
     */
    abstract public function get_header_file();

    /**
     * Get text editor javascript.
     * @param array $data
     */
    abstract public function get_javascript_config($data = '');

    /**
     * Get text editor textarea.
     * @param string $name
     * @param string $id
     * @param string $content
     * @param string $type
     * @param string $styleoverwrite overwrite base styling
     */
    abstract public function get_textarea($name, $id, $content, $type, $styleoverwrite = '');

    /**
     * Leadin clean function check
     * @param $leadin
     * @return boolean
     */
    abstract public function clean_leadin($leadin);

    /**
     * Return editor specific type class
     * @param string $type generic type
     * @return string
     */
    abstract public function get_type($type);

    /**
     * Convert text stored in database to editor text
     * @param string $text text from database
     * @return string
     */
    abstract public function get_text_for_display($text);

    /**
     * Convert text in editor to store in database
     * @param string $text editor text
     * @return string
     */
    abstract public function prepare_text_for_save($text);

    /**
     * Get data to render in header.
     * @return array
     */
    abstract function get_header_data();

    /**
     * Enable this plugin
     * Only one module text editor plugin should be enabled at anyone time
     */
    public function enable_plugin() {
        $enabled = array($this->plugin);
        $this->config->set_setting('enabled_plugin', $enabled, \Config::JSON, 'plugin_texteditor');
    }

    /**
     * Disable this plugin
     *
     */
    public function disable_plugin() {
      // Nothing to do as only one module text editor plugin is enable at a time enable_plugin handles everything.
    }

    /**
     * Get plugin name
     * @return string
     */
    public function get_name() {
      return $this->plugin;
    }

    /**
     * Get text editor base
     */
    public function get_header() {
      $render = new \render($this->config, $this->get_header_path());
      $render->render($this->get_header_data(), null, $this->get_header_file());
    }

    /**
     * Get text editor base path
     * @return string
     */
    public function get_header_path() {
      return $this->get_path() . DIRECTORY_SEPARATOR . 'templates';
    }

    /**
     * Get the enabled text editor
     * @return object
     */
    public static function get_editor() {
      $texteditorplugin_name = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
      $texteditorpluginns = 'plugins\texteditor\\' . $texteditorplugin_name[0] . '\\' . $texteditorplugin_name[0];
      return new $texteditorpluginns();
    }

    /**
     * Get path to render templates
     * @return array
     */
    public function get_render_paths() {
      $renderpath = array($this->get_header_path());
      // Always get plain text editor.
      if ($this->get_name() !== 'plugin_plain_texteditor') {
        $renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'texteditor'
          . DIRECTORY_SEPARATOR . 'plugin_plain_texteditor'
          . DIRECTORY_SEPARATOR . 'templates';
      }
      return $renderpath;
    }

    /**
     * Get texteditor langpack
     * @return array
     */
    public function get_strings() {
      $langpack = new \langpack();
      $strings = $langpack->get_all_strings($this->langcomponent);
      // Always get plain text editor.
      if ($this->get_name() !== 'plugin_plain_texteditor') {
        $strings = array_merge($strings, $langpack->get_all_strings('plugins/texteditor/plugin_plain_texteditor/plugin_plain_texteditor'));
      }
      return $strings;
    }
}