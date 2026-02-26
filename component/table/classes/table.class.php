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

namespace component\table;

use component\Component;
use render;

/**
 * A component for basic tables.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Table implements Component
{
    /** @var array[] Contains the data for rows. */
    protected array $rows = [];

    /** @var int The number of columns the table has. */
    protected int $columns = 0;

    /**
     * The constructor.
     *
     * @param array $headings The localised names of the columns.
     * @param string $caption The caption for the table (optional)
     * @param array $classes The classes for the table (optional)
     * @param bool $highlight Flags if we should highlight the row a user is hovering over (default: true)
     * @param string $id The id of the table (optional)
     */
    public function __construct(
        protected array $headings,
        protected string $caption = '',
        protected array $classes = [],
        protected bool $highlight = true,
        protected string $id = '',
    ) {
        $this->columns = count($this->headings);
    }

    /**
     * Adds a new row to the table.
     *
     * @param array $row A row of data.
     * @return void
     * @throws \coding_exception
     */
    public function addRow(array $row): void
    {
        $columns = count($row);

        if ($columns !== $this->columns) {
            // An invalid row was passed.
            $message = "The table has {$this->columns} columns, but the row contains {$columns} columns.";
            throw new \coding_exception($message);
        }

        $this->rows[] = $row;
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@table/table.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return [
            'caption' => $this->caption,
            'classes' => $this->classes,
            'headings' => $this->headings,
            'highlight' => $this->highlight,
            'id' => $this->id,
            'rows' => $this->rows,
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        $table = new self(
            headings: ['Column 1', 'Column 2', 'Column 3'],
            caption: 'An example table',
        );
        $table->addRow(['Row 1 Column 1', 'Row 1 Column 2', 'Row 1 Column 3']);
        $table->addRow(['Row 2 Column 1', 'Row 2 Column 2', 'Row 2 Column 3']);
        $table->addRow(['Row 3 Column 1', 'Row 3 Column 2', 'Row 3 Column 3']);
        return $table;
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
