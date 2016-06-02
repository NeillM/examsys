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
* Plugin manager functionality
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Plugin manager class.
 * 
 * This class implements plugins within rogo.
 */
class plugin_manager {
    /**
     * Whitelist of plugin types supported by rogo.
     * @var plugintypewhitelist
     */
    private $plugintypewhitelist = array('mapping');
    /**
     * Constructor
     */
    public function __construct($mysqli) {
        $this->config = \Config::get_instance();
        $this->config->set_db_object($mysqli);
    }
    /**
     * List available plugins.
     * @return array available plugins (name => namespace)
     */
    public function listplugins() {
        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . '*';
        $folders = glob($directory, GLOB_ONLYDIR);
        $plugins = array();
        foreach ($folders as $folder) {
            $type = basename($folder);
            if (in_array($type, $this->plugintypewhitelist)) {
                $sub = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . '*';
                $subfolders = glob($sub, GLOB_ONLYDIR);
                foreach ($subfolders as $subfolder) {
                    $plugins[basename($subfolder)] = 'plugins\\' . $type . '\\' . basename($subfolder) . '\\' . basename($subfolder);
                }
            }
        }
        return $plugins;
    }
    /**
     * List available plugin types.
     * @return array available plugin types
     */
    public function listplugintypes() {
        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . '*';
        $folders = glob($directory, GLOB_ONLYDIR);
        $plugintype = array();
        foreach ($folders as $folder) {
            if (in_array(basename($folder), $this->plugintypewhitelist)) {
                $plugintype[] = 'plugin_' . basename($folder);
            }   
        }
        return $plugintype;
    }
    /**
     * Get all enabled plugins
     * @return array list of enabeld plugins
     */
    public function get_all_enabled_plugins() {
        $enabledplugins = array();
        $plugintypes = $this->listplugintypes();
        foreach ($plugintypes as $type) {
            $enabled = $this->get_plugin_type_enabled($type);
            $enabledplugins = array_merge($enabledplugins, $enabled);
        }
        return $enabledplugins;
    }
    /**
     * Get the enabeld plugin for this type:
     * @param string $type type of plugin
     * @return array list of plugins that are enabled of this type
     */
    public function get_plugin_type_enabled($type) {
        $enabled = $this->config->get_setting($type, 'enabled_plugin');
        if (!is_null($enabled)) {
            $enabledarray = json_decode($enabled);
            $newenabled = array();
            $changed = false;
            // Check existing enabled plugins and disable those not installed.
            foreach ($enabledarray as $e) {
                if ($this->plugin_installed($e)) {
                    $newenabled[] = $e;
                } else {
                    $changed = true;
                }
            }
            if ($changed) {
                $this->config->set_setting('enabled_plugin', json_encode($newenabled), 'plugin_' . $type);
            }
        } else {
            $newenabled = array();
        }
        return $newenabled;
    }
    /**
     * Is plugin installed.
     * @param string $plugin name of plugin
     * @return bool true is installed false otherise.
     */
    public function plugin_installed($plugin) {
        $installed = $this->config->get_setting($plugin, 'installed');
        if ($installed >= 1) {
            return true;
        } else {
            return false;
        }
    }
}
