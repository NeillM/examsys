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
 * Base hotspot listener.
 *
 * Sets up listeners for interactions with html5 hotspot questions,
 * and routes the action to the question the user interacted with.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Setup the listensers for hotspot questions.
 *
 * @returns {void}
 */
ROGO.html5.listener.hotspot.init = function() {
  var base_class = '.html5.hotspot';
  var body = $('body');

  // Listen for clicks on the edit menu.
  var editmenuitem = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('editmenuitem', true);
  body.on('click', editmenuitem, ROGO.html5.listener.hotspot.editmenuitem_clicked);

  // Listen for edit menu checkbox state changes.
  var edittoggle = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('editmenu', true) + ' input[type=checkbox]';
  body.on('change', edittoggle, ROGO.html5.listener.hotspot.edititem_toggled);

  // Listen for clicks on layer menu items.
  var layermenuitem = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('layermenuitem', true);
  body.on('click', layermenuitem, ROGO.html5.listener.hotspot.layeritem_clicked);

  // Changes to the text of a name label.
  var textarea = layermenuitem + ' ' + ROGO.html5.classes.hotspot.get_class('layername', true) + '[contenteditable=true]';
  body.on('input', textarea, ROGO.html5.listener.hotspot.label_name_changed);

  // Clicks on the canvas.
  var viewarea = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('viewarea', true);
  body.on('click', viewarea, ROGO.html5.listener.hotspot.viewarea_clicked);

  // Keyboard listeners.
  var editmenu = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('editmenu', true);
  body.on('keydown', editmenu, ROGO.html5.listener.hotspot.menu_keyboard_handler);

  var layermenu = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('layermenu', true);
  body.on('keydown', layermenu, ROGO.html5.listener.hotspot.layer_keyboard_handler);
};

/**
 * Handle keyboard events for the colour selectors.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.colour_keyboard_handler = function(event) {
  var menu = $(event.target).closest('.' + ROGO.html5.hotspot_colourselector.prototype.html_classes.selector),
    selector = '#' + menu[0].id + ' [tabindex]';
  if (event.key === 'ArrowRight' || (event.key === 'Tab' && event.shiftKey === false)) {
    if (event.target.id === menu[0].id) {
      // The menu is the target.
      ROGO.html5.listener.hotspot.focus_first(event, selector);
    } else {
      // Next menu item.
      ROGO.html5.listener.hotspot.focus_next(event, selector, event.target);
    }
  } else if (event.key === 'ArrowLeft' || (event.key === 'Tab' && event.shiftKey === true)) {
    if (event.target.id !== menu[0].id) {
      // Previous Menu item.
      ROGO.html5.listener.hotspot.focus_previous(event, selector, event.target);
    }
  } else if (event.key === 'ArrowUp') {
    // Assume rows of 12 items.
    ROGO.html5.listener.hotspot.move_focus_by(event, selector, event.target, -12);
  } else if (event.key === 'ArrowDown') {
    // Assume rows of 12 items.
    ROGO.html5.listener.hotspot.move_focus_by(event, selector, event.target, 12);
  } else if (event.key === ' ' || event.key === 'Enter') {
    event.preventDefault();
    event.stopPropagation();
    // Turn the event into a click.
    $(event.target).click();
  }
};

/**
 * Handles keyborad presses for edit menus.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.menu_keyboard_handler = function(event) {
  var menu = $(event.target).closest(ROGO.html5.classes.hotspot.get_class('editmenu', true)),
    selector = '#' + menu[0].id + ' [tabindex]';
  if (event.key === 'ArrowRight' || (event.key === 'Tab' && event.shiftKey === false)) {
    if (event.target.id === menu[0].id) {
      // The menu is the target.
      ROGO.html5.listener.hotspot.focus_first(event, selector);
    } else {
      // Next menu item.
      ROGO.html5.listener.hotspot.focus_next(event, selector, event.target);
    }
  } else if (event.key === 'ArrowLeft' || (event.key === 'Tab' && event.shiftKey === true)) {
    if (event.target.id !== menu[0].id) {
      // Previous Menu item.
      ROGO.html5.listener.hotspot.focus_previous(event, selector, event.target);
    }
  } else if (event.key === ' ' || event.key === 'Enter') {
    event.preventDefault();
    event.stopPropagation();
    // Turn the event into a click.
    $(event.target).click();
  }
};

/**
 * Handle key presses for layer menus.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.layer_keyboard_handler = function(event) {
  var menu = $(event.target).closest(ROGO.html5.classes.hotspot.get_class('layermenu', true)),
    // Gets the layer items only.
    layeritem = '#' + menu[0].id + ' ' + ROGO.html5.classes.hotspot.get_class('layermenuitem', true) + '[tabindex]',
    // Get layers and tabbed items in the active layer.
    selector = layeritem + ', ' + layeritem + ROGO.html5.classes.hotspot.get_class('active', true) + ' [tabindex]',
    text_input = $(event.target).hasClass(ROGO.html5.classes.hotspot.get_class('layername')),
    layer = $(event.target).closest(layeritem).first();
  if ((event.key === 'ArrowRight' && !text_input) || (event.key === 'Tab' && event.shiftKey === false)) {
    ROGO.log(event, 'warn');
    if (event.target.id === menu[0].id) {
      // The menu is the target.
      ROGO.html5.listener.hotspot.focus_first(event, selector);
    } else {
      // Next menu item.
      ROGO.html5.listener.hotspot.focus_next(event, selector, event.target);
    }
  } else if ((event.key === 'ArrowLeft' && !text_input) || (event.key === 'Tab' && event.shiftKey === true)) {
    if (event.target.id !== menu[0].id) {
      // Previous Menu item.
      ROGO.html5.listener.hotspot.focus_previous(event, selector, event.target);
    }
  } else if (event.key === 'ArrowDown') {
    if (event.target.id === menu[0].id) {
      // The menu is the target.
      ROGO.html5.listener.hotspot.focus_first(event, layeritem);
    } else {
      // Next menu item.
      ROGO.html5.listener.hotspot.focus_next(event, layeritem, layer);
    }
  } else if (event.key === 'ArrowUp') {
    if (event.target.id !== menu[0].id) {
      // Previous Menu item.
      ROGO.html5.listener.hotspot.focus_previous(event, layeritem, layer);
    }
  } else if ((event.key === ' ' && !text_input) || event.key === 'Enter') {
    event.preventDefault();
    event.stopPropagation();
    // Turn the event into a click.
    $(event.target).click();
  }
};

/**
 * Focus on the first element selected by the selector.
 *
 * @param {Event} event
 * @param {String} selector
 * @returns {void}
 */
ROGO.html5.listener.hotspot.focus_first = function(event, selector) {
  var focusable = $(selector);
  event.preventDefault();
  event.stopPropagation();
  focusable.first().focus();
};

/**
 * Focus on the last element selected by the selector.
 *
 * @param {Event} event
 * @param {String} selector
 * @returns {void}
 */
ROGO.html5.listener.hotspot.focus_last = function(event, selector) {
  var focusable = $(selector);
  event.preventDefault();
  event.stopPropagation();
  focusable.last().focus();
};

/**
 * Focus on the next element.
 *
 * @param {Event} event
 * @param {String} selector
 * @param {Element} current_element
 * @returns {void}
 */
ROGO.html5.listener.hotspot.focus_next = function(event, selector, current_element) {
  ROGO.html5.listener.hotspot.move_focus_by(event, selector, current_element, 1);
};

/**
 * Focus on the previous element.
 *
 * @param {Event} event
 * @param {String} selector
 * @param {Element} current_element
 * @returns {void}
 */
ROGO.html5.listener.hotspot.focus_previous = function(event, selector, current_element) {
  ROGO.html5.listener.hotspot.move_focus_by(event, selector, current_element, -1);
};

/**
 * Moves the focus by a set number of elements.
 * If the distance change overflows then the first or last element is selected.
 *
 * @param {Event} event
 * @param {String} selector
 * @param {Element} current_element
 * @param {Number} distance the number of elements to move the focus by.
 * @returns {void}
 */
ROGO.html5.listener.hotspot.move_focus_by = function(event, selector, current_element, distance) {
  var focusable = $(selector),
    index = focusable.index(current_element),
    newindex = index + distance;
  if (index === -1) {
    // The focused element is not one we care about.
    return;
  }
  if (newindex < 0 && index === 0) {
    // We are on the first element already.
    return;
  }
  if (newindex >= focusable.length && index === (focusable.length - 1)) {
    // The last element so we will let tabe work normally.
    return;
  }
  // Check we are not overflowing.
  if (newindex < 0) {
    // set to the first element.
    newindex = 0;
  } else if (newindex >= focusable.length) {
    // Set to tha last element.
    newindex = focusable.length - 1;
  }
  event.preventDefault();
  event.stopPropagation();
  focusable[newindex].focus();
};

/**
 * Starts listening for drags on a specific question.
 *
 * @param {String} questionid
 * @returns {void}
 */
ROGO.html5.listener.hotspot.start_drag_listener = function(questionid) {
  var base_class = '#' + questionid,
    body = $('body'),
    viewarea = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('viewarea', true);
  body.on('mousedown', viewarea, ROGO.html5.listener.hotspot.viewarea_start_drag);
  body.on('mouseup', viewarea, ROGO.html5.listener.hotspot.viewarea_end_drag);
  body.on('mouseout', viewarea, ROGO.html5.listener.hotspot.viewarea_end_drag);
  body.on('mousemove', viewarea, ROGO.html5.listener.hotspot.viewarea_drag);
};

/**
 * Stop listening for drags on a specific question.
 *
 * @param {String} questionid
 * @returns {void}
 */
ROGO.html5.listener.hotspot.stop_drag_listener = function(questionid) {
  var base_class = '#' + questionid,
    body = $('body'),
    viewarea = base_class + ' ' + ROGO.html5.classes.hotspot.get_class('viewarea', true);
  body.off('mousedown', viewarea);
  body.off('mouseup', viewarea);
  body.off('mouseout', viewarea);
  body.off('mousemove', viewarea);
};

/**
 * Setup the event listeners on a colour selector.
 *
 * @param {String} selectorid
 * @returns {void}
 */
ROGO.html5.listener.hotspot.start_colour_select_listeners = function(selectorid) {
  var base_class = '#' + selectorid,
    body = $('body'),
    cancel = base_class + ' .' + ROGO.html5.hotspot_colourselector.prototype.html_classes.cancel,
    colour = base_class + ' .' + ROGO.html5.hotspot_colourselector.prototype.html_classes.colour;
  body.on('click', cancel, ROGO.html5.listener.hotspot.colour_select_cancel);
  body.on('click', colour, ROGO.html5.listener.hotspot.colour_selected);
  body.on('keydown', base_class, ROGO.html5.listener.hotspot.colour_keyboard_handler);
};

/**
 * Turns off the event listeners for a colour selector.
 *
 * @param {String} selectorid
 * @returns {void}
 */
ROGO.html5.listener.hotspot.stop_colour_select_listeners = function(selectorid) {
  var base_class = '#' + selectorid,
    body = $('body'),
    cancel = base_class + ' .' + ROGO.html5.hotspot_colourselector.prototype.html_classes.cancel,
    colour = base_class + ' .' + ROGO.html5.hotspot_colourselector.prototype.html_classes.colour;
  body.off('click', cancel);
  body.off('click', colour);
  body.off('keydown', base_class);
};

/**
 * The cancel button on the colour selector has been clicked.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.colour_select_cancel = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event);
  ROGO.html5.questions[question.id].deactivate_colourselect(true);
};

/**
 * A colour swatch has been clicked.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.colour_selected = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event),
    colour_element = $(event.target),
    // The colour in rgb form.
    colour = colour_element.css('backgroundColor');
  // Strip off the 'rgb(' and ')' parts of the colour.
  colour = colour.slice(4, -1);
  ROGO.html5.questions[question.id].colour_selected(colour);
};

/**
 * A edit menu item was clicked.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.editmenuitem_clicked = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event);
  var clicked_element = $(event.target);
  var edit_menu_item = clicked_element.closest('.html5 ' + ROGO.html5.classes.hotspot.get_class('editmenuitem', true));
  ROGO.html5.questions[question.id].activate_edit_menu_item(edit_menu_item.attr('id'));
  event.stopPropagation();
};

/**
 * A checkbox on the edit menu was toggled.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.edititem_toggled = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event);
  var menu_checkbox = $(event.target);
  ROGO.html5.questions[question.id].menu_item_toggled(menu_checkbox.attr('id'));
  event.stopPropagation();
};

/**
 * Gets the question element related to the event.
 *
 * @param {Event} event
 * @returns {HTMLElement}
 */
ROGO.html5.listener.hotspot.get_question = function(event) {
  var question = $(event.target).closest('.html5.hotspot');
  if (question.length === 1) {
    return question[0];
  } else {
    return null;
  }
};

/**
 * The text of the name of a label has been modified.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.label_name_changed = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event);
  var textbox = $(event.target);
  var layer = textbox.closest(ROGO.html5.classes.hotspot.get_class('layermenuitem', true));
  ROGO.html5.questions[question.id].change_layer_name(layer.attr('id'), textbox.text());
  event.stopPropagation();
};

/**
 * A layer menu item was clicked.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.layeritem_clicked = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event);
  var clicked_element = $(event.target);
  var layer_menu_item = clicked_element.closest('.html5 ' + ROGO.html5.classes.hotspot.get_class('layermenuitem', true));

  var set_active = ROGO.html5.questions[question.id].set_active_layer(layer_menu_item.attr('id'));
  if (clicked_element.hasClass(ROGO.html5.classes.hotspot.get_class('layername')) && clicked_element.attr('contenteditable') === 'true') {
    // If the user clicked on the text area give it focus so that can start editing.
    clicked_element.focus();
  }
  if (!set_active && clicked_element.hasClass(ROGO.html5.classes.hotspot.get_class('colourpicker'))) {
    // The colour picker of the active layer was clicked on.
    ROGO.html5.questions[question.id].activate_colourselect();
  }
  event.stopPropagation();
};

/**
 * A view area was clicked.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.viewarea_clicked = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event),
    x = event.offsetX,
    y = event.offsetY;
  if (ROGO.html5.questions[question.id].viewarea_clicked) {
    // Pass the co-ordinates of the click to the question.
    ROGO.html5.questions[question.id].viewarea_clicked(x, y);
  }
  event.stopPropagation();
};

/**
 * Store the id of the question that has an active drag on it.
 *
 * @type {String}
 */
ROGO.html5.listener.hotspot.active_drag_question = null;

/**
 * Store the last coordinate the mouse was over during a drag.
 *
 * @type {Object}
 */
ROGO.html5.listener.hotspot.active_drag_coordinates = {
  x: null,
  y: null
};

/**
 * Dragging was started on a view area.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.viewarea_start_drag = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event),
    x = event.offsetX,
    y = event.offsetY;
  // Save the active dragging question.
  ROGO.html5.listener.hotspot.active_drag_question = question.id;
  ROGO.html5.listener.hotspot.active_drag_coordinates.x = x;
  ROGO.html5.listener.hotspot.active_drag_coordinates.y = y;
  if (ROGO.html5.questions[question.id].drag_started) {
    ROGO.html5.questions[question.id].drag_started(x, y);
  }
};

/**
 * Dragging was ended.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.viewarea_end_drag = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event),
    x = event.offsetX,
    y = event.offsetY;
  if (ROGO.html5.listener.hotspot.active_drag_coordinates.x === null) {
    // No active drag in progress.
    return;
  }
  if (ROGO.html5.listener.hotspot.active_drag_question !== question.id) {
    // We are no longer over the shape we should be.
    x = ROGO.html5.listener.hotspot.active_drag_coordinates.x;
    y = ROGO.html5.listener.hotspot.active_drag_coordinates.y;
  }
  ROGO.html5.questions[ROGO.html5.listener.hotspot.active_drag_question].end_drag(x, y);
  ROGO.html5.listener.hotspot.active_drag_coordinates.x = null;
  ROGO.html5.listener.hotspot.active_drag_coordinates.y = null;
  ROGO.html5.listener.hotspot.active_drag_question = null;
};

/**
 * Dragging has happened on a view area.
 *
 * @param {Event} event
 * @returns {void}
 */
ROGO.html5.listener.hotspot.viewarea_drag = function(event) {
  var question = ROGO.html5.listener.hotspot.get_question(event),
    x = event.offsetX,
    y = event.offsetY;
  if (ROGO.html5.listener.hotspot.active_drag_question !== question.id) {
    ROGO.html5.questions[question.id].mouseover(x, y);
    return;
  }
  if (ROGO.html5.listener.hotspot.active_drag_coordinates.x === null) {
    // No drag is active.
    // We should let the question know the mouse is over it.
  } else {
    ROGO.html5.questions[ROGO.html5.listener.hotspot.active_drag_question].drag(x, y);
    ROGO.html5.listener.hotspot.active_drag_coordinates.x = x;
    ROGO.html5.listener.hotspot.active_drag_coordinates.y = y;
  }
};
