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
// Initialise std set review page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['start', 'stdsetreview', 'state', 'jquery'], function (START, STD, STATE, $) {
    var std = new STD();
    var state = new STATE();
    state.init();

    $('.reveal').click(function() {
        $('.var').toggle();
        $('.value').toggle();
    });

    var method = $('#dataset').attr('data-method');
    $(function() {
        if (method == 'ebel') {
            std.recountCategories();
        }
    });

    $('.recountcat').change(function() {
        if (method == 'ebel') {
            std.recountCategories();
        }
    });

    var start = new START();
    start.html5init();
});