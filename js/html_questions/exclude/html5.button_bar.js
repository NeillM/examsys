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
 * Button bar object used in html5 questions
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Constructor for a html5_button_bar
 * 
 * @returns {html5_button_bar}
 */
var html5_button_bar = function() {
  /**
   * An object of the names of the buttons in the bar.
   * It is used to look up the buttons by name.
   *
   * @type {Object}
   * @private
   */
  this.names = {};

  /**
   * An array of html5_button objects in the bar.
   *
   * @type {Array}
   * @private
   */
  this.buttons = [];

  /**
   * An internal pointer to the current record.
   *
   * @type {Number}
   * @private
   */
  this.iterator = null;
};

/**
 * Add a html5_button to the bar.
 *
 * @param {html5_button} button
 * @returns {void}
 * @public
 */
 html5_button_bar.prototype.add = function(button) {
   // The length of the buttons array should match the index
   // that the next element will get when pushed in.
   this.names[button.name] = this.buttons.length;
   this.buttons.push(button);
 };

/**
 * Returns a button based on it's name.
 *
 * @param {String} name
 * @returns {html5_button}
 * @public
 */
html5_button_bar.prototype.getByName = function(name) {
  var position = this.names[name];
  return this.buttons[position];
};

/**
 * Sets the itterator to the next button.
 *
 * @returns {Boolean}
 * @public
 */
html5_button_bar.prototype.next = function() {
  if (this.iterator === null) {
    this.iterator = 0;
  } else {
    this.iterator++;
  }
  // There is only a valid value if the iterator is
  // less than the length of the button array.
  return (this.iterator < this.buttons.length);
};

/**
 * Resets the iterator, next() should always be called after using it.
 * 
 * @returns {void}
 * @public
 */
html5_button_bar.prototype.reset = function() {
  this.iterator = null;
};

/**
 * Gets the current html5_button.
 *
 * @returns {html5_button}
 * @public
 */
html5_button_bar.prototype.get = function() {
  return this.buttons[this.iterator];
};
