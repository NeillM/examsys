<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Text editor plugin
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 */

namespace plugins;

use component\form\StaticHtml;

/**
 * Abstract mapping class.
 *
 * This class should be extend by classes used define text editor plugins.
 */
abstract class plugins_texteditor extends \plugins\plugins
{
    /**
     * Type of the editor.
     * @var string
     */
    public const TYPE_STANDARD_UANS = 'standarduans';

    /**
     * Type of the editor.
     * @var string
     */
    public const TYPE_STANDARD = 'standard';

    /**
     * Type of the editor.
     * @var string
     */
    public const TYPE_SIMPLE = 'simple';

    /**
     * Type of the editor.
     * @var string
     */
    public const TYPE_BASIC = 'basic';

    /**
     * Type of the editor.
     * @var string
     */
    public const TYPE_MATHJAX = 'mathjax';

    /**
     * Editor for general screens
     * @var string
     */
    public const CONFIG = 'config';

    /**
     * Editor for staff help screens
     * @var string
     */
    public const HELP_STAFF = 'config_help_staff';

    /**
     * Editor for student help screens
     * @var string
     */
    public const HELP_STUDENT = 'config_help_student';

    /**
     * Editor for announements screens
     * @var string
     */
    public const ANNOUNCEMENTS = 'config_announcements';

    /**
     * Editor for paper properties screen
     * @var string
     */
    public const PROPERTIES = 'config_properties';

    /**
     * Editor for unanswered questions
     * @var string
     */
    public const UNANSWERED = 'config_unanswered';

    /**
     * Editor for answered questions
     * @var string
     */
    public const ANSWERED = 'config_answered';

    /**
     * Editor for external email screens
     * @var string
     */
    public const EXTERNAL = 'config_externals_email';

    /**
     * Editor for email screens
     * @var string
     */
    public const EMAIL = 'config_email';

    /**
     * Editor for question editing screens
     * @var string
     */
    public const QUESTION = 'config_question_editor';

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
     * @param string $type The editor type.
     */
    abstract public function get_javascript_config($type = '');

    /**
     * Get text editor textarea.
     * @param string $name
     * @param string $id
     * @param string $content
     * @param string $type
     * @param string $styleoverwrite overwrite base styling
     *
     * @deprecated since 7.7.0 Use {@see self::getTextareaComponent()} instead.
     */
    abstract public function get_textarea($name, $id, $content, $type, $styleoverwrite = '');

    /**
     * Get text editor forms component for a textarea.
     *
     * Each text editor plugin should override this method.
     *
     * @since ExamSys 7.7.0
     * @param string $id The unique id of the text area.
     * @param string $label The label for the text area.
     * @param string $content The existing content of the text area.
     * @param string $type
     * @param array $classes add additional classes to the text area to style it
     * @param bool $disabled If the text area is disabled.
     * @return \component\form\FormElement[] An array of form elements used to output the text editor.
     */
    public function getTextareaComponent(
        string $id,
        string $label,
        string $content,
        string $type,
        array $classes = [],
        bool $disabled = false,
    ): array {
        $message = 'Please override getTextareaComponent() in the ' . $this->get_name() . ' text plugin.';
        trigger_error($message, \E_USER_DEPRECATED);
        return [
            new StaticHtml("<p>{$message}</p>"),
        ];
    }

    /**
     * Leadin clean function check
     * @param $leadin
     * @return boolean
     */
    public function clean_leadin($leadin)
    {
        if (!is_string($leadin)) {
            // We do not need to clean in this case.
            return false;
        }

        if (
            mb_strpos($leadin, 'class="mee"') === false and mb_strpos($leadin, 'class=mee') === false
            and preg_match_all('#\[tex\](.*?)\[/tex\]#si', $leadin) === 0
            and preg_match_all('#\[texi\](.*?)\[/texi\]#si', $leadin) === 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return editor specific type class
     * @param string $type generic type
     * @return string
     */
    abstract public function get_type($type);

    /**
     * Parse text before saving to the database
     * @param string $text the text to be processed
     * @return string
     */
    public function prepare_text_for_save($text)
    {
        if (!is_string($text)) {
            // No preparation needed.
            return $text;
        }

        // Replace deprecated mee maths
        preg_match_all('#<div class="mee">(.*?)\</div>#si', $text, $tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach ($tex_matches[0] as $m) {
                $new = str_replace(['<div class="mee">','</div>'], ['[tex]','[/tex]'], $m);
                $text = str_replace($m, $new, $text);
            }
        }
        preg_match_all('#<span class="mee">(.*?)\</span>#si', $text, $tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach ($tex_matches[0] as $m) {
                $new = str_replace(['<span class="mee">','</span>'], ['[texi]','[/texi]'], $m);
                $text = str_replace($m, $new, $text);
            }
        }
        return $text;
    }

    /**
     * Parse text before displaying in the editor
     * @param string $text the text to be processed
     * @return string
     */
    public function get_text_for_display($text)
    {
        if (!is_string($text)) {
            return $text;
        }

        // Support deprecated mee maths
        preg_match_all('#\[tex\](.*?)\[/tex\]#si', $text, $tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach ($tex_matches[0] as $m) {
                $new = str_replace(['[tex]','[/tex]'], ['<div class="mee">','</div>'], $m);
                $text = str_replace($m, $new, $text);
            }
        }
        preg_match_all('#\[texi\](.*?)\[/texi\]#si', $text, $tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach ($tex_matches[0] as $m) {
                $new = str_replace(['[texi]','[/texi]'], ['<span class="mee">','</span>'], $m);
                $text = str_replace($m, $new, $text);
            }
        }
        return $text;
    }

    /**
     * Get data to render in header.
     * @return array
     */
    public function get_header_data()
    {
        // Enable/Disable deprecated mee maths.
        $data['mee'] = $this->config->get_setting('core', 'paper_mee');
        return $data;
    }

    /**
     * Enable this plugin
     * Only one module text editor plugin should be enabled at anyone time
     */
    public function enable_plugin()
    {
        $enabled = [$this->plugin];
        $this->config->set_setting('enabled_plugin', $enabled, \Config::JSON, 'plugin_texteditor');
    }

    /**
     * Disable this plugin
     *
     */
    public function disable_plugin()
    {
        // Nothing to do as only one module text editor plugin is enable at a time enable_plugin handles everything.
    }

    /**
     * Get plugin name
     * @return string
     */
    public function get_name()
    {
        return $this->plugin;
    }

    /**
     * Render text editor header
     */
    public function display_header()
    {
        $render = new \render($this->config, $this->get_render_paths());
        $render->render($this->get_header_data(), null, $this->get_header_file());
    }

    /**
     * Get text editor base path
     * @return string
     */
    public function get_header_path()
    {
        return $this->get_path() . DIRECTORY_SEPARATOR . 'templates';
    }

    /**
     * Get the enabled text editor
     * @return object
     */
    public static function get_editor()
    {
        $texteditorplugin_name = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
        if (!empty($texteditorplugin_name)) {
            $texteditorpluginns = 'plugins\texteditor\\' . $texteditorplugin_name[0] . '\\' . $texteditorplugin_name[0];
        } else {
            // The text editor plugins are not currently configured, so we need to fall back to the plain text editor.
            $texteditorpluginns = 'plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor';
        }
        return new $texteditorpluginns();
    }

    /**
     * Get path to render templates
     * @return array
     */
    public function get_render_paths()
    {
        $renderpath = [$this->get_header_path()];
        // Always get plain text editor.
        $renderpath[] = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'texteditor';
        return $renderpath;
    }

    /**
     * Get texteditor langpack
     * @return array
     */
    public function get_strings()
    {
        $langpack = new \langpack();
        $strings = $langpack->get_all_strings($this->langcomponent);
        // Always get plain text editor.
        $strings = array_merge($strings, $langpack->get_all_strings('/texteditor'));
        return $strings;
    }
}
