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

/**
 * Used to display the list of tabs using a vertical orientation.
 *
 * This component mostly exists to allow the multiple tab options to
 * be demonstrated in the Component Library.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class TabListVertical extends TabList
{
    /**
     * Constructor.
     *
     * @param string $id Unique id for the tab list.
     * @param string $name The display name for the tab list.
     * @param array $tabs The tabs that belong to the list (optional) They may be added after construction.
     */
    public function __construct(
        string $id,
        string $name,
        array $tabs = [],
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            tabs: $tabs,
            orientation: TabList::ORIENTATION_VERTICAL,
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'tab-list-1',
            name: 'Example of WAI compliant vertical tabs',
            tabs: self::exampleTabs(),
        );
    }
}
