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
 * The results of a search.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class SearchResult
{
    /** @var int The offset of the first result. */
    public $first;

    /** @var int The offset of the last result. */
    public $last;

    /** @var int The total number of pages of results. */
    public $pages;

    /** @var \mysqli_stmt An executed query with results. */
    public $query;

    /** @var int The total number of results. */
    public $total;
}
