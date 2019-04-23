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
 * A menu_group holds a set of menu_items
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Creates a menu item that acts as a button.
 *
 * @param {String} image The name of the css class containing the details of the items image.
 * @param {String} help A description of what the menu item does.
 * @param {String} id The unique identifier for the menu item.
 * @returns {ROGO.html5.menu_button}
 */
ROGO.html5.menu_button = function(image, help, id) {
  ROGO.html5.menu_item.call(this, image, help, id);
};

ROGO.html5.menu_button.prototype = Object.create(ROGO.html5.menu_item.prototype);

/**
 * Creates the html for the menu item.
 *
 * @returns {Element}
 */
ROGO.html5.menu_button.prototype.create = function() {
  var button = ROGO.html5.menu_item.prototype.create.call(this),
    item = $(button),
    attributes = {
      role: 'button',
      tabindex: -1
    };
  if (this.togglable) {
    attributes['aria-pressed'] = false;
  }
  item.attr(attributes);
  return button;
};
