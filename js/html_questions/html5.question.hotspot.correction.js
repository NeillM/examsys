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
 * Base html5 hotspot question in answer mode.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Constructor
 *
 * @returns {ROGO.html5.classes.hotspot_correction}
 */
ROGO.html5.classes.hotspot_correction = function() {
  // Extend the hotspot prototype.
  ROGO.html5.classes.hotspot.call(this);
  this.set_mode('correction');
  /**
   * The layerzone menu group.
   *
   * @type {ROGO.html5.menu_hotspot_layerzone}
   * @private
   */
  this.layerzone = new ROGO.html5.menu_hotspot_layerzone();
  /**
   * Stores the last coordinate for moving.
   * 
   * @type {Object}
   * @private
   */
  this.temp_move_coordinate = {
    x: 0,
    y: 0
  };
  /**
   * Stores the shape that should be highlighted.
   * 
   * @type {Object}
   */
  this.highlight = {
    layer: null,
    shape: null
  };
  /**
   * Stores the index of the last vertex to be selected.
   * 
   * @type {Number|Boolean}
   */
  this.temp_vertex = false;
  /**
   * The colour selector for the question.
   *
   * @type {ROGO.html5.hotspot_colourselector}
   */
  this.colourselect;
};

/**
 * Extend the hotspot prototype.
 * @type Object
 */
ROGO.html5.classes.hotspot_correction.prototype = Object.create(ROGO.html5.classes.hotspot.prototype);

/**
 * Builds the menu for correction mode questions.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.build_menu = function() {
  // Create the menu.
  this.layerzone.add(new ROGO.html5.menu_checkbox(this.menu_item_id_from_name('viewall'), ROGO.lang.get_string('viewall', 'html5')));
  this.layerzone.add(new ROGO.html5.menu_filler());
  var movegroup = new ROGO.html5.menu_group();
  movegroup.add(new ROGO.html5.menu_button('move', ROGO.lang.get_string('move', 'html5'), this.menu_item_id_from_name('move')));
  var shapegroup = new ROGO.html5.menu_group();
  shapegroup.add(new ROGO.html5.menu_button('ellipse', ROGO.lang.get_string('ellipse', 'html5'), this.menu_item_id_from_name('ellipse')));
  shapegroup.add(new ROGO.html5.menu_button('rectangle', ROGO.lang.get_string('rectangle', 'html5'), this.menu_item_id_from_name('rectangle')));
  shapegroup.add(new ROGO.html5.menu_button('polygon', ROGO.lang.get_string('polygon', 'html5'), this.menu_item_id_from_name('polygon')));
  var erasegroup = new ROGO.html5.menu_group();
  erasegroup.add(new ROGO.html5.menu_button('delete', ROGO.lang.get_string('delete', 'html5'), this.menu_item_id_from_name('delete')));

  this.editmenu.add_item(this.layerzone);
  this.editmenu.add_item(movegroup);
  this.editmenu.add_item(shapegroup);
  this.editmenu.add_item(erasegroup);
  this.editmenu.add_item(new ROGO.html5.menu_filler());
  var help = new ROGO.html5.menu_button('help', ROGO.lang.get_string('help', 'html5'), this.menu_item_id_from_name('help'));
  help.togglable = false;
  this.editmenu.add_item(help);
};

/**
 * Sets up the object for use.
 * @param {Element} parent
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.setup = function(parent) {
  ROGO.html5.classes.hotspot.prototype.setup.call(this, parent);
  this.build_menu();
  parent.appendChild(this.editmenu.create(this.identifier));
  var main = this.create_main();
  parent.appendChild(main);
  ROGO.html5.listener.hotspot.start_drag_listener(this.identifier);
  this.colourselect = new ROGO.html5.hotspot_colourselector(this.identifier);
  $('#' + this.identifier).append(this.colourselect.create());
  this.colourselect.position(0, 0);
  this.colourselect.hide();
  this.layer_names_editable = true;
};

/**
 * Create a layermenu item.
 *
 * @param {ROGO.html5.hotspot_layer} layer
 * @returns {HTMLElement}
 */
ROGO.html5.classes.hotspot_correction.prototype.create_layermenu_item = function(layer) {
  var menu_item = ROGO.html5.classes.hotspot.prototype.create_layermenu_item.call(this, layer),
    main_area = $('.mainarea', menu_item);

  var colour_selector = document.createElement('div'),
    item = $(colour_selector);
  item.addClass(this.html_classes.colourpicker);
  item.attr('role', 'button');
  item.attr('tabindex', -1);
  main_area.append(colour_selector);

  return menu_item;
};

/**
 * Tests if the question is in a drawing mode.
 *
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.in_draw_mode = function() {
  // There must be a menu mode active.
  if (this.editmenu.active_item === null) {
    return false;
  }
  switch (this.editmenu.active_item.get_name()) {
    case this.menu_item_id_from_name('ellipse'):
    case this.menu_item_id_from_name('rectangle'):
    case this.menu_item_id_from_name('polygon'):
      return true;
    default:
      return false;
  }
};

/**
 * Tests if the question is in a highlighting mode.
 *
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.in_highlight_mode = function() {
  // There must be a menu mode active.
  if (this.editmenu.active_item === null) {
    return false;
  }
  switch (this.editmenu.active_item.get_name()) {
    case this.menu_item_id_from_name('move'):
    case this.menu_item_id_from_name('delete'):
      return true;
    default:
      return false;
  }
};

/**
 * Defines if shape control points should be displayed.
 *
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.display_control_points = function() {
  return (this.editmenu.active_item === null);
};

// Menu action handlers.

/**
 * Turn delete mode on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.delete_on = function() {
  this.highlight.layer = null;
  this.highlight.shape = null;
  ROGO.log('Delete enabled', 'info');
};

/**
 * Turn delete mode off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.delete_off = function() {
  this.highlight.layer = null;
  this.highlight.shape = null;
  ROGO.log('Delete disabled', 'info');
};

/**
 * Turn ellipse drawing mode on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.ellipse_on = function() {
  ROGO.log('Ellipse creation on', 'info');
};

/**
 * Turn ellipse drawing mode off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.ellipse_off = function() {
  ROGO.log('Ellipse creation off', 'info');
};

/**
 * Turn move mode on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.move_on = function() {
  this.highlight.layer = null;
  this.highlight.shape = null;
  ROGO.log('Move enabled', 'info');
};

/**
 * Turn move drawing mode off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.move_off = function() {
  this.highlight.layer = null;
  this.highlight.shape = null;
  ROGO.log('Move disabled', 'info');
};

/**
 * Turn polygon drawing mode on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.polygon_on = function() {
  // Unset all the active shapes, so we do not accidentally modify an existing one.
  for (var i in this.layers) {
    this.layers[i].active_shape = null;
  }
  ROGO.log('Polygon creation on', 'info');
};

/**
 * Turn polygon drawing mode off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.polygon_off = function() {
  ROGO.log('Polygon creation off', 'info');
};

/**
 * Turn rectangle drawing mode on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.rectangle_on = function() {
  ROGO.log('Rectangle creation on', 'info');
};

/**
 * Turn rectangle drawing mode off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.rectangle_off = function() {
  ROGO.log('Rectangle creation off', 'info');
};

/**
 * Displays the colour selector for the active layer.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.activate_colourselect = function() {
  if (this.active_layer !== this.layers[this.active_layer].index) {
    // The active layer is incorrect.
    ROGO.log('Invalid active layer', 'error');
    return;
  }
  // Find the position of the colour select button that was clicked.
  var menu_selector = '#' + this.identifier + ' .' + this.html_classes.layermenu,
    menu = $(menu_selector)[0],
    active_selector = menu_selector + ' .' + this.html_classes.layermenuitem + '.' + this.html_classes.active,
    // The active layers menu.
    active = $(active_selector)[0],
    selector = menu_selector + ' .' + this.html_classes.colourpicker,
    // The colour picker button element.
    colourpicker = $(selector)[0],
    // The colour of the active layer.
    colour = 'rgb(' + this.layers[this.active_layer].colour + ')',
    // The top left of the colour select button.
    x = menu.offsetLeft + active.offsetLeft + colourpicker.offsetLeft,
    y = menu.offsetTop + active.offsetTop + colourpicker.offsetTop;
  this.colourselect.position(x, y);
  this.colourselect.set_colour(colour);
  this.colourselect.show();
  // Start the listeners for the colour picker.
  ROGO.html5.listener.hotspot.start_colour_select_listeners(this.colourselect.identifier);
};

/**
 * Changes the colour of the active layer.
 *
 * @param {String} colour The colour described as three comma separated numbers.
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.colour_selected = function(colour) {
  if (this.active_layer === this.layers[this.active_layer].index) {
    // Change the colour of the layer.
    this.layers[this.active_layer].colour = colour;
    // Change the colour of the layer menubar colour bar.
    var menu_selector = '#' + this.identifier + ' .' + this.html_classes.layermenu,
      active_selector = menu_selector + ' .' + this.html_classes.layermenuitem + '.' + this.html_classes.active,
      colour_swatch = active_selector + ' .' + this.html_classes.layercolourbar;
    $(colour_swatch).css('backgroundColor', 'rgb(' + colour + ')');
  }
  // Set the layer colour.
  this.deactivate_colourselect();
  this.update_page();
  this.redraw();
};

/**
 * Hides the colour selector and turns off it's listeners.
 *
 * @param {Boolean} change_focus True if the focus should be changed to the active colour select button.
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.deactivate_colourselect = function(change_focus) {
  change_focus = false | change_focus;
  this.colourselect.hide();
  ROGO.html5.listener.hotspot.stop_colour_select_listeners(this.colourselect.identifier);
  if (change_focus === true) {
    // Focus on the colour selector button of the active layer.
    $('#' + this.identifier + ' .' + this.html_classes.layermenuitem + '.' + this.html_classes.active + ' .' + this.html_classes.colourpicker).focus();
  }
};

/**
 * Turn the display of all layers on.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.viewall_on = function() {
  this.display_inactive = true;
  this.redraw();
};

/**
 * Turn the display of all layers off.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.viewall_off = function() {
  this.display_inactive = false;
  this.redraw();
};

// End of Menu action handlers.

/**
 * Sets the answer for the active layer and then increments the active layer.
 *
 * @param {Number} x The x coordinate of the answer.
 * @param {Number} y The y coordinate of the answer.
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.viewarea_clicked = function(x, y) {
  if (this.active_layer === this.layers[this.active_layer].index) {
    if (this.editmenu.active_item === null) {
      // No other modes are active.
      this.layers[this.active_layer].select_shape_at(x, y);
    } else if (this.editmenu.active_item.get_name() === this.menu_item_id_from_name('delete')) {
      // Delete mode.
      this.layers[this.active_layer].delete_shape_at(x, y);
      this.update_page();
    } else if (this.editmenu.active_item.get_name() === this.menu_item_id_from_name('polygon')) {
      // We need to add the point to the active shape, which needs to be a polygon.
      var closed = this.layers[this.active_layer].add_polygon_point(x, y);
      if (closed) {
        // Turn off polygon drawning mode.
        this.activate_edit_menu_item(this.editmenu.active_item.get_name());
        this.update_page();
      }
    }
    this.redraw();
  }
};

/**
 * Changes the active layer of the hotspot question.
 *
 * @param {Number} index The index of the new active layer.
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.set_active_layer = function(index) {
  this.deactivate_colourselect();
  ROGO.html5.classes.hotspot.prototype.set_active_layer.call(this, index);
};

/**
 * Activates a menu item.
 *
 * Classes that include this prototype and define an edit menu should define what each button does.
 *
 * @param {string} name The name of the menu item.
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.activate_edit_menu_item = function(name) {
  this.deactivate_colourselect();
  ROGO.html5.classes.hotspot.prototype.activate_edit_menu_item.call(this, name);
};

// Drag handlers.

/**
 * A drag has been initiated.
 *
 * @param {Number} x
 * @param {Number} y
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.drag_started = function(x, y) {
  if (this.editmenu.active_item === null && this.active_layer === this.layers[this.active_layer].index) {
    // No specific mode is active, so we allow the active shape to be modified by control points.
    this.temp_vertex = this.layers[this.active_layer].activate_control_point_at(x, y);
    if (this.temp_vertex !== false) {
      this.temp_move_coordinate.x = x;
      this.temp_move_coordinate.y = y;
      this.redraw();
      this.update_page();
      return true;
    }
    return false;
  } else if (this.active_layer === this.layers[this.active_layer].index) {
    switch (this.editmenu.active_item.get_name()) {
      case this.menu_item_id_from_name('rectangle'):
        this.layers[this.active_layer].add_shape('rectangle', x, y);
        break;
      case this.menu_item_id_from_name('ellipse'):
        this.layers[this.active_layer].add_shape('ellipse', x, y);
        break;
      case this.menu_item_id_from_name('move'):
        // If the user clicks on a shape, make it active.
        this.layers[this.active_layer].select_shape_at(x, y);
        this.temp_move_coordinate.x = x;
        this.temp_move_coordinate.y = y;
        break;
      default:
        return false;
    }
    this.redraw();
    this.update_page();
    return true;
  }
  // No active layer.
  return false;
};

/**
 * Process the latest coordinate of the drag.
 *
 * @param {Number} x
 * @param {Number} y
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.drag = function(x, y) {
  if (this.editmenu.active_item === null && this.active_layer === this.layers[this.active_layer].index) {
    // No specific mode is active, but there is an active layer.
    if (this.temp_vertex !== false) {
      if (this.layers[this.active_layer].change_point(x, y, this.temp_move_coordinate.x, this.temp_move_coordinate.y, this.temp_vertex)) {
        this.temp_move_coordinate.x = x;
        this.temp_move_coordinate.y = y;
        this.update_page();
        this.redraw();
        return true;
      } else {
        this.temp_vertex = false;
        this.temp_move_coordinate.x = null;
        this.temp_move_coordinate.y = null;
        return false;
      }
    }
    return false;
  } else if (this.active_layer === this.layers[this.active_layer].index) {
    // A specific menu mode is active.
    switch (this.editmenu.active_item.get_name()) {
      case this.menu_item_id_from_name('rectangle'):
      case this.menu_item_id_from_name('ellipse'):
        this.layers[this.active_layer].change_end_point(x, y);
        break;
      case this.menu_item_id_from_name('move'):
        // Do the move.
        var xdiff = x - this.temp_move_coordinate.x,
          ydiff = y - this.temp_move_coordinate.y;
        this.layers[this.active_layer].move_active_shape(xdiff, ydiff);
        // Store the new coordinates.
        this.temp_move_coordinate.x = x;
        this.temp_move_coordinate.y = y;
        break;
      default:
        return false;
    }
    this.redraw();
    return true;
  }
  // No active layer.
  return false;
};

/**
 * Process a drag ending.
 *
 * @param {Number} x
 * @param {Number} y
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.end_drag = function(x, y) {
  this.drag(x, y);
  if (this.editmenu.active_item !== null) {
    switch (this.editmenu.active_item.get_name()) {
      case this.menu_item_id_from_name('rectangle'):
      case this.menu_item_id_from_name('ellipse'):
        // Turn these modes off.
        this.activate_edit_menu_item(this.editmenu.active_item.get_name());
        break;
      default:
        // Do nothing.
        break;
    }
    this.update_page();
  }
};

// End of drag handlers.

/**
 * Process that the mouse is over a certain point.
 *
 * @param {Number} x
 * @param {Number} y
 * @returns {Boolean}
 */
ROGO.html5.classes.hotspot_correction.prototype.mouseover = function(x, y) {
  if (this.editmenu.active_item === null) {
    // No mode is active.
    return false;
  }
  if (this.active_layer === this.layers[this.active_layer].index) {
    switch (this.editmenu.active_item.get_name()) {
      case this.menu_item_id_from_name('polygon'):
        this.layers[this.active_layer].change_end_point(x, y);
        break;
      case this.menu_item_id_from_name('move'):
      case this.menu_item_id_from_name('delete'):
        this.highlight.layer = this.active_layer;
        this.highlight.shape = this.layers[this.active_layer].get_shape_at(x, y);
        // Needs a redraw.
        break;
      default:
        return false;
    }
    this.redraw();
    return true;
  }
  // No active layer.
  return false;
};

/**
 * Modify the Rogo page with the current state of the question.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_correction.prototype.update_page = function() {
  $('#points' + this.number).attr('value', this.toString());
};
