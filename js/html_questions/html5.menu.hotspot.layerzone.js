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
 * A special menu group that is as wide as the layer menu in hotspot questions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * A special menu group that is as wide as the layer menu in hotspot questions.
 *
 * @param {String} id The unique identifier for the menu item.
 * @returns {ROGO.html5.menu_group}
 */
ROGO.html5.menu_hotspot_layerzone = function() {
  // Extend the hotspot prototype.
  ROGO.html5.menu_group.call(this, '');
  /**
   * @see ROGO.html5.menu_item.class
   * @private
   */
  this.layerzoneclass = 'layerzone';
};

/**
 * Extend the menu_item prototype.
 * @type Object
 */
ROGO.html5.menu_hotspot_layerzone.prototype = Object.create(ROGO.html5.menu_group.prototype);

/**
 * Constructor.
 *
 * @returns {HTMLElement}
 */
ROGO.html5.menu_hotspot_layerzone.prototype.create = function() {
  var layerzone = document.createElement('div');
  layerzone.className = this.layerzoneclass;
  $(layerzone).append(ROGO.html5.menu_group.prototype.create.call(this));
  return layerzone;
};
