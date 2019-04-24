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
 * A component to serve localised Rogo language strings in JavaScript
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

// Create the language namespace.
ROGO.lang = ROGO.lang || {};

/**
 * Each property of the object should be a component for the strings.
 *
 * @type {Object}
 */
ROGO.lang.components = {};

/**
 * Get a string.
 *
 * Will log an error if the string does not exist and a warning
 * if a parameter in a string foes not have a value passed.
 *
 * @param {String} name The name of the string
 * @param {String} component The component the string is part of
 * @param {Array} args An array of strings that are used to replace any instances of %s in the string.
 * @returns {String}
 */
ROGO.lang.get_string = function(name, component, args) {
  component = component || 'default';
  if (!ROGO.lang.components[component] || !ROGO.lang.components[component][name]) {
    ROGO.log('Missing string: ' + component + ':' + name, 'error');
    // String not found, print out the component and name in a way that makes it obvious which one is missing.
    return '[[' + component + ':' + name + ']]';
  }
  var string = ROGO.lang.components[component][name];
  var match_count = 0;
  // Replace each '%s' in the string with a value from the arguments array.
  return string.replace(/%s/g, function (match) {
    if (Array.isArray(args) && args[match_count]) {
      // Return the new value for string and increment the match count.
      return args[match_count++];
    }
    // No replacement sent, highlight this.
    ROGO.log('Missing parametr for string: ' + component + ':' + name, 'warn');
    return match;
  });
};

/**
 * Set a string.
 *
 * @param {String} string The value of the string
 * @param {String} name The name of the string
 * @param {String} component The component the string is part of.
 * @returns {void}
 */
ROGO.lang.set_string = function(string, name, component) {
  component = component || 'default';
  if (!ROGO.lang.components[component]) {
    // Create the component.
    ROGO.lang.components[component] = {};
  }
  ROGO.lang.components[component][name] = string;
};

/**
 * Set a batch of strings in one go.
 *
 * @param {Object} strings The properties should be the string name, the value the text to be displayed.
 * @param {String} component The component the string is part of.
 * @returns {void}
 */
ROGO.lang.set_strings = function(strings, component) {
  component = component || 'default';
  for (var name in strings) {
    ROGO.lang.set_string(strings[name], name, component);
  }
};
