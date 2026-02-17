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

namespace component\tabs;

use component\Component;
use render;

/**
 * Used to display the list of tabs.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class TabList implements Component
{
    /** @var string Display the tabs horizontally. */
    public const ORIENTATION_HORIZONTAL = 'horizontal';

    /** @var string Display the tabs vertically. */
    public const ORIENTATION_VERTICAL = 'vertical';

    /**
     * The constructor.
     *
     * @param string $id Unique id for the tab list.
     * @param string $name The display name for the tab list.
     * @param array $tabs The tabs that belong to the list (optional) They may be added after construction.
     * @param string $orientation The orientation of the tabs (Default: ORIENTATION_HORIZONTAL)
     */
    public function __construct(
        protected string $id,
        protected string $name,
        protected array $tabs = [],
        protected string $orientation = self::ORIENTATION_HORIZONTAL,
    ) {
        // Validate that tabs are of the correct class.
        foreach ($this->tabs as $tab) {
            if (!($tab instanceof Tab)) {
                $message = 'All tabs must implement "' . Tab::class . '", an instance of "'
                    . get_class($tab) . '" was found instead';
                throw new \coding_exception($message);
            }
        }
    }

    /**
     * Adds a new tab to the TabList.
     *
     * @param Tab $tab
     * @return void
     */
    public function addTab(Tab $tab): void
    {
        $this->tabs[] = $tab;
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@tabs/tab_list.html';
    }

    /**
     * Get a list of tabs to use as examples.
     *
     * @return Tab[]
     */
    protected static function exampleTabs(): array
    {
        $tabs =  [
            new Tab(
                id: 'tab-1',
                name: 'General',
                content: 'General content',
            ),
            new Tab(
                id: 'tab-2',
                name: 'Security',
                content: 'Security content',
            ),
            new Tab(
                id: 'tab-3',
                name: 'Feedback',
                content: 'Feedback content',
            ),
            new Tab(
                id: 'tab-4',
                name: 'Reviewers',
                content: 'Reviewers content',
            ),
            new Tab(
                id: 'tab-5',
                name: 'Exam Rubric',
                content: 'Exam Rubric content',
            ),
            new Tab(
                id: 'tab-6',
                name: 'Prologue',
                content: 'Prologue content',
            ),
            new Tab(
                id: 'tab-7',
                name: 'Postscript',
                content: 'Postscript content',
            ),
            new Tab(
                id: 'tab-8',
                name: 'Reference Material',
                content: 'The content for Reference Material',
            ),
            new Tab(
                id: 'tab-9',
                name: 'Changes',
                content: 'Changes content',
            ),
        ];

        $tabs[0]->setSelected();

        return $tabs;
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        $tabdata = [];
        foreach ($this->tabs as $tab) {
            $tabdata[] = $tab->getData($renderer);
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tabs' => $tabdata,
            'orientation' => $this->getValidOrientation(),
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'tab-list-1',
            name: 'Example of WAI compliant tabs',
            tabs: self::exampleTabs(),
            orientation: self::ORIENTATION_HORIZONTAL,
        );
    }

    #[\Override]
    public function getJavascriptForHead(): array
    {
        return [];
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        return [
            '/component/tabs/js/tabs.min.js',
        ];
    }

    #[\Override]
    public function getStrings(): array
    {
        return [];
    }

    /**
     * Ensures that we only output a valid orientation value.
     *
     * @return string
     */
    protected function getValidOrientation(): string
    {
        return match ($this->orientation) {
            self::ORIENTATION_VERTICAL,
            self::ORIENTATION_HORIZONTAL => $this->orientation,
            default => self::ORIENTATION_HORIZONTAL,
        };
    }
}
