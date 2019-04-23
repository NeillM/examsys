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
 * Label used in hotspot questions
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 *
 * @param {Number} index
 * @param {String} config_text
 * @returns {html5_hotspot}
 */
var html5_hotspot_label = function(index, config_text) {
  var config = config_text.split("~");

  /**
   * @type {Number}
   * @public
   */
  this.index = index;

  /**
   * The text for the label.
   *
   * @type {String}
   * @public
   */
  this.text = config.shift();

  var nextval = config[0],
      colourval;
  if (nextval === 'polygon' || nextval === 'rectangle' || nextval === 'ellipse') {
    // Some legacy configs do not contain a colour, so we need to hardcode it.
    colourval = '#0070C0';
  } else {
    colourval = hexifycolour(config.shift());
  }

  /**
   * The colour of the label in hexidecimal format.
   *
   * @type {String}
   * @public
   */
  this.colour = colourval;

  /**
   * An array of shapes used in the layer.
   *
   * @type {Array}
   * @public
   */
  this.shapes = [];

  // Find all the shapes configured in the layer.
  var shape, coordinates, id;
  while ((config.length / 3) >= 1) {
    // There are at least 3 values left.
    shape = config.shift();
    coordinates = config.shift();
    id = config.shift();
    // Add the shape to the layer.
    this.shapes.push(new html5_shape(shape, coordinates, id, this));
  }

  /**
   * The number of shapes in the layer.
   *
   * @type {Number}
   * @public
   */
  this.length = this.shapes.length;
};

/**
 * Draws the shapes in the label.
 *
 * @param {Object} context
 * @returns {void}
 */
html5_hotspot_label.prototype.draw = function(context) {
  for (var shape in this.shapes) {
    shape.draw(context);
  }
};
