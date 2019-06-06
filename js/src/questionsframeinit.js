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
// Initialise questions frame.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery'], function ($) {
    $(function () {
        $("#qbuttons").load("add_questions_buttons.php");
        var paperid = $("#dataset").attr('data-paperid');
        var module = $("#dataset").attr('data-module');
        var folder = $("#dataset").attr('data-folder');
        var disp = $("#dataset").attr('data-disp');
        var srcofy = $("#dataset").attr('data-srcofy');
        var max = $("#dataset").attr('data-max');
        $("#controls").load("add_question_controls.php?paperID=" + paperid
            + "&module=" + module + "&folder=" + folder + "&display_pos=" + disp + "&scrOfY=" + srcofy + "&max_screen=" + max);
    });
});
