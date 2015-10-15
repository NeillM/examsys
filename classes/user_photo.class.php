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
 * Utility class to get information about Rogo the user photo directory.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package core
 */
class user_photo extends rogo_directory {
  public function location() {
    return $this->base_directory() . 'users' . DIRECTORY_SEPARATOR . 'photos' . DIRECTORY_SEPARATOR;
  }

  public function cachetime() {
    // Cache for 1 hour.
    return 3600;
  }
}
