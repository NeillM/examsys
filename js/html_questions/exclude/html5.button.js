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
 * Button object used in html5 questions
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * A html5_button constructor.
 *
 * @param {String} name
 * @param {Number} posx
 * @param {Number} posy
 * @param {Number} width
 * @param {Number} height
 * @param {Number} overstate
 * @param {Number} awaystate
 * @param {String} set
 * @param {String} text
 * @param {String} tooltip
 * @returns {html5_button}
 */
var html5_button = function(name, posx, posy, width, height, overstate, awaystate, set, text, tooltip) {
  /**
   * The name of the icon.
   * It should refgerence an entry in the menuImages global object fron html5.images.js
   */
  this.name = name;
  this.icon_x = posx;
  this.icon_y = posy;
  this.icon_witdh = width;
  this.icon_height = height;
  this.over = overstate;
  /**
   * Indicator of "away state"
   * 0 - icon is not active - panel is hidden
   * 1 - the mouse hovers over the icon - present hover state
   * 2 - the icon was clicked and connected panel (color/line/size...) should be displayed
   */
  this.away = awaystate;
  /**
   * Two functionalities here:
   * In Labelling the value here is "a", "b" or "c"
   * and there ButtonSets are groups of buttons with option (radio button / group switching) functionality
   *
   * In Hotspot it's "a" and functionality as above
   * or "-" which signifies checkbox is correction mode
   * 
   * In Area the value is "+" which signify sticky button functionality
   * or "-" which signify inactive button ("delete point" or "Clear all" when there is no shape drawn yet)
   */
  this.set = set;
  /**
   * The text on the button that is always visible to the user.
   */
  this.text = text;
  /**
   * Text that appears when a user hovers over a button.
   */
  this.tooltip = tooltip;
};
