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
    const type_mathjax= 'mathjax';
    
    /**
     * Type of the plugin.
     * @var string
     */
    protected $plugin_type = 'texteditor';
    /**
     * Get text editor header
     */
    abstract public function get_header();
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
     * Prints trigger save js.
    */
    abstract public function get_trigger_save();
    /**
     * Leadin clean function check
     * @param $leadin
     * @return boolean
     */
    abstract public function clean_leadin($leadin);
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
     * Only one module text editor plugin should be enabled at anyone time
     */
    public function disable_plugin() {
        $enabled = $this->config->get_setting('plugin_texteditor', 'enabled_plugin');
        if ($this->plugin == $enabled[0]) {
            $this->config->set_setting('enabled_plugin', array(), \Config::JSON, 'plugin_texteditor');
        }
        // Default to plain texteditor if non set.
        if (count(\plugin_manager::get_plugin_type_enabled('plugin_texteditor')) === 0) {
            $defaulttexteditorns = 'plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor';
            $defaulttexteditor = new $defaulttexteditorns($mysqli);
            $defaulttexteditor->enable_plugin();
        }
    }

    /**
     * Get plugin name
     * @return string
     */
    public function get_name() {
      return $this->plugin;
    }
}