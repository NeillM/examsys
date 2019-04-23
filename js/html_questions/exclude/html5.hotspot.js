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
 * Hotspot question
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * Hotspot question.
 *
 * @param {Integer} num question number?
 * @param {String} doorId ?
 * @param {String} lang The language of the page
 * @param {String} image The name of the image to be displayed
 * @param {String} config The encoded configuration for the question (including the answers!!!)
 * @param {String} answer The user answer?
 * @param {type} extra ?
 * @param {String} colour A hexidecimal encoded colour code
 * @param {String} mode The mode the question should display in
 * @returns {html5_question_hotspot}
 */
var html5_hotspot = function(num, doorId, lang, image, config, answer, extra, colour, mode) {
  this.q_Num = num;
  
  /**
   * The canvas element used to display the question.
   *
   * @type {Element}
   * @private
   */
  this.canvas = document.getElementById('canvas' + num);

  this.doorId = doorId;
  this.qmode = mode;

  /**
   * An array of html5_hotspot_label objects.
   *
   * @type {Array}
   * @private
   */
  this.hotSpots = [];

  if (config == '') {
    // Force a blank unconfigured hotspot.
    config = '~~';
  }

  var hotspotconfigs = config.split('|');
  for (var i = 0; i < hotspotconfigs.length; i++) {
    this.hotSpots.push(new html5_hotspot_label(i, hotspotconfigs[i]));
  }
  
  /**
   * @type {Boolean}
   * @private
   */
  this.is_an_answer;
  /**
   * @type {Boolean}
   * @private
   */
  this.allUnaswered;

  /**
   * Stores the answer a student has given.
   *
   * @type {array}
   * @private
   */
  this.useranswer = [];

  /**
   * Stores the answers to be analysed.
   *
   * @type {array}
   * @private
   */
  this.analysis = [];

  /**
   * Stores all user answers for corrections.
   *
   * @type {array}
   * @private
   */
  this.corrections = [];

  switch(this.qmode) {
    case 'answer':
    case 'script':
      this.init_answer(answer);
      break;
    case 'analysis':
      this.init_analysis(answer);
      break;
    case 'correction':
      break;
  }
};

/**
 * 
 * @param {String} answer
 * @returns {void}
 */
html5_hotspot.prototype.init_answer = function(answer) {
  var i, j;
  // Create unanswered values for each label
  for (i in this.hotSpots) {
    // Set all the parts to unanswered.
    // If the user actually placed a label on a hotspot
    // the entry for it will be overwritten later.
    this.answers[i] = new html5_hotspot_answer(0, 'false', 'false');
  }
  // Each hotspot layer should have an answer containing 3 comma separated values,
  // each of these layer answers should be sepated by a pipe. i.e.
  // 7,true,false|4,false,true
  var answer_parts = answer.split('|');
  if (this.is_unanswered(answer) || answer_parts.length !== this.answers.length) {
    // Not a valid answer so do not process it further.
    return;
  }
  for (j = 0; j < answer_parts.length; j++) {
    this.answers[j].update_from_config(answer_parts[j]);
  }
};

html5_hotspot.prototype.init_analysis = function(answer) {
  // Create unanswered values for each label
  for (i in this.hotSpots) {
    this.analysis[i] = [new html5_hotspot_answer(0, 'false', 'false')];
  }
  // Do stuff.
};

/**
 * Checks if the question is unanswered,
 * returns true if this is the case 
 * or false if there is an answer.
 * 
 * @param {String} answer
 * @returns {boolean}
 */
html5_hotspot.prototype.is_unanswered = function(answer) {
  var returnval;
  if (answer === '' || answer === 'undefined' || answer === null || answer === 'null') {
    // Seesm we can get some random strange answers from historical data.
    returnval = true;
    this.is_an_answer = false;
    this.allUnaswered = false;
  } else if (answer === 'u') {
    // The question is unanswered.
    returnval = true;
    this.is_an_answer = false;
    this.allUnaswered = true;
  } else {
    // The question has some form of answer.
    returnval = false;
    this.is_an_answer = true;
    this.allUnaswered = false;
  }
  return returnval;
};
