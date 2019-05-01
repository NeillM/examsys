/**
 * html5 question image map.
 *
 * This file is generated via grunt from /js/images/html5.template.js
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

/**
 * The location of the combined image relative to the root Rogo directory.
 *
 * @type {String}
 */
ROGO.html5.images.location = 'js/images/combined.png';

/**
 * The combined image.
 *
 * @type {Image}
 */
ROGO.html5.images.image = new Image();

/**
 * Loads the combined image.
 *
 * @return {void}
 */
ROGO.html5.images.load = function() {
  this.image = new Image();
  this.image.src = ROGO.html5.webroot + this.location;
  this.image.onload = function() {
    ROGO.log('HTML5 combined image loaded.', 'info');
    for (var i in ROGO.html5.questions) {
      ROGO.html5.questions[i].redraw();
    }
  };
};

/**
 * Defines the co-ordinates of images in the combined image
 *
 * To reduce the amount of network traffic by the html5 questions all
 * of the UI images have be combined into a single image. This object
 * maps the images name to it's location on the combined image.
 *
 * The combined image is /js/images/combined.png
 *
 * The individual images are in sub directories of /js/images/
 *
 * @type {Object}
 */
ROGO.html5.images.map = {
    'toolbar/back_h1.png': { left: 3, top: 290, width: 3, height: 35 },
    'toolbar/back_h2.png': { left: 0, top: 290, width: 3, height: 35 },
    'toolbar/but_back0.png': { left: 320, top: 141, width: 21, height: 20 },
    'toolbar/but_back1.png': { left: 343, top: 141, width: 2, height: 20 },
    'toolbar/but_back2.png': { left: 341, top: 141, width: 2, height: 20 },
    'toolbar/but_back_drop.png': { left: 0, top: 270, width: 13, height: 20 },
    'toolbar/button.png': { left: 87, top: 143, width: 53, height: 25 },
    'toolbar/combo.png': { left: 320, top: 161, width: 18, height: 20 },
    'toolbar/cur_cross.png': { left: 335, top: 235, width: 12, height: 11 },
    'toolbar/cur_draw.png': { left: 320, top: 235, width: 15, height: 15 },
    'toolbar/cur_erase.png': { left: 302, top: 168, width: 16, height: 13 },
    'toolbar/ico_area.png': { left: 320, top: 181, width: 19, height: 18 },
    'toolbar/ico_arrow.png': { left: 320, top: 199, width: 19, height: 18 },
    'toolbar/ico_bobble.png': { left: 320, top: 217, width: 19, height: 18 },
    'toolbar/ico_brush.png': { left: 140, top: 143, width: 19, height: 18 },
    'toolbar/ico_bucket.png': { left: 159, top: 143, width: 19, height: 18 },
    'toolbar/ico_check_off.png': { left: 178, top: 143, width: 19, height: 18 },
    'toolbar/ico_check_on.png': { left: 197, top: 143, width: 19, height: 18 },
    'toolbar/ico_cross_off.png': { left: 216, top: 143, width: 19, height: 18 },
    'toolbar/ico_cross_on.png': { left: 235, top: 143, width: 19, height: 18 },
    'toolbar/ico_drop.png': { left: 19, top: 252, width: 11, height: 18 },
    'toolbar/ico_ellipse.png': { left: 273, top: 143, width: 19, height: 18 },
    'toolbar/ico_erase.png': { left: 169, top: 168, width: 19, height: 18 },
    'toolbar/ico_erase_off.png': { left: 292, top: 143, width: 19, height: 18 },
    'toolbar/ico_help.png': { left: 188, top: 168, width: 19, height: 18 },
    'toolbar/ico_label.png': { left: 207, top: 168, width: 19, height: 18 },
    'toolbar/ico_letter.png': { left: 0, top: 252, width: 19, height: 18 },
    'toolbar/ico_line.png': { left: 245, top: 168, width: 19, height: 18 },
    'toolbar/ico_lines.png': { left: 264, top: 168, width: 19, height: 18 },
    'toolbar/ico_menu.png': { left: 283, top: 168, width: 19, height: 18 },
    'toolbar/ico_menu_off.png': { left: 254, top: 143, width: 19, height: 18 },
    'toolbar/ico_minus.png': { left: 106, top: 198, width: 19, height: 18 },
    'toolbar/ico_minus_off.png': { left: 87, top: 198, width: 19, height: 18 },
    'toolbar/ico_multiple.png': { left: 144, top: 198, width: 19, height: 18 },
    'toolbar/ico_multiple_off.png': { left: 125, top: 198, width: 19, height: 18 },
    'toolbar/ico_palette.png': { left: 163, top: 198, width: 19, height: 18 },
    'toolbar/ico_plus.png': { left: 201, top: 198, width: 19, height: 18 },
    'toolbar/ico_plus_off.png': { left: 182, top: 198, width: 19, height: 18 },
    'toolbar/ico_polygon.png': { left: 220, top: 198, width: 19, height: 18 },
    'toolbar/ico_rectangle.png': { left: 239, top: 198, width: 19, height: 18 },
    'toolbar/ico_resize.png': { left: 258, top: 198, width: 19, height: 18 },
    'toolbar/ico_single.png': { left: 277, top: 198, width: 19, height: 18 },
    'toolbar/ico_size.png': { left: 296, top: 198, width: 19, height: 18 },
    'toolbar/ico_tick.png': { left: 282, top: 230, width: 19, height: 18 },
    'toolbar/ico_tick_g.png': { left: 244, top: 230, width: 19, height: 18 },
    'toolbar/ico_tick_r.png': { left: 263, top: 230, width: 19, height: 18 },
    'toolbar/ico_warn.png': { left: 301, top: 230, width: 19, height: 18 },
    'toolbar/ico_zoom.png': { left: 226, top: 168, width: 19, height: 18 },
    'toolbar/loupe.png': { left: 0, top: 143, width: 87, height: 87 },
    'toolbar/pan_colours.png': { left: 0, top: 0, width: 184, height: 143 },
    'toolbar/pan_lines.png': { left: 184, top: 0, width: 136, height: 141 },
    'toolbar/pan_sizes.png': { left: 320, top: 0, width: 28, height: 141 },
    'toolbar/smoke.png': { left: 128, top: 168, width: 41, height: 30 },
    'toolbar/smoke_b.png': { left: 87, top: 168, width: 41, height: 30 },
    'toolbar/textbox.png': { left: 0, top: 230, width: 244, height: 22 },
    'toolbar/vert_0.png': { left: 6, top: 290, width: 2, height: 26 },
    'toolbar/vert_1.png': { left: 10, top: 290, width: 1, height: 26 },
    'toolbar/vert_2.png': { left: 8, top: 290, width: 2, height: 26 },
};
