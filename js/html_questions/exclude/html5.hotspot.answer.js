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
 * Stores the answer for a hotspot question.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

var html5_hotspot_answer = function(val1, val2, val3) {
  this.val1 = val1;
  this.val2 = val2;
  this.val3 = val3;
};

html5_hotspot_answer.prototype.update_from_config = function(answer) {
  var parts = answer.split(',');
  this.val1 = parts[0];
  this.val2 = parts[1];
  this.val3 = parts[2];
}
