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
 * HTML5 hotspot question editing mode functions
*
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Constructor
 *
 * @returns {ROGO.html5.classes.hotspot_edit}
 */
ROGO.html5.classes.hotspot_edit = function() {
  // Extend the hotspot prototype.
  ROGO.html5.classes.hotspot_correction.call(this);
  this.set_mode('edit');
};

/**
 * Extend the hotspot prototype.
 * @type Object
 */
ROGO.html5.classes.hotspot_edit.prototype = Object.create(ROGO.html5.classes.hotspot_correction.prototype);

/**
 * Builds the menu for edit mode questions.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_edit.prototype.build_menu = function() {
  ROGO.html5.classes.hotspot_correction.prototype.build_menu.call(this);
  var add_layer = new ROGO.html5.menu_button('add_layer', ROGO.lang.get_string('addlayer', 'html5'), this.menu_item_id_from_name('add_layer'));
  add_layer.togglable = false;
  this.layerzone.add(add_layer);
  var remove_layer = new ROGO.html5.menu_button('remove_layer', ROGO.lang.get_string('removelayer', 'html5'), this.menu_item_id_from_name('remove_layer'));
  remove_layer.togglable = false;
  this.layerzone.add(remove_layer);
};

/**
 * Create a layermenu item.
 *
 * @param {ROGO.html5.hotspot_layer} layer
 * @returns {HTMLElement}
 */
ROGO.html5.classes.hotspot_edit.prototype.create_layermenu_item = function(layer) {
  var menu_item = ROGO.html5.classes.hotspot_correction.prototype.create_layermenu_item.call(this, layer);
  // Make the active layers text area editable.
  if (this.active_layer === layer.index) {
    $('.textarea', menu_item).attr('contenteditable', 'true');
  }
  return menu_item;
};

/**
 * Changess the active layer of the hotspot question.
 *
 * @param {Number} index The index of the new active layer.
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_edit.prototype.set_active_layer = function(index) {
  var current_active = this.active_layer,
    parent_success = ROGO.html5.classes.hotspot_correction.prototype.set_active_layer.call(this, index);
  // Swap the editable text area to the new active layer.
  if (parent_success) {
    $('#' + this.identifier + '-layer-' + this.layers[current_active].index + ' .textarea').removeAttr('contenteditable');
    $('#' + this.identifier + '-layer-' + this.layers[this.active_layer].index + ' .textarea').attr('contenteditable', 'true');
  }
  return parent_success;
};

// Menu action handlers.

/**
 * The add layer menu item was activated.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_edit.prototype.add_layer_off = function() {
  this.add_layer();
};

/**
 * The delete layer menu item was activated.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_edit.prototype.remove_layer_off = function() {
  if (confirm(ROGO.lang.get_string('removelayerconfirm', 'html5'))) {
    this.delete_layer();
  }
};

// End of Menu action handlers.
