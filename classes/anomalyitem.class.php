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
 * An anomaly item.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
class AnomalyItem
{
    /**
     * @var int $userid the user that had the anomaly
     */
    public int $userid;

    /**
     * @var string $forename the users forename
     */
    public string $forename;

    /**
     * @var string $surname the users surname
     */
    public string $surname;

    /**
     * @var ?int $sid the users student id
     */
    public ?int $sid;

    /**
     * @var string $type the type of anomaly
     */
    public string $type;

    /**
     * @var string $timestamp the time the anomaly took place
     */
    public string $timestamp;

    /**
     * @var array $details anomaly details
     */
    public array $details;

    /**
     * @var int $screen the paper screen number the anomaly took place on
     */
    public int $screen;
}
