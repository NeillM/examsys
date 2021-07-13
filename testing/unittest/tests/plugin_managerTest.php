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

use testing\unittest\unittestdatabase;

/**
 * Test plugin_manager class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class plugin_managertest extends unittestdatabase
{

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('config', 'core');
        $datagenerator->change_setting(array('component' => 'plugin_plain_texteditor', 'setting' => 'installed', 'value' => 0));
    }

    /**
     * Test listing enabled plugin for type
     * @group plugins
     */
    public function test_get_plugin_type_enabled()
    {
        $this->assertEquals(array('plugin_tinymce_texteditor'), plugin_manager::get_plugin_type_enabled('plugin_texteditor'));
    }

    /**
     * Test plugin installed
     * @group plugins
     */
    public function test_plugin_installed()
    {
        $this->assertTrue(plugin_manager::plugin_installed('plugin_tinymce_texteditor'));
        $this->assertFalse(plugin_manager::plugin_installed('plugin_plain_texteditor'));
        $this->assertFalse(plugin_manager::plugin_installed('unknowntestplugin'));
    }
}
