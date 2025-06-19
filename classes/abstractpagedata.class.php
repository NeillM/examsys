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
 * Abstract base class for page data classes.
 * This class provides common functionality for data classes that prepare data for templates.
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */
abstract class AbstractPageData
{
    /** @var array Language strings used for the page */
    protected $string;

    /** @var Config The configuration object */
    protected $config;

    /** @var mysqli The database connection */
    protected $db;

    /**
     * Constructor for AbstractPageData
     *
     * @param array $string Array of language strings
     */
    public function __construct(array $string)
    {
        $this->string = $string;
        $this->config = Config::get_instance();
        $this->db = $this->config->db;
    }

    /**
     * Get month options for date selectors
     *
     * @return array Array of month options
     */
    public function getMonthOptions(): array
    {
        $months = [];
        $month_keys = ['january', 'february', 'march', 'april', 'may', 'june',
                      'july', 'august', 'september', 'october', 'november', 'december'];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'value' => $i,
                'text' => $this->string[$month_keys[$i - 1]]
            ];
        }

        return $months;
    }

    /**
     * Prepare data for the header template
     *
     * @return array Data for the header template
     */
    public function prepareHeaderData(): array
    {
        return [
            'css' => $this->getCssFiles(),
            'metadata' => [],
            'mathjax' => $this->config->get_setting('core', 'cfg_mathjax_path'),
            'three' => $this->config->get_setting('core', 'cfg_three_path'),
            'editor' => $this->config->get_setting('core', 'cfg_editor_path'),
            'texteditor' => '',
            'scripts' => []
        ];
    }

    /**
     * Get CSS files for the page
     *
     * @return array Array of CSS file paths
     */
    abstract protected function getCssFiles(): array;

    /**
     * Get JavaScript files for the page
     *
     * @return array Array of JavaScript file paths
     */
    abstract protected function getScriptFiles(): array;
}
