<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Installation script for inital setup of TouchStone.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  // If config.inc exists we just created config.inc and need to redirect to homepage
  $configfile = '/touchstone/config/config.inc';
  if (file_exists($configfile)) {
    header("Location: /touchstone/index.php");
    exit;
  }
  
  mkdir('/touchstone/new_config');
  
  if (!copy('/touchstone/install/config.tmpl','/touchstone/new_config/config.inc') ) die("Can't copy config file");
?>