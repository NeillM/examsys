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
 * Settings for buttons on the log-in form.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package Authentication
 */
class displaystdformobjbutton extends stdClass {
  public $pretext;
  public $posttext;
  public $type;
  public $name;
  public $value;
  public $style;

  function __construct() {
    $this->pretext = '';
    $this->posttext = '';
    $this->type = '';
    $this->name = '';
    $this->value = '';
    $this->style = '';
  }
}
