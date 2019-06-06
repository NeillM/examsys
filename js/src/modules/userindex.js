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
// User index functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Start the paper.
         */
        this.startPaper = function() {
            var paperURL = "../paper/start.php?id=" + $('#dataset').attr('data-id');

            if ($('#dataset').attr('data-mode') == 'preview') {
                paperURL += '&mode=preview';
            }

            if ($('#dataset').attr('data-fullscreen')) {
                var exam = window.open(paperURL,"paper","fullscreen=" + this.fullscreen + ",width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,menubar=no,titlebar=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes");
                if (window.focus) {
                    exam.focus();
                }
            } else {
                window.location = paperURL;
            }
        };

        /**
         * Review paper feedback of previous attempts.
         * @param integer metadataID identifier of attempt on paper.
         * @param integer type type of paper
         */
        this.reviewPaper = function(metadataID, type) {
            var exam = window.open("finish.php?id=" + $('#dataset').attr('data-id') + "&metadataID="+metadataID+"&log_type="+type+"","paper","fullscreen=" + this.fullscreen + ",width="+(screen.width-80)+",height="+(screen.height-80)+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                exam.focus();
            }
        };
    }
});
