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

namespace testing\behat\steps\common;

use Exception;
use plugin_manager;
use testing\behat\helpers\database\state;

/**
 * Steps for manipulating the plugins in ExamSys
 *
 * @copyright Copyright (c) 2026 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait plugins
{
    /**
     * Enables a plugin.
     *
     * @Given the :plugin plugin is enabled
     *
     * @param string $plugin The name of the plugin
     * @return void
     */
    public function thePluginIsEnabled(string $plugin): void
    {
        $pluginslist = plugin_manager::listplugins();
        if (!isset($pluginslist[$plugin])) {
            throw new Exception("'$plugin' is not a valid plugin");
        }
        $pluginns = $pluginslist[$plugin];

        /** @var \plugins\plugins $plugin */
        $plugin = new $pluginns(state::get_db());
        $plugin->enable_plugin();
    }
}
