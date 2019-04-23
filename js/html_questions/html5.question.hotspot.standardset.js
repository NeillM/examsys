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
 * Base html5 hotspot question in standard set mode.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Constructor
 *
 * @returns {ROGO.html5.classes.hotspot_script}
 */
ROGO.html5.classes.hotspot_standardset = function() {
  // Extend the hotspot prototype.
  ROGO.html5.classes.hotspot.call(this);
  this.set_mode('standardset');
  /**
   * @see ROGO.html5.classes.hotspot.inactive_coloured
   */
  this.inactive_coloured = true;
};

/**
 * Extend the hotspot prototype.
 * @type Object
 */
ROGO.html5.classes.hotspot_standardset.prototype = Object.create(ROGO.html5.classes.hotspot.prototype);

/**
 * Sets up the object for use.
 * @param {Element} parent
 * @returns {void}
 */
ROGO.html5.classes.hotspot_standardset.prototype.setup = function(parent) {
  ROGO.html5.classes.hotspot.prototype.setup.call(this, parent);
  this.build_menu();
  parent.appendChild(this.editmenu.create(this.identifier));
  var question = $(parent);
  this.setup_exclusions(question.data('exclusions'));
  parent.appendChild(this.create_main());
};

/**
 * Builds the menu for the standards setting mode questions.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_standardset.prototype.build_menu = function() {
  this.editmenu.add_item(new ROGO.html5.menu_checkbox(this.menu_item_id_from_name('hotspots'), ROGO.lang.get_string('allhotspots', 'html5')));
};

/**
 * Marks any layers as excluded.
 *
 * @param {String} exclusion_config
 * @returns {void}
 */
ROGO.html5.classes.hotspot_standardset.prototype.setup_exclusions = function(exclusion_config) {
  if(!exclusion_config) {
    return;
  }
  for (var i = 0; i < exclusion_config.length; i++) {
    // The exclusion_config string should bbe made up exlusively of 1 and 0.
    // The index of the character should match the index of a layer
    // Each characters represents a Boolean answer to: Is this layer excluded?
    var exclusion_state = exclusion_config.charAt(i);
    if (exclusion_state === '1') {
      // The 'i'th layer has been excluded.
      this.layers[i].excluded = true;
    }
  }
};

// Menu handlers.

/**
 * Turn the display of all layers on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_standardset.prototype.hotspots_on = function() {
  this.display_inactive = true;
  this.redraw();
};

/**
 * Turn the display of all layers off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_standardset.prototype.hotspots_off = function() {
  this.display_inactive = false;
  this.redraw();
};

// End of menu handlers
