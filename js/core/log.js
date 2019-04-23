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
 * Wrapper for outputting loggin messages.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/* eslint no-console: "off" */

ROGO.log = function(message, level) {
  if (!console) {
    return;
  }
  switch (level) {
    case 'error':
      if (console.error) {
        console.error(message);
      } else {
        console.log('Error: ' + message);
      }
      break;
    case 'warn':
      if (console.warn) {
        console.warn(message);
      } else {
        console.log('Warn: ' + message);
      }
      break;
    case 'info':
      if (console.info) {
        console.info(message);
      } else {
        console.log('Info: ' + message);
      }
      break;
    case 'trace':
      if (console.error && console.trace) {
        console.error(message, console.trace());
      } else {
        console.log('Error: ' + message);
      }
      break;
    default:
      console.log(message);
      break;
  }
};
