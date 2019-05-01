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
 * Base html5 menu base class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Creates a menu.
 * @returns {ROGO.html5.hotspot_menu}
 */
ROGO.html5.menu = function() {
  /**
   * The current active menu item.
   *
   * @type ROGO.html5.menu_item
   */
  this.active_item = null;
  /**
   * An array of ROGO.html5.menu_item objects.
   *
   * @type Array
   */
  this.items = [];
};

/**
 * Adds a menu item.
 *
 * @param {ROGO.html5.menu_item} item
 * @returns {void}
 */
ROGO.html5.menu.prototype.add_item = function(item) {
  this.items.push(item);
};

/**
 * Creates the html for the edit menu.
 *
 * @param {String} questionid
 * @returns {HTMLElement}
 */
ROGO.html5.menu.prototype.create = function(questionid) {
  var item;
  var div = document.createElement('div');
  div.id = questionid + '-editmenu';
  div.className = 'editmenu';
  div.setAttribute('tabindex', 0);
  var menu = $(div);
  for (var i in this.items) {
    item = this.items[i].create();
    menu.append(item);
  }
  return div;
};

ROGO.html5.menu.prototype.set_active = function(name) {
  var item = this.find(name);
  if (item === false || !item.togglable) {
    return false;
  }
  if (item.toggle()) {
    if (this.active_item !== null) {
      // Make the exisiting active item inactive.
      this.active_item.set_inactive();
    }
    this.active_item = item;
    return true;
  } else {
    this.active_item = null;
    return false;
  }
};

ROGO.html5.menu.prototype.get_active = function() {
  return this.active_item;
}

/**
 * Find an item in the menu that has thie name.
 *
 * @param {String} name
 * @returns {Boolean}
 */
ROGO.html5.menu.prototype.find = function(name) {
  for (var i in this.items) {
    var result = this.items[i].find(name);
    if (result !== false) {
      // We found the item we want, stop searching.
      return result;
    }
  }
  // It wasn't here.
  return false;
};
