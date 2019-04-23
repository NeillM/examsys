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
 * Base html5 hotspot question in answer mode.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

ROGO.html5.classes.hotspot_answer = function() {
  // Extend the hotspot prototype.
  ROGO.html5.classes.hotspot.call(this);
  this.set_mode('answer');
  /**
   * The user's answer to the question.
   *
   * @type {String}
   * @private
   */
  this.answer;
};

/**
 * Extend the hotspot prototype.
 * @type Object
 */
ROGO.html5.classes.hotspot_answer.prototype = Object.create(ROGO.html5.classes.hotspot.prototype);

/**
 * Sets up the object for use.
 * @param {Element} parent
 * @returns {void}
 */
ROGO.html5.classes.hotspot_answer.prototype.setup = function(parent) {
  ROGO.html5.classes.hotspot.prototype.setup.call(this, parent);
  var question = $(parent);
  this.setup_answers(question.data('answer'));
  parent.appendChild(this.create_main());
  this.redraw();
};

ROGO.html5.classes.hotspot_answer.prototype.setup_answers = function(answer_config) {
  this.answer = answer_config;
  if (!answer_config || answer_config === 'u') {
    // The question is unanswered. If re-visiting page, need to highlight all
    // text boxes.
    return;
  }
  // Answers for each layer should be separated by a pipe character.
  // If any answer is set, then the number of answer parts should
  // equal the number of layers.
  var answers = this.answer.split('|');
  if (answers.length !== this.layers.length) {
    ROGO.log('Mismatch between the number of answers and layers.', 'warn');
  }
  for (var i in answers) {
    // Each answer should consit of an x and y coordinate separated by a comma.
    if (answers[i] == 'u') {
      ROGO.log('Unanswered question (index: ' + i + ')', '', 'info');
      this.layers[i].unanswered = true;
    } else {
      var answer = answers[i].split(',');
      if (answer.length !== 2) {
        ROGO.log('Answer invalid (index: ' + i + ')', 'error');
      } else if (this.layers[i]) {
        this.layers[i].set_answer(new ROGO.html5.hotspot_answer(answer[0], answer[1]));
      } else {
        // The answer does not have a coresponding layer.
        ROGO.log('Answer for layer that does not exist', 'warn');
      }
    }
  }
};

/**
 * Sets the answer for the active layer and then increments the active layer.
 *
 * @param {Number} x The x coordinate of the answer.
 * @param {Number} y The y coordinate of the answer.
 * @returns {void}
 */
ROGO.html5.classes.hotspot_answer.prototype.viewarea_clicked = function(x, y) {
  for (var i in this.layers) {
    if (this.active_layer === this.layers[i].index) {
      this.layers[i].set_answer(new ROGO.html5.hotspot_answer(x, y));
      var next_layer = (parseInt(i) + 1) % this.layers.length;
      var layer_changed = this.set_active_layer(this.generate_layermenu_item_id(this.layers[next_layer].index));
      if (!layer_changed) {
        // If there is only a single layer the set active layer method will not have done a redraw.
        this.redraw();
      }
      this.update_page();
      break;
    }
  }
};

/**
 * Modify the Rogo page with the current state of the question.
 *
 * @returns {void}
 */
ROGO.html5.classes.hotspot_answer.prototype.update_page = function() {
  $('#q' + this.number).attr('value', this.answer_string());
};
