// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Configuration module.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'log'], function($, Log) {
    var config = $('#rogoconfig');
    var mathjax = 0;
    var three = 0;
    var editor = config.data('editor');
    var cfgrootpath = config.data('root');
    if (config.data('mathjax')) {
        mathjax = 1;
    }
    if (config.data('three')) {
        three = 1;
    }
    if (typeof editor === 'undefined') {
        editor = 'plain';
    }
    if (typeof cfgrootpath === 'undefined') {
        Log('Root path not set, using default', 'warn');
        cfgrootpath = '';
    }
    return {
        // Root path.
        cfgrootpath: cfgrootpath,
        // Mathjax enabled?
        mathjax: mathjax,
        // ThreeJS enabled?
        three: three,
        // Editor enabled?
        editor: editor
    }
});
