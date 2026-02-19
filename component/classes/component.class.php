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

use render;

/**
 * Interface for all component classes.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
interface Component
{
    /**
     * The name of the default template to use rendering this component.
     *
     * @return string
     */
    public function defaultTemplate(): string;

    /**
     * Generates the date structure that will be used in templates.
     *
     * @param render $renderer
     * @return array
     */
    public function getData(render $renderer): array;

    /**
     * Creates an example of the component that can be used to demonstrate it in the library.
     *
     * @return Component
     */
    public static function getExample(): Component;

    /**
     * Gets the JavaScript that should be loaded in the head of the page
     *
     * This JavaScript must not require the contents of the page to already be loaded to work.
     *
     * The array will be a list of JavaScript files.
     *
     * @return array
     */
    public function getJavascriptForHead(): array;

    /**
     * Gets the JavaScript that should be loaded at the bottom of the page
     *
     * This JavaScript will be loaded after the contents of the page.
     *
     * The array will be a list of JavaScript files.
     *
     * @return array
     */
    public function getJavascriptForFooter(): array;

    /**
     * Gets any language strings the component requires.
     *
     * @return array
     */
    public function getStrings(): array;
}
