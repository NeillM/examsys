var rq = new Array(); //array of questions/canvases

/**
 * Main startup function.
 * 
 * Sets up the javascript required for a html5 question to function.
 *
 * @param {Integer} num question number
 * @param {String} canvasId Seems to always be flash<num> 'imprint of flash version but could have been any id
 * @param {String} lang The language of the page
 * @param {String} image The name of the image to be displayed
 * @param {String} config The encoded configuration for the question (including the answers!!!)
 * @param {String} answer The user answer (sometimes)
 * @param {type} extra - question type specific extra options:
 *         Labelling - comma separated: extra_std (ie.'1~0~Mark per Option'), exclusions(string of 0's or 1's), 
 *                     feedback options (5x digits[01]: Ticks/Crosses; Correct Answer Highlight; Question Marks; Text Feedback; Hide all feedback if unanswered))
 *         Hotspot - comma separated: feedback options (2x digits[01]:Ticks/Crosses; Correct Answer Highlight), exclusions(string of 0's or 1's)
 *         Area: 5x digits[0-1] display_students_response, display_correct_answer, not used, not used, hide_feedback_ifunanswered
 * @param {String} colour
 * @param {String} type The type of question to be generated (labelling, hotspot, or area)          
 * @param {String} mode The mode the question should display in (answer, edit, script, analysis, or correction)
 * @returns {void}
 *
 * @global {Array} rq
 */
function setUpQuestion(num, canvasId, lang, image, config, answer, extra, colour, type, mode) {
  var jspathprefix = '../';
  if (typeof (mode) == 'undefined') {
    mode = 'answer';
  } else if (mode == '1') {
    mode = 'answer';
  } else if (mode == '2') {
    mode = 'edit';
    jspathprefix = '../../';
  } else if (mode == '3') {
    mode = 'script';
  } else if (mode == '4') {
    mode = 'analysis';
  } else if (mode == '5') {
    mode = 'correction';
  } else if (mode == 'edit') {
    jspathprefix = '../../';
  }

  //preload cursors
  $.get(jspathprefix + 'js/images/cur_erase.cur', function () { });
  $.get(jspathprefix + 'js/images/cur_cross.cur', function () { });

// Set up the question.
  if (type == 'labelling') {
    rq[num] = new rql(num);
    rq[num].setUpLabelling(num, canvasId, lang, image, config, answer, extra, colour, mode);
  }
  if (type == 'hotspot') {
    rq[num] = new rqh(num);
    rq[num].setUpHotspot(num, canvasId, lang, image, config, answer, extra, colour, mode)
  }
  if (type == 'area') {
    rq[num] = new rqa(num);
    rq[num].setUpArea(num, canvasId, lang, image, config, answer, extra, colour, mode)
  }
}

/**
 * Function gets the key used in an event of the object it is bound to.
 * 
 * @returns {get_char_key}
 */
function get_char_key() {
  if (this.ev.type == 'keypress') {
    this.char_code = (this.ev.charCode == 0 ? '' : String.fromCharCode(this.ev.charCode));
  }
  if (this.ev.type == 'keydown') {
    this.isShift = this.ev.shiftKey ? true : false;
    this.isCtrl = this.ev.ctrlKey ? true : false;
    this.ShiftChange = true;
    if (this.ev.keyCode == 32) {
      this.char_code = ' ';
    }
  }
  if (this.ev.type == 'keyup') {
    this.isShift = this.ev.shiftKey ? true : false;
    this.isCtrl = this.ev.ctrlKey ? true : false;
    this.ShiftChange = true;
    this.key_code = this.ev.keyCode;
  }
}

/**
 * converts flashcolor into htmlcolor
 * 
 * @param {String} thiscolor
 * @returns {String}
 */
function hexifycolour(thiscolor) {
  if (typeof (thiscolor) != 'undefined') {
    if (thiscolor != '' && thiscolor.indexOf('0x') == -1 && thiscolor.indexOf('#') == -1) {
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
}

/**
 * Calculates the height fo the text block of given width.
 * 
 * @param {String} tt The text
 * @param {Integer} tw The width of the text area
 * @returns {Number} Height of the text
 */
function textHeight(tt, tw) {
  var ty = 0;
  if (tt != '' && tt != 'undefined') {
    var words = tt.split(' ');
    var line = '';
    for (var n = 0; n < words.length; n++) {
      var testLine = line + words[n] + ' ';
      var metrics = this.context.measureText(testLine);
      var testWidth = metrics.width;
      if (testWidth > tw) {
        line = words[n] + ' ';
        ty += this.fontSizes[this.fontSizePos];
      } else {
        line = testLine;
      }
    }
  }
  return (ty + this.fontSizes[this.fontSizePos]);
}

/**
 * Wraps text with given width.
 *
 * Returns an Array:
 * [0] The text using | to define a new line
 * [1] The height of the text
 * [2] The width of the text
 * 
 * @param {String} tt The text to be wrapped
 * @param {Integer} tw The width of a line
 * @param {Boolean} elastic When set to true the width should be expanded to the maximum width of a single word if it is lower.
 * @returns {Array}
 * @global {type} undefinesd I'm assuming this is a mistake... and it should really have been a string.
 */
function wrapText(tt, tw, elastic) {
  if (typeof (elastic) == 'undefined') {
    elastic = true;
  }
  /**
   * Checks if any words are wider than the width of the line.
   * If there are it will split them with a space.
   *
   * @param {type} ctx A canvas 2d context object.
   * @returns {Boolean}
   * @global {String} tt Text from the wrapText() that called this method
   * @global {Integer} tw The width of a line
   */
  function breakText(ctx) {
    var words = tt.split(' ');
    var broken = false;
    for (var n = 0; n < words.length; n++) {
      var metrics = ctx.measureText(words[n]);
      if (metrics.width > tw) {
        // The word is wider than the line.
        broken = true;
        // Loop through each character in the word until it is longer than the line, then define a break point.
        // This doesn't look like it would handle a situation where the word was more than twice the width of the line.
        for (var m = 1; m < words[n].length; m++) {
          metrics = ctx.measureText(words[n].substr(0, m));
          if (metrics.width < tw) {
            var div_point = m;
          }
        }
        // Split the word with a space.
        words[n] = words[n].substr(0, div_point) + ' ' + words[n].substr(div_point);
      }
      tt = words.join(' ');
    }
    return broken;
  }

  // Stores the total height of the text.
  var ty = 0;
  if (tt != 'undefined') {
    var to_brake = true;
    if (!elastic) {
      while (to_brake) {
        to_brake = breakText(this.context);
      }
    }
    // Split the text into individulal words.
    var words = tt.split(' ');
    // Stores the working line.
    var line = '';
    // Stores the text that has been broken down into lines that will be returned.
    var lines = '';

    //verify width (tw) against words lengths
    if (elastic) {
      // Set the width to be at least fit the longest word.
      for (var n = 0; n < words.length; n++) {
        var metrics = this.context.measureText(words[n]);
        if (metrics.width > tw) {
          tw = metrics.width;
        }
      }
    }
    for (var n = 0; n < words.length; n++) {
      var testLine = line;
      if (testLine != '') {
        testLine += ' ';
      }
      testLine += words[n];

      var metrics = this.context.measureText(testLine);
      var testWidth = metrics.width;
      if (testWidth > tw) {
        // The test line is longer than the width.
        // Add the line without the word that causes the overflow to the output,
        // line endings are denoted by a pipe symbol.
        lines += line + '|';
        // Start a new line with the word that casued the overflow.
        line = words[n];
        ty += this.fontSizes[this.fontSizePos];
      } else {
        line = testLine;
      }
    }
    // Append on the remaining text.
    lines += line;
    return Array(lines, ty + this.fontSizes[this.fontSizePos], tw);
  }
}

/**
 * Draws a string to the canvas. 
 * 
 * If it has been passed through wrapText() it will be be on
 * multiple lines if necesary.
 * 
 * @param {type} ctx Unused
 * @param {String} tt The text to be written
 * @param {Integer} tx x position of the start of the text
 * @param {Integer} ty y position of the start of the text
 * @returns {void}
 */
function fillWrappedText(ctx, tt, tx, ty) {
  var words = tt.split('|');
  for (var n = 0; n < words.length; n++) {
    this.context.fillText(words[n], tx, ty);
    ty += this.fontSizes[this.fontSizePos];
  }
}

/**
 * ? not used anymore?
 * 
 * @param {Object} obj
 * @returns {Object|void}
 */
function findPos(obj) {
  var loc_lft = 0;
  var loc_top = 0;
  if (obj.offsetParent) {
    do {
      loc_lft += obj.offsetLeft;
      loc_top += obj.offsetTop;
    } while (obj = obj.offsetParent);
    return {left: loc_lft, top: loc_top};
  }
}

/**
 * tests if given point is within given rectangle
 * 
 * @param {Integer} ax Looks like x coordinate of the point to test
 * @param {Integer} ay Looks like y coordinate of the point to test
 * @param {Integer} bx Looks like x coordinate of the top left of the rectangle
 * @param {Integer} by Looks like y coordinate of the top left of the rectangle
 * @param {Integer} cx Looks like size of the rectangle on the x-axis
 * @param {Integer} cy Looks like size of the rectangle on the y-axis
 * @returns {Boolean}
 * @gloabl {type} tw Where do we expect this to come from?
 * @gloabl {Array} twr
 */
function testWithin(ax, ay, bx, by, cx, cy) {
  var testres = false;
  if ((ax > bx) && (ax < (bx + cx)) && (ay > by) && (ay < (by + cy))) {
    testres = true;
  }

  var showtest = false;
  if (showtest) {
    if (typeof (tw) == 'undefined') {
      tw = true;
    }
    this.context.strokeStyle = '#AAA';
    if (tw) {
      tw = false;
      if (testres) {
        this.context.strokeStyle = '#0F0';
      } else {
        this.context.strokeStyle = '#F00';
      }
    }
    this.context.strokeRect(bx, by, cx, cy);
    twr = [bx, by, cx, cy, this.context.strokeStyle];
  }

  return testres;
}

/**
 * draws a dot
 * 
 * @param {type} ctx Not used...
 * @param {color|gradient|pattern} cc Describes how the dot should look
 * @param {Integer} xx x positoon of the centre
 * @param {Integer} yy y positoon of the centre
 * @param {Integer} rr Radius
 * @returns {void}
 */
function edtDot(ctx, cc, xx, yy, rr) {
  this.context.strokeStyle = cc;
  this.context.fillStyle = cc;
  this.context.beginPath();
  this.context.arc(xx, yy, rr, 0, 2 * Math.PI, false);
  this.context.stroke();
  this.context.fill();
}

/**
 * draws a line colour, start, length/width
 * 
 * @param {type} ctx Not used
 * @param {color|gradient|pattern} cc Describes how the line should look
 * @param {Integer} xx x position the user started the line.
 * @param {Integer} yy y position the user started the line.
 * @param {Integer} ww the distance on the x-axis the users dragged.
 * @param {Integer} hh the distance on the y-axis the users dragged.
 * @param {type} ee Unused.
 * @returns {void}
 */
function lineDraw(ctx, cc, xx, yy, ww, hh, ee) {
  this.context.strokeStyle = cc;
  this.context.beginPath();
  this.context.moveTo(xx, yy);
  this.context.lineTo(xx + ww, yy + hh);
  this.context.stroke();
}

/**
 * draws an ellipse line/fill colour, start, length/width, is_filled
 * 
 * @param {type} ctx
 * @param {color|gradient|pattern} cc Describes how the border should look
 * @param {color|gradient|pattern} cb Describes how the fill should look
 * @param {Integer} xx x-position the user started the ellipse.
 * @param {Integer} yy y-position the user started the ellipse.
 * @param {Integer} ww the distance on the x-axis the users dragged.
 * @param {Integer} hh the distance on the y-axis the users dragged.
 * @param {Boolean} ee ? flag to make fill of the ellipse the same colour as the border - for testing purposes
 * @returns {void}
 *
 * Should any of these really be global variables?
 * @global {Number} ox
 * @global {Number} oy
 * @global {Number} xe
 * @global {Number} ye
 * @global {Number} xm
 * @global {Number} ym
 */
function ellipseDraw(ctx, cc, cb, xx, yy, ww, hh, ee) {
  if (cc != '') {
    this.context.strokeStyle = cc;
  }
  if (cb != '') {
    this.context.fillStyle = cb;
  }
  if (ee) {
    this.context.fillStyle = this.context.strokeStyle = cc;
  }

  //recalculating against limits
  if (ww < 0) {
    xx = xx + ww;
    ww = -ww
  }
  ;
  if (hh < 0) {
    yy = yy + hh;
    hh = -hh
  }
  ;
  var wx, hy; //calulated left and bottom side
  if (xx < this.draw_limit[0]) {
    wx = xx + ww;
    xx = this.draw_limit[0];
    ww = wx - xx;
    if (ww < 0) {
      ww = 0;
    }
  }
  if (xx > this.draw_limit[2]) {
    xx = this.draw_limit[2];
  }
  if (yy < this.draw_limit[1]) {
    hy = yy + hh;
    yy = this.draw_limit[1];
    hh = hy - yy;
    if (hh < 0) {
      hh = 0;
    }
  }
  if (yy > this.draw_limit[3]) {
    yy = this.draw_limit[3];
  }

  if ((xx + ww) > this.draw_limit[2]) {
    ww = this.draw_limit[2] - xx;
  }
  if ((yy + hh) > this.draw_limit[3]) {
    hh = this.draw_limit[3] - yy;
  }

  // Lets explain the maths...
  // Cubic Bezier curve
  // https://en.wikipedia.org/wiki/B%C3%A9zier_curve
  var kappa = .5522848;
  var ox = (ww / 2) * kappa,
          oy = (hh / 2) * kappa,
          xe = xx + ww,
          ye = yy + hh,
          xm = xx + ww / 2,
          ym = yy + hh / 2;

  this.context.beginPath();
  this.context.moveTo(xx, ym);
  this.context.bezierCurveTo(xx, ym - oy, xm - ox, yy, xm, yy);
  this.context.bezierCurveTo(xm + ox, yy, xe, ym - oy, xe, ym);
  this.context.bezierCurveTo(xe, ym + oy, xm + ox, ye, xm, ye);
  this.context.bezierCurveTo(xm - ox, ye, xx, ym + oy, xx, ym);
  this.context.closePath();
  this.context.stroke();
  if (cb != '') {
    this.context.fill();
  }

  if (ee) {
    // A bounding box for the elipse?
    this.context.globalAlpha = 1;
    this.edtDot(ctx, cb, xx, yy, 3);
    this.edtDot(ctx, cb, xx + ww, yy, 3);
    this.edtDot(ctx, cb, xx, yy + hh, 3);
    this.edtDot(ctx, cb, xx + ww, yy + hh, 3);
  }
}

/**
 * draws rectangle line/fill colour, start, length/width, is_filled
 * 
 * @param {type} ctx
 * @param {color|gradient|pattern} cc Describes how the border should look
 * @param {color|gradient|pattern} cb Describes how the fill should look
 * @param {Integer} xx x-position the user started the rectangle.
 * @param {Integer} yy y-position the user started the rectangle.
 * @param {Integer} ww the distance on the x-axis the users dragged.
 * @param {Integer} hh the distance on the y-axis the users dragged.
 * @param {type} ee flag to make fill of the ellipse the same colour as the border - for testing purposes
 * @returns {void}
 */
function rectDraw(ctx, cc, cb, xx, yy, ww, hh, ee) {

  if (cc != '') {
    this.context.strokeStyle = cc;
  }
  if (cb != '') {
    this.context.fillStyle = cb;
  }
  if (ee) {
    this.context.fillStyle = this.context.strokeStyle = cc;
  }

  //recalculating against limits
  if (ww < 0) {
    xx = xx + ww;
    ww = -ww
  }
  ;
  if (hh < 0) {
    yy = yy + hh;
    hh = -hh
  }
  ;

  var wx, hy; //calulated left and bottom side
  if (xx < this.draw_limit[0]) {
    wx = xx + ww;
    xx = this.draw_limit[0];
    ww = wx - xx;
    if (ww < 0) {
      ww = 0;
    }
  }
  if (xx > this.draw_limit[2]) {
    xx = this.draw_limit[2];
  }
  if (yy < this.draw_limit[1]) {
    hy = yy + hh;
    yy = this.draw_limit[1];
    hh = hy - yy;
    if (hh < 0) {
      hh = 0;
    }
  }
  if (yy > this.draw_limit[3]) {
    yy = this.draw_limit[3];
  }

  if ((xx + ww) > this.draw_limit[2]) {
    ww = this.draw_limit[2] - xx;
  }
  if ((yy + hh) > this.draw_limit[3]) {
    hh = this.draw_limit[3] - yy;
  }

  this.context.strokeRect(xx, yy, ww, hh);
  if (cb != '') {
    this.context.fillRect(xx, yy, ww, hh);
  }
  if (ee) {
    // Draws a dot on each corner of the rectangle.
    this.context.globalAlpha = 1;
    this.edtDot(ctx, cb, xx, yy, 3);
    this.edtDot(ctx, cb, xx + ww, yy, 3);
    this.edtDot(ctx, cb, xx, yy + hh, 3);
    this.edtDot(ctx, cb, xx + ww, yy + hh, 3);
  }
}

/**
 * draws polygon in different modes
 * 
 * @param {type} ctx context
 * @param {color|gradient|pattern} cc stroke colour (in some modes is also the fill)
 * @param {color|gradient|pattern} cb fill colour (only used in some modes)
 * @param {Integer} xx relative x
 * @param {Integer} yy relative y
 * @param {Array} pp Array of points in hex. Even keys are x values, odd keys are y values.
 * @param {String} mode The mode that the polygon should be drwan in.
 * @returns {polyDrawH}
 *
 * @global {type} any_overlaping ?
 * 
 * Are these all accidental globals?
 * @global {type} dx
 * @global {type} dy
 * @global {type} distn1
 * @global {type} distn2
 * @global {type} distn3
 * @global {type} distn4
 * @global {type} pos1
 * @global {type} pos2
 * @global {type} pos3
 * @global {type} pos4
 */
function polyDrawH(ctx, cc, cb, xx, yy, pp, mode) {
  /*
   this.context - this.canvas this.context
   cc - stroke colour
   cb - fill colour
   xx - relative x
   yy - relative y
   pp - array of points in hex

   mode:
   t - test the area only
   a - test with active elements
   h - show with black/white handlers
   e - show coloured without handlers
   f - show coloured with handlers
   d - show with green dot at the start and without handlers
   */
  if (cc != '') {
    this.context.strokeStyle = cc;
  }
  if (cb != '') {
    this.context.fillStyle = cb;
  }
  if (mode == 'e' || mode == 'r' || mode == 'f' || mode == 't') {
    this.context.fillStyle = this.context.strokeStyle = cc;
  }

  var tpe = new Array(); //array of line equations for polygons
  var tpi = new Array(); //array of line interconnections
  var qq = new Array(); //corrected
  var templw = this.context.lineWidth;
  // Radius of 'dots'
  var d1 = 3.5;
  // Diameter of 'dots'.
  var d2 = 7;
  var int_count = 0;
  this.context.lineJoin = "round";
  this.context.lineCap = "round";
  //yy=yy-0.5;
  this.context.beginPath();
  var tx0, ty0, tx1, ty1, tx2, ty2, tx3, ty3, ta, tb;
  // Canvas uses an odd coordinate system that does not match up to screen pixels, but treats the top left of a screen pixel as
  // the centre of it's own pixels:
  // https://blog.idrsolutions.com/2012/09/handling-floating-point-coordinates-with-pixels-in-svg-html5-canvas/
  // seems to work this way in Chrome, Firefox and IE. Without the adjustment lines with an odd width (1, 3, 5 etc) will look odd,
  // with it I expect even widths will look odd, as they will be antialiased, even when vertical or horizontal.
  // Niko: to make the lines look sharp we need to add half of the pixel to coordinates
  tx2 = parseInt(pp[0].trim(), 16) + xx + 0.5;
  ty2 = parseInt(pp[1].trim(), 16) + yy + 0.5;
  if (this.draw_limit.length > 0 && tx2 < this.draw_limit[0]) {
    tx2 = this.draw_limit[0];
  }
  if (this.draw_limit.length > 0 && tx2 > this.draw_limit[2]) {
    tx2 = this.draw_limit[2];
  }
  qq.push(tx2);
  if (this.draw_limit.length > 0 && ty2 < this.draw_limit[1]) {
    ty2 = this.draw_limit[1];
  }
  if (this.draw_limit.length > 0 && ty2 > this.draw_limit[3]) {
    ty2 = this.draw_limit[3];
  }
  qq.push(ty2);

  // Store the starting coordinates so we can ensure this is a closed shape.
  tx0 = tx2;
  ty0 = ty2;
  // Set the point we should start drawing at.
  this.context.moveTo(tx0, ty0);

  // Nothing seems to change these in the loop, why do we need to store them? Niko: Indeed, maybe it should be moved to 855
  var css = this.context.strokeStyle;
  var cfs = this.context.fillStyle;
  for (var n = 1; n < pp.length / 2; n++) {
    this.context.strokeStyle = css;
    this.context.fillStyle = cfs;
    // Create a new 'line equation' record.
    tpe[n] = new Array();
    // Store the last coordinate to be generated, i.e. the from point.
    tx1 = tx2;
    ty1 = ty2;
    // Generate a new to point.
    tx2 = parseInt(pp[n * 2].trim(), 16) + 0.5 + xx
    ty2 = parseInt(pp[n * 2 + 1].trim(), 16) + 0.5 + yy;
    // Discard any points that are too close together.
    // If you have a whole serise of very close together points, even if overall they move a long way
    // it looks as though this code would cause them all to be discarded.
    // i.e. if the coordinates are: 1,1 1,2 1,3, 1,4 1,5 1,6 1,7 1,8 1,9 ... then only 1,1 will be stored.
    // Should we only save the t.2 values into the t.1 variables if the current t.2 values are used?
    if (Math.abs(tx2 - tx1) > 3 || Math.abs(ty2 - ty1) > 3) {
      // Test points against limits.
      if (this.draw_limit.length > 0 && tx2 < this.draw_limit[0]) {
        tx2 = this.draw_limit[0];
      }
      if (this.draw_limit.length > 0 && tx2 > this.draw_limit[2]) {
        tx2 = this.draw_limit[2];
      }
      qq.push(tx2);
      if (this.draw_limit.length > 0 && ty2 < this.draw_limit[1]) {
        ty2 = this.draw_limit[1];
      }
      if (this.draw_limit.length > 0 && ty2 > this.draw_limit[3]) {
        ty2 = this.draw_limit[3];
      }
      qq.push(ty2);

      //calculate and record line coords and equation
      ta = 0; // Slope of the line, 0 implies horizontal rather than vertical though (which is what this default case should be covering)
      tb = tx2; // The y-intercept, the value on the y-axis when the line crosses 0 in the x-axis. Seems like an incorrect default.
      if (tx2 != tx1) {
        // The x-coordinates matching will cause a divide by zero.
        // Calculate the slope of the line.
        ta = (ty2 - ty1) / (tx2 - tx1);
        // The equation of a line can be expressed as: 
        // y = mx + b
        // where m is the slope and b is the y-intercept
        //
        // This can be rearranged to:
        //
        // b = y - mx
        //
        // http://www.purplemath.com/modules/strtlneq.htm
        // http://www.coolmath.com/algebra/08-lines/11-finding-equation-line-point-slope-01
        // https://www.mathsisfun.com/equation_of_line.html
        tb = ty1 - ta * tx1;
      }
      // Store the start point.
      tpe[n][0] = tx1;
      tpe[n][1] = ty1;
      // Store the end point.
      tpe[n][2] = tx2;
      tpe[n][3] = ty2;

      // Store the slope equation.
      tpe[n][4] = ta;
      // Store the y-intercept.
      tpe[n][5] = tb;
      tpe[n][6] = ''; // x of intersection(s)
      tpe[n][7] = ''; // y of intersection(s)

      this.context.lineTo(tx2, ty2);
      //test lines intersections
      // Reading on calculating intersections: https://en.wikipedia.org/wiki/Line%E2%80%93line_intersection
      if (n > 1) {
        // Loop through all the previous lines we have made.
        for (var m = 1; m < n; m++) {
          // Check if the lines are parallel.
          if (tpe[m][4] != ta) {
            // The lines are not parallel.
            //
            // Now work out the x-cordinate of the point the two lines meet using:
            // x = (d - c) / (a - b)
            // where:
            // a is slope of line 1
            // b is slope of line 2
            // c is y-intercept of line 1
            // d is y-intercept of line 2
            tx3 = (tb - tpe[m][5]) / (tpe[m][4] - ta);
            if (tx1 == tx2) {
              // ignore the calculated value if the line being looked at is
              // vertical as the data stores will be wrong...
              // Should this be the same if the stored point is vertical?
              tx3 = tx1;
            }
            // The y-coordinate of the intersection can be worked out using:
            // y = (a * ((d - c) / (a - b))) + c
            // The meanings are the same as described above.
            //
            // Since we have already worked out part of it we can use the value we have already calculated.
            // y = (a * tx3) + c
            ty3 = tpe[m][4] * tx3 + tpe[m][5];
            // Store the point at which the two lines intersect, if they have an infinate length.
            // It is not guarenteed to be within the area that the line is drawn...
            tpe[m][6] += ',' + tx3;
            tpe[m][7] += ',' + ty3;

            //distances between points (using Pythageras' therum)
            // Distance between the intersect point and the start point of the previous line.
            var dx = tx3 - tpe[m][0];
            var dy = ty3 - tpe[m][1];
            var distn1 = Math.sqrt(dx * dx + dy * dy);
            // Distance between the intersect point and the end point of the previous line.
            dx = tx3 - tpe[m][2];
            dy = ty3 - tpe[m][3];
            var distn2 = Math.sqrt(dx * dx + dy * dy);
            // Distance between the intersect point and the start point of the new line.
            dx = tx3 - tpe[n][0];
            dy = ty3 - tpe[n][1];
            var distn3 = Math.sqrt(dx * dx + dy * dy);
            // Distance between the intersect point and the end point of the new line.
            dx = tx3 - tpe[n][2];
            dy = ty3 - tpe[n][3];
            var distn4 = Math.sqrt(dx * dx + dy * dy);

            //order of point coordinats
            // This block appears to be taking the difference between points on a single axis
            // and doing the following:
            // difference between start and end - difference between start and intersect - difference between end and intersect
            // If the result of this is 0 then the intersect point must be between the start and end point on that axis.
            var pos1 = Math.abs(tpe[m][2] - tpe[m][0]) - Math.abs(tpe[m][2] - tx3) - Math.abs(tpe[m][0] - tx3);
            var pos2 = Math.abs(tpe[m][3] - tpe[m][1]) - Math.abs(tpe[m][3] - ty3) - Math.abs(tpe[m][1] - ty3);
            var pos3 = Math.abs(tpe[n][2] - tpe[n][0]) - Math.abs(tpe[n][2] - tx3) - Math.abs(tpe[n][0] - tx3);
            var pos4 = Math.abs(tpe[n][3] - tpe[n][1]) - Math.abs(tpe[n][3] - ty3) - Math.abs(tpe[n][1] - ty3);

            if (pos1 == 0 && pos2 == 0 && pos3 == 0 && pos4 == 0 && distn1 > 1 && distn2 > 1 && distn3 > 1 && distn4 > 1) {
              // The interect point must be between all the coordinates of the two lines, 
              // since we know that the lines to intersect we also now know that the intersection 
              // occurs where the lines are visible.
              // 
              // The distance calculations mean that the intersection must not be at the end 
              // of a line, but through the middle somewhere.
              //
              // Now record an interconnection.
              tpi[++int_count] = new Array();
              tpi[int_count][0] = tx3;
              tpi[int_count][1] = ty3;
            }
          }
        }
      }
    }
  }

  if (mode != 'd') {
    // Set the ent point of the line to the start coordinates.
    this.context.lineTo(tx0, ty0);
  }
  if (mode != 't') {
    // Draw the outline of the shape.
    this.context.stroke();
  }
  if (cb != '') {
    // Fill in the shape.
    this.context.fill();
  }

  //green dot for area  
  if (mode == 'd') {
    this.context.lineWidth = 1;
    this.context.globalAlpha = 0.75;
    // Why use the ellipse method, rather than the dot method? Niko: To my knowledge there is no dot method in HTML5?
    this.ellipseDraw(ctx, '#00ff00', '#00ff00', tx0 - d1, ty0 - d1, d2, d2, false);
    this.context.globalAlpha = 1;
  }

  //duplicate first two to the end if not already there
  // Surely this is buggy and will not work if the last point is vertically alligned
  // with the first, i.e. has the same x-coridinate but a different y-coordinate.
  if (qq[0] != qq[qq.length - 2] && mode != 'd') {
    // Why does this not need the whole intersect stuff done in the loop above doing to it?
    qq.push(qq[0]);
    qq.push(qq[1]);
  }

  //draw handlers
  if (mode == 'h' || mode == 'f') {
    if (mode == 'h') {
      var lcc = '#000000';
      var lcb = '#ffffff';
    }
    if (mode == 'f') {
      lcc = lcb = cc;
    }
    this.context.globalAlpha = 1;
    this.context.lineWidth = 1;
    // What are we doing here?
    // We are looping through all the points and drawing shapes,
    // a dot halfway between points and a square at the point.
    // What is the overall purpose of this?
    // Niko: the square indicate the corner point that user can reposition
    // Niko: repositioning an oval creates a new corner point - this is the way to adjust the shape
    for (var n = 1; n < qq.length / 2; n++) {
      // Find the coordinate halfway between this this point and the previous one.
      var ttx = (qq[n * 2] - qq[n * 2 - 2]) / 2 + qq[n * 2 - 2];
      var tty = (qq[n * 2 + 1] - qq[n * 2 - 1]) / 2 + qq[n * 2 - 1];
      //edge dots
      // Why use an ellipse rather than the dot method? Niko: no dot method available, ellipse recommended by developers instead.
      // Create an 'edge dot' half way between the points.
      this.ellipseDraw(ctx, lcc, lcb, ttx - d1, tty - d1, d2, d2, false);
      //nod sqares
      // Create a 'nod square' on the current point.
      this.rectDraw(ctx, lcb, lcc, qq[n * 2] - d1, qq[n * 2 + 1] - d1, d2, d2, false)
    }
  }
  this.context.lineWidth = templw;

  //mark intersections
  if (mode != 'a' && mode != 't') {
    // For each intersection we recorded do:
    // What is the purpose of the points we are drawing here?
    // Niko: the purpose - to indicate unadvised intersections to users
    // hotspot.js line 1151: displays an error message:
    // $string['errormessage1'] = 'The polygon lines have overlapped.';
    // $string['errormessage2'] = 'This may result in holes in the hotspot and incorrect marking of student answers.';
    
    for (var m = 1; m < tpi.length; m++) {
      this.context.strokeStyle = '#ff0000';
      this.context.beginPath();
      // Draw a circle 3 pixels wide on the intersection.
      this.context.arc(tpi[m][0], tpi[m][1], 3, 0, Math.PI * 2, true);
      this.context.closePath();
      this.context.stroke();
      this.any_overlaping = true;
    }
  }
  // Reset the styles.
  this.context.strokeStyle = css;
  this.context.fillStyle = cfs;
}

/**
 * adds this menu icon to the this.buttonBox array of menuicons' parameters
 *
 * @param {String} name The path of the icon
 * @param {Integer} posx The x positoon of the icon, unless the name contains vert_
 * @param {Integer} posy The y positoon on the icon, unless the name contains vert_
 * @param {type} state ?
 * @param {type} set ?
 * @param {String} text
 * @param {String} tooltip
 * @returns {Number} The position for the next icon?
 * @global {Object} menuImages a global object of images in htm5.images.js
 */
function menuBuild_icons(name, posx, posy, state, set, text, tooltip) {
  var iposy = posy;
  var iposx = posx;
  // Get the image object from the named property. What happens if the name is not present? Probably some kind of error.
  var imgdata = menuImages[name];
  // Why add 2 to the width and 1 to the height?
  var iwidth = imgdata.width + 2;
  var iheight = imgdata.height + 1;
  if (name == 'toolbar/ico_drop.png') {
    // Why do this adjustment?
    iwidth = 12;
    iposx += -4;
  }
  // Why base positioning of icons based on a part of the file name? Why not just pass the correct value in the first palce?
  // Niko: for some reason (probably the same as 1/2 pixel coordinates) without that hack icons were displayed with "borderline shadow" - blured edges
  if (name.indexOf('vert_') > -1) {
    iposy = -1;
    iposx = posx - 1;
  }
  this.context.font = "13px Arial";
  var textWidth = this.context.measureText(text).width;

  // This seems a waste of time, since we are about to overwrite the value, why not just push the array we want to add?
  this.buttonBox.push(name);
  this.buttonBoxNames[name] = this.buttonBox.length - 1;
  // Should probably convert this over to the var = [value, value...] syntax for array creation.
  // Niko: Dunno
  this.buttonBox[this.buttonBox.length - 1] = new Array();
  this.buttonBox[this.buttonBox.length - 1][0] = name;
  this.buttonBox[this.buttonBox.length - 1][1] = iposx;
  this.buttonBox[this.buttonBox.length - 1][2] = iposy;
  var bpad = 2;
  if (text == '') {
    bpad = 0;
  }
  this.buttonBox[this.buttonBox.length - 1][3] = iwidth + textWidth + bpad * 2;
  this.buttonBox[this.buttonBox.length - 1][4] = iheight;
  this.buttonBox[this.buttonBox.length - 1][5] = state; //over state
  this.buttonBox[this.buttonBox.length - 1][6] = state; //away state
  this.buttonBox[this.buttonBox.length - 1][7] = set;
  this.buttonBox[this.buttonBox.length - 1][8] = text;
  this.buttonBox[this.buttonBox.length - 1][9] = tooltip;
  // Why set posx as you exit it's scope?
  return posx = iposx + iwidth + textWidth + bpad * 2;
}

/**
 * recreates menu based on this.buttonBox array data
 *
 * @param {type} ctx Not used
 * @param {type} bar Colour the background?
 * @returns {undefined}
 * @global {Object} menuImages a global object of images in htm5.images.js
 */
function menuRebuild(ctx, bar) {
  // Store the original values so the can be set back at the end of the method.
  var tmp_lw = this.context.lineWidth;
  var tmp_ss = this.context.strokeStyle;
  var tmp_fs = this.context.fillStyle;

  // Set the styleing for the menu.
  this.context.lineWidth = 1;
  this.context.strokeStyle = '#000088';
  this.context.fillStyle = '#FFFFFF';

  //toolbar background
  if (typeof (bar) == 'undefined') {
    bar = true;
  }
  if (bar) {
    this.context.fillRect(0, 0, this.canvas.width, 25);
  }
  // Draw the menu buttons.
  for (var n = 0; n < this.buttonBox.length; n++) {
    var state = this.buttonBox[n][5];
    var imgdata = menuImages[this.buttonBox[n][0]];
    //imgdatab = menuImages['toolbar/but_back'+state+'.png'];
    // Why resize the image?
    var iwidth = imgdata.width + 2;
    if (this.buttonBox[n][0] == 'toolbar/ico_drop.png') {
      // Why the exception for this width?
      iwidth = 12;
    }
    //button background
    // key 7 seems to be stored from a variable called set... what is the significance of '-'?
    if (state != 0 && this.buttonBox[n][7] != '-') {
      this.context.fillStyle = '#ffd389';
      if (state == 1) {
        // What does state 1 mean? i.e. why are we recolouring here?
        this.context.fillStyle = '#ffeab7';
      }
      this.context.fillRect(this.buttonBox[n][1], this.buttonBox[n][2], this.buttonBox[n][3] + 1, this.buttonBox[n][4] + 1);
    }
    var bpad = 1;
    if (this.buttonBox[n][8] == '') {
      bpad = 0;
    }
    this.context.drawImage(this.menu_img, imgdata.left, imgdata.top, imgdata.width, imgdata.height, this.buttonBox[n][1] + 1 + bpad, this.buttonBox[n][2] + 1, iwidth - 2, imgdata.height);
    if (this.buttonBox[n][8] != '') {
      // The button should have some text displayed.
      this.context.textAlign = "left";
      this.context.fillStyle = '#000000';
      this.context.font = "13px Arial";
      this.context.fillText(this.buttonBox[n][8], this.buttonBox[n][1] + 20 + bpad, this.buttonBox[n][2] + 15);
    }
  }
  // Reset back to previous values.
  this.context.lineWidth = tmp_lw;
  this.context.strokeStyle = tmp_ss;
  this.context.fillStyle = tmp_fs;
}

/**
 * Defines the positions of colours that are displayed
 * on the colour picker image
 * 
 * @returns {void}
 */
function def_colour_panel_parts() {
  var i;
  //defining panel's active parts
  this.panelActiveParts.push('toolbar/pan_colours.png'); // Not sure I get why this line is done yet...
  this.panelActiveParts['toolbar/pan_colours.png'] = new Array();
  var lw = 12;
  var lh = 18;
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][00 + i] = (i * lh + 1) + ',' + (7 + lw * 1);
  }
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][10 + i] = (i * lh + 1) + ',' + (15 + lw * 2);
  }
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][20 + i] = (i * lh + 1) + ',' + (15 + lw * 3);
  }
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][30 + i] = (i * lh + 1) + ',' + (15 + lw * 4);
  }
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][40 + i] = (i * lh + 1) + ',' + (15 + lw * 5);
  }
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][50 + i] = (i * lh + 1) + ',' + (15 + lw * 6);
  }
  for (i = 0; i < 10; i++) {
    this.panelActiveParts['toolbar/pan_colours.png'][60 + i] = (i * lh + 1) + ',' + (37 + lw * 7);
  }
}

/**
 * recreates line 2, letter 1 or colour 0 (signed by panel_code) panel with selection highlighted
 * 
 * @param {type} panelActiveParts
 * @param {type} panelBox
 * @param {String} but_name The name of a button
 * @param {String} pan_name Property name of an image
 * @param {int} panel_code What is a panel code?
 * @param {int} selection
 * @returns {menuRebuild_panel}
 * @global {Object} menuImages a global object of images in htm5.images.js
 * @global {type} lang_string presumably language strings... how is it set?
 */
function menuRebuild_panel(panelActiveParts, panelBox, but_name, pan_name, panel_code, selection) {
  // Get the button.
  var temp_but = this.buttonBox[this.buttonBoxNames[but_name]];
  var imgdata = menuImages[pan_name];
  this.context.lineWidth = 1;
  this.context.strokeStyle = '#000088';
  this.context.fillStyle = '#FFFFFF';

  if (temp_but[6] == 2) {
    // What is the significance of key 6 being 2?
    //Niko: key 6 is a indicator of "away state" 
    //Niko: 2 means the icon was clicked and connected panel (color/line/size...) should be displayed 
    //Niko: 1 means the mouse hovers over the icon - present hover state
    //Niko: 0 - icon is not active - panel is hidden
    //Niko so basicaly states: clicked, hover, iddle
    
    this.context.fillRect(temp_but[1] + 0.5, temp_but[2] + 25.5, imgdata.width, imgdata.height);
    var tx = 12;
    var ty = 12;
    var px = 3.5;
    var py = 28.5;

    if (panel_code == 1) {
      // ok, but why? What is special about this panel_code?
      //Niko: panel_code=0 is used by all colour panels - the highlight is in a shape of box with amber border
      //Niko: panel_code=1 is used by size panel - the highlight is in a shape of light amber box with no border 
      //Niko: panel_code=2 is used by line panel - the highlight is in a shape of light amber bar with no border       
      tx = 21;
      ty = 20;
      px = 0;
      py = 25;
    }
    if (panel_code == 2) {
      // Ditto.
      tx = 129;
      ty = 20;
      px = 0;
      py = 25;
    }

    //drawing the image of the panel for colour panel
    if (panel_code == 0) {
      // Select part of the combined menu image that should be used as an icon.
      this.context.drawImage(this.menu_img, imgdata.left, imgdata.top, imgdata.width, imgdata.height, temp_but[1], temp_but[2] + 25, imgdata.width, imgdata.height);

      var tmp_but_num = this.buttonBoxNames[but_name];
      panelBox[tmp_but_num][3] = temp_but[1];
      panelBox[tmp_but_num][4] = temp_but[2] + 25;

      this.context.textAlign = "left";
      this.context.fillStyle = '#00156E';
      this.context.font = "11px Arial";
      // What is the logic behind the y positioning of the text? I'm assuming there is an assumption about the size of the text? Niko: basically... yes
      this.context.fillText(lang_string['themecolours'], temp_but[1] + 5, temp_but[2] + 25 + 16);
      this.context.fillText(lang_string['standardcolours'], temp_but[1] + 5, temp_but[2] + 25 + 117);

      //building up the this.colorReference
      if (this.colorReference.length == 0 && pan_name == 'toolbar/pan_colours.png') {
        for (var n = 0; n < panelActiveParts[pan_name].length; n++) {
          var tpc = panelActiveParts[pan_name][n].split(',');
          // Get the colour of a pixel in the colour picker image.
          var timgd = this.context.getImageData(temp_but[1] + 1 * tpc[0] + 9, temp_but[2] + 25 + 1 * tpc[1] + 9, 1, 1);
          // The colour information is stored in an array with 4 keys:
          //  0 - Red
          //  1 - Green
          //  2 - Blue
          //  4 - Alpha
          //  All values will be in the range 0 - 255.
          var timgp = timgd.data;
          // A hex colour is stored as:
          // #RRGGBB
          // In hex each character represents 16 numbers, 0 - 15, represented 0 - F
          // Two digits can store 256 values, 16 * 16, 0 - 255, 00 - FF
          // A decimmalised Red code from this is therefor:
          // red * 16^4 = red * 65536
          // A decimalised green:
          // green * 16^2 = green * 256
          // A decimlised blue:
          // blue * 16^0 = blue * 1 = blue
          // If you then add all those values together you will then get 
          // the decimalised version of the colour code.
          // Here is has been written as:
          // (((red * 256) + green) * 256 ) + (1 * blue)
          // It could be simplified in its current form to:
          // (((red * 256) + green) * 256 ) + blue
          // or changed to:
          // red * 65536 + green * 256 + blue
          this.colorReference[n] = hexifycolour('' + ((timgp[0] * 256 + timgp[1]) * 256 + 1 * timgp[2]));
        }
      }
    }
    //solid option
    if (selection > -1 && panel_code > 0) {
      // What does it mean if we get into this code? Niko: see comment in line 1085
      var tp = panelActiveParts[pan_name][selection].split(',');
      this.context.fillStyle = '#ffd389';
      this.context.fillRect(temp_but[1] + 1 * tp[0] + 0.5, temp_but[2] + 25 + 1 * tp[1] + 0.5, tx, ty);
    }
    //soft option
    if (this.panelOptionOver > -1 && panel_code > 0) {
      // What does it mean if we get into this code? Niko: see comment in line 1085
      var tpc = panelActiveParts[pan_name][this.panelOptionOver].split(',');
      this.context.fillStyle = '#ffeab7';
      this.context.fillRect(temp_but[1] + 1 * tpc[0] + 0.5, temp_but[2] + 25 + 1 * tpc[1] + 0.5, tx, ty);
    }

    //drawing the image of the panel for lines and sizes
    if (panel_code > 0) {
      // Select part of the combined menu image that should be used as an icon.
      // We always seem to want to draw this image... why not simply do it before the 'if (panel_code == 0) {' line for the colour panel?
      this.context.drawImage(this.menu_img, imgdata.left, imgdata.top, imgdata.width, imgdata.height, temp_but[1], temp_but[2] + 25, imgdata.width, imgdata.height);
    }

    //solid option
    if (selection > -1 && panel_code == 0) {
      // What does it mean if we get into this code? Niko: see comment in line 1085
      var tp = panelActiveParts[pan_name][selection].split(',');
      if (panel_code == 0) {
        this.context.drawImage(this.menu_img, imgdata.left + tp[0] * 1 + 4.5, imgdata.top + tp[1] * 1 + 4.5, 12, 11, temp_but[1] + tp[0] * 1 + 4.5, temp_but[2] + tp[1] * 1 + 4.5 + 25, 11, 11);
      }
      this.context.strokeStyle = '#ffe294';
      this.context.strokeRect(temp_but[1] + 1 * tp[0] + px + 1, temp_but[2] + 1 * tp[1] + py + 1, tx - 1, ty - 1);
      this.context.strokeStyle = '#ee4810';
      this.context.strokeRect(temp_but[1] + 1 * tp[0] + px, temp_but[2] + 1 * tp[1] + py, tx + 1, ty + 1);
    }

    //soft option
    if (this.panelOptionOver > -1 && panel_code == 0) {
      // What does it mean if we get into this code? Niko: see comment in line 1085
      var tpc = panelActiveParts[pan_name][this.panelOptionOver].split(',');
      if (panel_code == 0 && typeof (tpc) != 'undefined') {
        this.context.drawImage(this.menu_img, imgdata.left + tpc[0] * 1 + 5.5, imgdata.top + tpc[1] * 1 + 5.5, 10, 10, temp_but[1] + tpc[0] * 1 + 4.5, temp_but[2] + tpc[1] * 1 + 4.5 + 25, 11, 11);
      }
      this.context.strokeStyle = '#ffe294';
      this.context.strokeRect(temp_but[1] + 1 * tpc[0] + px + 1, temp_but[2] + 1 * tpc[1] + py + 1, tx - 1, ty - 1);
      this.context.strokeStyle = '#f29436';
      this.context.strokeRect(temp_but[1] + 1 * tpc[0] + px, temp_but[2] + 1 * tpc[1] + py, tx + 1, ty + 1);

      //testing the colour
      var timgd = this.context.getImageData(temp_but[1] + 1 * tpc[0] + 9, temp_but[2] + 25 + 1 * tpc[1] + 9, 1, 1);
      var timgp = timgd.data;
      this.panelOverColour = hexifycolour('' + ((timgp[0] * 256 + timgp[1]) * 256 + 1 * timgp[2]));
    }
  }
}

/**
 * tests mouse actions against buttons from this.buttonBox
 *
 * @returns {void}
 */
function button_test() {
  var n;
  this.buttonClicked = -1;
  if (this.buttonOver != -1) {
    //double button? - what is a double button?
    // Niko: ... should be twin-button...
    // Niko: "ico_drop" is the half button next to those buttons that open panels (colour, line, size...)
    var m = n = this.buttonOver;
    if (this.buttonBox[n][0] == 'toolbar/ico_drop.png') {
      // Why? 
      // Niko: so here is just rerefferencing to the button on the left (-1)
      n = m - 1;
    }
    if (n < this.buttonBox.length - 1 && this.buttonBox[n + 1][0] == 'toolbar/ico_drop.png') {
      // Why do this if n is less than the last button? What is the point of m? It does not appear to be used...
      // Niko: those buttons had different style before - it was changed with v.6)
      // I think n - was refferencing to the main button and m to that additional?
      m = n + 1;
    }
    this.buttonClicked = this.buttonOver = n;

    // What are button sets?
    // Why do we set these values?
    // 
    // Niko: Two functionalities here:
    // In Labelling the value here is "a", "b" or "c" 
    //    and there ButtonSets are groups of buttons with option (radio button / group switching) functionality
    // In Hotspot it's "a" and functionality as above 
    //    or "-" which signifies checkbox is correction mode
    // In Area the value is "+" which signify sticky button functionality 
    //    or "-" which signify inactive button ("delete point" or "Clear all" when there is no shape drawn yet)

    //testing button sets
    var butSet = this.buttonBox[this.buttonOver][7];
    for (n = 0; n < this.buttonBox.length; n++) {
      if (butSet == this.buttonBox[n][7] && butSet != '' && butSet != '+') {
        this.buttonBox[n][5] = this.buttonBox[n][6] = 0;
      }
    }

    //press button in set
    if (butSet != '' && butSet != '+') {
      this.buttonBox[this.buttonOver][5] = this.buttonBox[this.buttonOver][6] = 2;
    }

    //switch buttons without sets
    if (butSet == '' || butSet == '+') {
      if (this.buttonBox[this.buttonClicked][6] == 2) {
        this.buttonBox[this.buttonClicked][5] = this.buttonBox[this.buttonClicked][6] = 0;
      } else {
        this.buttonBox[this.buttonClicked][5] = 2;
        this.buttonBox[this.buttonClicked][6] = 0;
        if (this.buttonBox[this.buttonClicked][7] == '+') {
          this.buttonBox[this.buttonClicked][6] = 2;
        }
      }
    }
  }
}

/**
 * builds messagebox with (x,y width and height) and 4x texts
 * 
 * @param {Number} mx x postion of ?
 * @param {Number} my y position of?
 * @param {Number} mw A width?
 * @param {Number} mh A height?
 * @param {String} txt1 The message?
 * @param {String} txt2 Text for a button
 * @param {String} txt3 Text for a button
 * @param {String} txt4 Text for a button... what is the difference between them?
 * @returns {void}
 * @global {Object} menuImages a global object of images in htm5.images.js
 */
function build_msgbox(mx, my, mw, mh, txt1, txt2, txt3, txt4) {
  //setting shadow
  this.context.shadowColor = '#555';
  this.context.shadowBlur = 4;
  this.context.shadowOffsetX = 2;
  this.context.shadowOffsetY = 2;

  this.rectDraw(this.context, '#aaaaaa', '#ffffff', mx, my, mw, mh, false);
  //resetting the shadow
  this.context.shadowColor = 'white';
  this.context.shadowBlur = 0;
  this.context.shadowOffsetX = 0;
  this.context.shadowOffsetY = 0;

  //msg text
  this.context.fillStyle = '#000000';
  this.context.textAlign = "center";
  var txt0 = txt1.split('|'); // | denotes a new paragraph?
  var posy = my + 25;
  for (var n = 0; n < txt0.length; n++) {
    this.context.font = "12px Arial";
    var wrapped = this.wrapText(txt0[n], mw - 20);
    this.fillWrappedText(this.context, wrapped[0], mx + mw / 2, posy);
    posy += wrapped[1] + 5;
  }

  //buttons 
  var imgdata = menuImages['toolbar/button.png'];
  //y
  if (txt2 != '') {
    this.context.drawImage(this.menu_img, imgdata.left + 1, imgdata.top, imgdata.width - 2, imgdata.height, mx + mw / 2 - imgdata.width / 2 - 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
    this.panel_buttons[1] = new Array('Y', mx + mw / 2 - imgdata.width / 2 - 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
    this.context.fillText(txt2, mx + mw / 2 - 40, my + mh - 20);
  }
  //n    
  if (txt3 != '') {
    this.context.drawImage(this.menu_img, imgdata.left + 1, imgdata.top, imgdata.width - 2, imgdata.height, mx + mw / 2 - imgdata.width / 2 + 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
    this.panel_buttons[0] = new Array('N', mx + mw / 2 - imgdata.width / 2 + 40, my + mh - 12 - imgdata.height, imgdata.width, imgdata.height);
    this.context.fillText(txt3, mx + mw / 2 + 40, my + mh - 20);
  }
  //n    
  if (txt4 != '') {
    var bw = 120;
    this.context.drawImage(this.menu_img, imgdata.left + 1, imgdata.top, 10, imgdata.height, mx + mw / 2 - bw / 2 - 10, my + mh - 12 - imgdata.height, 10, imgdata.height);
    this.context.drawImage(this.menu_img, imgdata.left + imgdata.width - 12, imgdata.top, 10, imgdata.height, mx + mw / 2 + bw / 2, my + mh - 12 - imgdata.height, 10, imgdata.height);
    this.context.drawImage(this.menu_img, imgdata.left + 10, imgdata.top, 10, imgdata.height, mx + mw / 2 - bw / 2, my + mh - 12 - imgdata.height, bw, imgdata.height);
    panel_buttons[0] = new Array('C', mx + mw / 2 - bw / 2 - 10, my + mh - 12 - imgdata.height, bw + 20, imgdata.height);
    this.context.fillText(txt4, mx + mw / 2, my + mh - 20);
  }
}

/**
 * builds tooltip
 *
 * @param {type} ctx
 * @param {Array} but
 * @returns {void}
 */
function tooltip_draw(ctx, but) {
  //tooltip
  if (typeof but != 'undefined' && but[5] == 1 && but[9] != '') {
    this.context.font = "12px Arial";
    var metrics = this.context.measureText(but[9]);

    //setting the shadow
    this.context.shadowColor = '#888';
    this.context.shadowBlur = 6;
    this.context.shadowOffsetX = 1;
    this.context.shadowOffsetY = 1;

    this.rectDraw(ctx, '#FFF', '#FFF', but[1] + 10.5, but[2] + 30.5, metrics.width + 5, 16);
    //resetting the shadow
    this.context.shadowColor = '#fff';
    this.context.shadowBlur = 0;
    this.context.shadowOffsetX = 0;
    this.context.shadowOffsetY = 0;

    this.context.fillStyle = '#000';
    this.context.textAlign = "left";
    this.context.fillText(but[9], but[1] + 13, but[2] + 42);
  }
}
