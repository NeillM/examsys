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
 * Shape used in hotspot question layers.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Constructor for the shape.
 *
 * @param {String} shape
 * @param {String} coordiantes
 * @param {Number} id
 * @param {html5_label} parent The parent html5_label of the shape.
 * @returns {html5_label}
 */
var html5_shape = function(shape, coordiantes, id, parent) {
  /**
   * Stores the type of shape.
   * i.e. ellipse, polygon, rectangle
   *
   * @type {String}
   * @public
   */
  this.shape = shape;

  /**
   * A comma seperated string of coordinates.
   *
   * @type {String}
   * @public
   */
  this.coordinates = coordiantes;

  /**
   * An id for the shape.
   *
   * @type {Number}
   * @public
   */
  this.id = id;

  /**
   * The parent layer of the shape.
   *
   * @type {html5_label}
   * @private
   */
  this.label = parent;
};

/**
 * Returns an array of coordinate parts for the shape.
 *
 * @returns {Array}
 */
html5_shape.prototype.getCoordinates = function() {
  return this.coordinates.split(',');
};

/**
 *
 * @param {Object} context
 * @returns {void}
 */
html5_shape.prototype.draw = function(context) {
  var colour = this.label.colour;
  // Draw the shape in the colour that is passed.
};
