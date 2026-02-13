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

namespace component;

/**
 * Class that provides utility functions for components.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Helper
{
    /**
     * Adds the language strings from components into the main strings
     *
     * When there is a clash with string names precedence will be:
     *
     * 1. The existing strings
     * 2. Components earlier in the arguments
     *
     * @param array $language The existing language strings
     * @param Component ...$components
     * @return array
     */
    public static function combineLang(array $language, Component ...$components): array
    {
        $strings = [];

        // Reverse the array because components with the same string names
        $components = array_reverse($components);
        foreach ($components as $component) {
            $strings[] = $component->getStrings();
        }
        // We add the existing language strings last so that none will be overwritten by components.
        $strings[] = $language;

        return array_merge(...$strings);
    }

    /**
     * Merges JavaScript from components with an existing array of JavaScript.
     *
     * @param array $existingjs The array of scripts that are currently defined
     * @param array $componentjs The scripts for the component
     * @return array Array of scripts with no duplicates
     */
    public static function combineJS(array $existingjs, array ...$componentjs): array
    {
        $output = $existingjs;

        foreach ($componentjs as $js) {
            $output = array_merge($existingjs, $js);
        }

        return array_unique($output);
    }

    /**
     * Gets the html that can be used to include the stylesheet in a page.
     *
     * @return string
     */
    public static function getCSSString(): string
    {
        $csspath = self::getCSSPath(true);
        return '<link rel="stylesheet" type="text/css" href="' . $csspath . '"/>';
    }

    /**
     * Gets the path to the CSS file for components.
     *
     * @param bool $includepath Includes the base path for ExamSys
     * @return string
     */
    public static function getCSSPath(bool $includepath = false): string
    {
        if ($includepath) {
            $rootpath = \Config::get_instance()->get('cfg_root_path');
        } else {
            $rootpath = '';
        }

        return $rootpath . '/component/css/component.css';
    }
}
