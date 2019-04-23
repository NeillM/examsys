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
 * Base html5 listener.
 *
 * Sets up listeners for interactions with html5 questions,
 * and routes the action to the question the user interacted with.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Activate listeners for question types that are on the page only.
 *
 * @param {Object} questiontypes @see ROGO.html5.question_types_on_page
 * @returns {undefined}
 */
ROGO.html5.listener.init = function(questiontypes) {
  for (var type in questiontypes) {
    if (questiontypes[type] === true) {
      // This type of question was found.
      if (ROGO.html5.listener[type] && ROGO.html5.listener[type].init) {
        // Call the question types listner initialisation.
        ROGO.html5.listener[type].init();
      }
    }
  }
};
