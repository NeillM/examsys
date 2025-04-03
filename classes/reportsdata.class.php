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
 * Data class that handles the creation of data for the reports page template.
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */
class ReportsData
{
    /** @var array Language strings used for the page */
    private $string;

    /** @var Config The configuration object */
    private $config;

    /** @var mysqli The database connection */
    private $db;

    /**
     * Constructor for ReportsData
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
     * Prepare data for the header template
     *
     * @return array Data for the header template
     */
    public function prepareHeaderData(): array
    {
        return [
            'css' => ['/css/source/reports_form.css'],
            'metadata' => [],
            'mathjax' => $this->config->get_setting('core', 'cfg_mathjax_path'),
            'three' => $this->config->get_setting('core', 'cfg_three_path'),
            'editor' => $this->config->get_setting('core', 'cfg_editor_path'),
            'texteditor' => '',
            'scripts' => []
        ];
    }

    /**
     * Prepare data for the reports template
     *
     * @param PaperProperties $properties Paper properties
     * @param int $paperID Paper ID
     * @param string|null $module Module code
     * @param string|null $folder Folder name
     * @return array Data for the template
     */
    public function prepareTemplateData(
        PaperProperties $properties,
        int $paperID,
        $module = null,
        $folder = null
    ): array {
        // Get paper type name
        $paperType = $properties->get_paper_type();

        return [
            'paperID' => $paperID,
            'module' => $module,
            'folder' => $folder,
            'paper_title' => $properties->get_paper_title(),
            'paper_type' => $paperType
        ];
    }
}
