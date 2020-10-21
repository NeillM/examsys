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
 * Render package for use during installation, before the database is setup.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 onwards The University of Nottingham
 */

/**
 * This class should override any methods in the render class that require a configured database
 * and remove the requirement.
 */
class render_install extends render
{
    /**
     * Render an arbitrary template file during installation before the database is installed.
     *
     * Config::get_setting must never be called within this method.
     *
     * @param array $data Data for the template
     * @param array $lang Language strings
     * @param string $template The template filename
     * @param string $additionaljs additional javascript required
     * @param string $additionalcss additional css required
     */
    public function render($data, $lang, $template, $additionaljs = '', $additionalcss = '')
    {
        $data = array('data' => $data, 'lang' => $lang, 'path' => $this->config->get('cfg_root_path'), 'charset' => $this->config->get('cfg_page_charset'),
        'additionaljs' => $additionaljs, 'additionalcss' => $additionalcss);
        echo $this->twig->render($template, $data);
    }
}
