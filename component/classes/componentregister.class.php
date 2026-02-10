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

namespace component;

/**
 * Interface for the Register that is in collections
 *
 * The Register is used so that we do not scan directories
 * and files to get component class names.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
interface ComponentRegister
{
    /**
     * Gets the list of collections in ExamSys.
     *
     * @return string[]
     */
    public static function getComponentList(): array;
}
