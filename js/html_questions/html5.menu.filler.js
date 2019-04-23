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
 * A menu item that pushes following items to the right.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * A menu item that pushes following items to the right.
 *
 * @returns {ROGO.html5.menu_group}
 */
ROGO.html5.menu_filler = function() {
  // Extend the hotspot prototype.
  ROGO.html5.menu_item.call(this, '');
  /**
   * @see ROGO.html5.menu_item.class
   * @private
   */
  this.class = 'clear';
  /**
   * The menu items that are part of this group.
   *
   * @type {ROGO.html5.menu_item}
   * @private
   */
  this.items = [];
};

/**
 * Extend the menu_item prototype.
 * @type Object
 */
ROGO.html5.menu_filler.prototype = Object.create(ROGO.html5.menu_item.prototype);

/**
 * This will never be a valid return result.
 *
 * @param {String} name
 * @returns {Boolean}
 */
ROGO.html5.menu_filler.prototype.find = function(name) {
  return false;
};
