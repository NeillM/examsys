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
// Initialise question buttons.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['addquestionsbuttons', 'jquery'], function (BUTTONS, $) {
    var buttons = new BUTTONS();
    buttons.init();

    $('#button_unused').click(function() {
        buttons.buttonclick('unused','add_questions_list.php?type=unused');
    });

    $('#button_alphabetic').click(function() {
        buttons.buttonclick('alphabetic','add_questions_list.php?type=all');
    });

    $('#button_keywords').click(function() {
        buttons.buttonclick('keywords','add_questions_keywords_frame.php');
    });

    $('#button_status').click(function() {
        buttons.buttonclick('status','add_questions_by_status.php');
    });

    $('#button_papers').click(function() {
        buttons.buttonclick('papers','add_questions_paper_types.php');
    });

    $('#button_team').click(function() {
        if (!$(this).hasClass( "grey" )) {
            buttons.buttonclick('team', 'add_questions_team_list.php');
        }
    });

    $('#button_search').click(function() {
        buttons.buttonclick('search','add_questions_list_search.php');
    });
});
