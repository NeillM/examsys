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
 * Base html5 question related functions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * An object containing an entry for each html5 question based objects on the page.
 *
 * @type {Object}
 */
ROGO.html5.questions = {};

/**
 * An object containing drawing methods.
 *
 * @type {Object}
 */
ROGO.html5.draw = {};

/**
 * The total tab index of the item on the page.
 *
 * @type {Number}
 * @static
 */
ROGO.html5.tabindex = 1;

/**
 * Stores if html5 questions have been initialised already.
 *
 * @type {Boolean}
 */
ROGO.html5.init_done = false;

/**
 * The web root config setting for Rogo.
 *
 * @type {String}
 */
ROGO.html5.webroot = '';

/**
 * Finds all html5 question placeholders and initialises them.
 *
 * @param {String} webroot The webroot config setting for Rogo.
 * @returns {void}
 */
ROGO.html5.init = function(webroot) {
  if (ROGO.html5.init_done === true) {
    return;
  }
  // Set the web root.
  if (typeof webroot !== 'string') {
    ROGO.log('webroot is not a string it is a ' + (typeof webroot), 'error');
  } else {
    if (!webroot.endsWith('/')) {
      webroot += '/';
    }
    ROGO.html5.webroot = webroot;
  }
  // Load the combined image.
  ROGO.html5.images.load();
  // Initialise the questions.
  $('.html5').each(ROGO.html5.init_question);
  // Add listeners, for question tyes on the page.
  ROGO.html5.listener.init(ROGO.html5.question_types_on_page);
  // Stop the initialisation from running more than one time.
  ROGO.html5.init_done = true;
};

/**
 * Stores which question types have been found on the page.
 *
 * @type {Object}
 */
ROGO.html5.question_types_on_page = {
  hotspot: false
};

/**
 * Initialise an individual question.
 *
 * @param {Integer} index
 * @param {Element} element
 * @returns {undefined}
 */
ROGO.html5.init_question = function(index, element) {
  var question = $(element);
  var type = question.data('type');
  var mode = question.data('mode');
  var class_name = type + '_' + mode;
  var instance = ROGO.html5.question_from_string(class_name, element);
  if (instance !== null) {
    ROGO.html5.questions[element.id] = instance;
    ROGO.log('HTML5 Loaded question: ' + element.id + ' as a ' + class_name, 'info');
    // Store that this type of question has been found.
    ROGO.html5.question_types_on_page[type] = true;
  }
};

/**
 * Initialise the question in the correct mode, assuming it exists.
 *
 * @param {String} class_name
 * @param {element} element
 * @returns {Object}
 */
ROGO.html5.question_from_string = function(class_name, element) {
  var question = null;
  if (typeof ROGO.html5.classes[class_name] === 'function') {
    question = new ROGO.html5.classes[class_name];
    question.setup(element);
  }
  return question;
};

/**
 * Converts a flash coded color into html coded color.
 *
 * @param {String} thiscolor
 * @returns {String}
 */
ROGO.html5.decode_colour = function(thiscolor) {
  if (typeof (thiscolor) !== 'undefined') {
    if (thiscolor !== '' && thiscolor.indexOf('0x') === -1 && thiscolor.indexOf('#') === -1) {
      thiscolor = '#' + Number(thiscolor).toString(16);
    }
    if (thiscolor.indexOf('0x') > -1) {
      thiscolor = '#' + thiscolor.substr(2, 6);
    }
    if (thiscolor.length < 7) {
      thiscolor = '000000' + thiscolor.substr(1, thiscolor.length - 1);
      thiscolor = '#' + thiscolor.substr(thiscolor.length - 6, 6);
    }
    return thiscolor;
  }
};

/**
 * Converts a flash coded color into an rgb coded color string.
 *
 * @param {String} thiscolor
 * @returns {String}
 */
ROGO.html5.decode_colour_rgb = function(thiscolor) {
  if (typeof (thiscolor) === 'undefined') {
    return;
  }
  if (thiscolor.indexOf(',') !== -1) {
    // Probably already decoded.
    return thiscolor;
  }
  if (thiscolor !== '' && thiscolor.indexOf('0x') === -1 && thiscolor.indexOf('#') === -1) {
    // It is a encoded as a single integer, it will be easier to convert after changing it to hexidecimal.
    thiscolor = Number(thiscolor).toString(16);
  } else if (thiscolor.indexOf('#') !== -1) {
    // Remove the leading hash.
    thiscolor = thiscolor.substr(1, 6);
  }
  // Remove any leading 0x string.
  if (thiscolor.indexOf('0x') > -1) {
    thiscolor = thiscolor.substr(2, 6);
  }
  // Ensure the size of the string is correct.
  if (thiscolor.length < 6) {
    thiscolor = '000000' + thiscolor;
    thiscolor = thiscolor.substr(thiscolor.length - 6, 6);
  }
  // Decode the hex colour into it's three part rgb decimal values, e.g.
  // 100, 25, 69
  return parseInt(thiscolor.substr(0, 2), 16) + ', '
          + parseInt(thiscolor.substr(2, 2), 16) + ', '
          + parseInt(thiscolor.substr(4, 2), 16);
};

/**
 * Encodes an RGB encoded colour as an integer.
 *
 * @param {String} colour
 * @returns {Integer}
 */
ROGO.html5.encode_rgb_colour = function(colour) {
  var rgb = colour.split(','),
    red = parseInt(rgb[0]) * 65536, // 256^2.
    green = parseInt(rgb[1]) * 256,
    blue = parseInt(rgb[2]),
    decimalcolour = red + green + blue;
  return decimalcolour;
};

/**
 * Draws an elipse based on a bounding box, using cubic bezier curves.
 * https://en.wikipedia.org/wiki/B%C3%A9zier_curve
 *
 * @param {CanvasRenderingContext2D} context
 * @param {String} fill_colour
 * @param {String} border_colour
 * @param {Array} coordinates
 * @returns {void}
 */
ROGO.html5.draw.ellipse = function(context, fill_colour, border_colour, coordinates) {
  var x, y, width, height, offset;
  if (!coordinates || coordinates.length !== 4) {
    // An invalid number of coordinates.
    return;
  }
  offset = ROGO.html5.offset(context);

  // Define the bounding box position and dimensions.
  width = coordinates[2] - coordinates[0];
  height = coordinates[3] - coordinates[1];

  if (width < 0) {
    // The shape was drawn from bottom to top.
    x = coordinates[2] + offset;
    width = Math.abs(width);
  } else {
    x = coordinates[0] + offset;
  }

  if (height < 0) {
    // The shape was drawn from right to left.
    y = coordinates[3] + offset;
    height = Math.abs(height);
  } else {
    y = coordinates[1] + offset;
  }

  // Kappa is the percentage of an arcs bounding box that the control points should be moved.
  // I'm assuming it is related to:
  // http://www.whizkidtech.redprince.net/bezier/circle/
  // This value should mean that if the bounding box is aquare then the elipse will aprxfactorimate a circle.
  var kappa = .5522848;
  // The distances of the control points should be moved along the bounding box for each arc.
  // Each individual control point will
  var xfactor = (width / 2) * kappa;
  var yfactor = (height / 2) * kappa;
  // Bottom right of the bounding box.
  var bottomx = x + width;
  var bottomy = y + height;
  // Centre of the elipse.
  var centrex = x + (width / 2);
  var centrey = y + (height / 2);

  // Prepare the shape.
  context.beginPath();
  context.moveTo(x, centrey);
  // The shape is drawn in 4 arcs
  context.bezierCurveTo(x, centrey - yfactor, centrex - xfactor, y, centrex, y); // Top left arc.
  context.bezierCurveTo(centrex + xfactor, y, bottomx, centrey - yfactor, bottomx, centrey); // Top right arc.
  context.bezierCurveTo(bottomx, centrey + yfactor, centrex + xfactor, bottomy, centrex, bottomy); // Bottom right arc.
  context.bezierCurveTo(centrex - xfactor, bottomy, x, centrey + yfactor, x, centrey); // Botton left arc.
  context.closePath();

  // Save the context settings we will change.
  context.save();

  context.strokeStyle = border_colour;
  context.fillStyle = fill_colour;
  context.fill();
  context.stroke();

  // Control points
  /*context.strokeStyle = '#FF0000';
  context.fillStyle = '#00FF00';
  context.globalAlpha = 1;
  context.fillRect(x, centrey - yfactor, 2, 2);
  context.strokeRect(x, centrey - yfactor, 2, 2);
  context.fillRect(centrex - xfactor, y, 2, 2);
  context.strokeRect(centrex - xfactor, y, 2, 2);
  context.fillRect(centrex + xfactor, y, 2, 2);
  context.strokeRect(centrex + xfactor, y, 2, 2);
  context.fillRect(bottomx, centrey - yfactor, 2, 2);
  context.strokeRect(bottomx, centrey - yfactor, 2, 2);
  context.fillRect(bottomx, centrey + yfactor, 2, 2);
  context.strokeRect(bottomx, centrey + yfactor, 2, 2);
  context.fillRect(centrex + xfactor, bottomy, 2, 2);
  context.strokeRect(centrex + xfactor, bottomy, 2, 2);
  context.fillRect(centrex - xfactor, bottomy, 2, 2);
  context.strokeRect(centrex - xfactor, bottomy, 2, 2);
  context.fillRect(x, centrey + yfactor, 2, 2);
  context.strokeRect(x, centrey + yfactor, 2, 2);*/
  
  // Bounding box.
  /*context.strokeStyle = '#00FF00';
  context.fillStyle = '#FF0000';
  context.fillRect(x, y, 2, 2);
  context.strokeRect(x, y, 2, 2);
  context.fillRect(bottomx, bottomy, 2, 2);
  context.strokeRect(bottomx, bottomy, 2, 2);
  context.globalAlpha = 0.5;
  context.strokeRect(x, y, bottomx - x, bottomy - y);
  // Centre
  context.globalAlpha = 1;
  context.strokeStyle = '#00FF00';
  context.fillStyle = '#0000FF';
  context.fillRect(centrex, centrey, 2, 2);
  context.strokeRect(centrex, centrey, 2, 2);*/

  context.restore();
};

/**
 * Draws a polygon shape.
 *
 * @param {CanvasRenderingContext2D} context
 * @param {String} fill_colour
 * @param {String} border_colour
 * @param {Array} coordinates
 * @returns {void}
 */
ROGO.html5.draw.polygon = function(context, fill_colour, border_colour, coordinates) {
  if (!coordinates || coordinates.length < 4 || coordinates.length % 2 !== 0) {
    // An invalid number of coordinates.
    return;
  }
  var offset = ROGO.html5.offset(context);

  context.beginPath();
  // Start at the first co-ordinates.
  context.moveTo(coordinates[0] + offset, coordinates[1] + offset);
  // Draw a line from one co-ordinate to the next.
  for (var i = 2; i < coordinates.length; i = i + 2) {
    context.lineTo(coordinates[i] + offset, coordinates[i+1] + offset);
  }
  context.closePath();

  context.save();
  context.strokeStyle = border_colour;
  context.fillStyle = fill_colour;
  context.fill();
  context.stroke();
  context.restore();
};

/**
 * Draws a rectangle.
 *
 * @param {CanvasRenderingContext2D} context
 * @param {String} fill_colour
 * @param {String} border_colour
 * @param {Array} coordinates
 * @returns {void}
 */
ROGO.html5.draw.rectangle = function(context, fill_colour, border_colour, coordinates) {
  var x, y, width, height, offset;
  if (!coordinates || coordinates.length !== 4) {
    // An invalid number of coordinates.
    return;
  }
  offset = ROGO.html5.offset(context);

  // Convert the stored coordinates into a form that can be used for drawing.
  width = coordinates[2] - coordinates[0];
  height = coordinates[3] - coordinates[1];

  if (width < 0) {
    // The shape was drawn from right to left.
    x = coordinates[2] + offset;
    width = width * -1;
  } else {
    x = coordinates[0] + offset;
  }

  if (height < 0) {
    // The shape was drawn from bottom to top.
    y = coordinates[3] + offset;
    height = height * -1;
  } else {
    y = coordinates[1] + offset;
  }

  context.save();
  // Do the drawing.
  context.strokeStyle = border_colour;
  context.fillStyle = fill_colour;
  context.fillRect(x, y, width, height);
  context.strokeRect(x, y, width, height);
  context.restore();
};

/**
 * A drawing offset may be needed to avoid anti-aliasing of horizontal and vertical lines.
 * This is caused by lines being centered at the 'edge' of pixels.
 *
 * @param {CanvasRenderingContext2D} context
 * @returns {Number}
 */
ROGO.html5.offset = function(context) {
  var offset;
  if (context.lineWidth % 2 === 0) {
    // Lines have an even number of pixels, they will not anti-alias without a half pixel offset.
    offset = 0;
  } else {
    // Lines have an odd number of pixels, to avoid anti-aliasing the position needs tobe offset by half a pixel.
    offset = 0.5;
  }
  return offset;
};
