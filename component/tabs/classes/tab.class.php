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
 * Used to display an individual tab.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Tab implements Component
{
    /** @var bool If the tab is selected. */
    protected bool $selected = false;

    /**
     * The constructor.
     *
     * The content of the tab may be included for simple tabs.
     *
     * @param string $id The unique identifier for the tab.
     * @param string $name The display name for the tab.
     * @param string|null $content The content for the tab (this is optional)
     */
    public function __construct(
        protected string $id,
        protected string $name,
        protected ?string $content = null,
    ) {
        // Intentionally empty.
    }

    /**
     * Set the selected status of the tab.
     *
     * Within a TablLst only a single tab should be selected.
     *
     * @param bool $selected
     * @return void
     */
    public function setSelected(bool $selected = true): void
    {
        $this->selected = $selected;
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@tabs/tab.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return [
            'content' => $this->content,
            'id' => $this->id,
            'name' => $this->name,
            'selected' => ($this->selected) ? 'true' : 'false',
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'tab1',
            name: 'My tab',
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
        return [];
    }

    #[\Override]
    public function getStrings(): array
    {
        return [];
    }
}
