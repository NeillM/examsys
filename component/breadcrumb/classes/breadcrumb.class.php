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

namespace component\breadcrumb;

use component\Component;
use render;

/**
 * Interface for all component classes.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Breadcrumb implements Component
{
    /** @var array The list of breadcrumbs (excluding the current page) */
    protected array $breadcrumbs = [];

    /** @var array The details for the current page. */
    protected array $currentpage;

    /**
     * Appends a breadcrumb
     *
     * @param string $description the description of the breadcrumb.
     * @param string $url The url to the page (optional)
     * @return void
     */
    public function addBreadcrumb(string $description, string $url = '')
    {
        $this->breadcrumbs[] = [
            'description' => $description,
            'url' => $url,
            'current' => false,
        ];
    }

    /**
     * The current page will be at the end of any breadcrumbs added.
     *
     * @param string $description The description for the breadcrumb.
     * @return void
     */
    public function addCurrentPage(string $description)
    {
        $this->currentpage = [
            'description' => $description,
            'url' => '',
            'current' => true,
        ];
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@breadcrumb/breadcrumb.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        $breadcrumbs = $this->breadcrumbs;
        if (isset($this->currentpage)) {
            $breadcrumbs[] = $this->currentpage;
        }
        return [
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        $breadcrumb = new self();
        $breadcrumb->addBreadcrumb('Home', '.');
        $breadcrumb->addBreadcrumb('A11CRH');
        $breadcrumb->addBreadcrumb('Formative Papers', '.');
        $breadcrumb->addCurrentPage('My formative paper');
        return $breadcrumb;
    }

    #[\Override]
    public function getJavascriptForHead(): array
    {
        return [];
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        return [];
    }

    #[\Override]
    public function getStrings(): array
    {
        static $string;

        if (!isset($string)) {
            $langpack = new \langpack();
            $string = $langpack->get_all_strings('component/breadcrumb/breadcrumb');
        }

        return $string;
    }
}
