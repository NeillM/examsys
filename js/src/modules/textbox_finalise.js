// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.
//
// Textbox finalise marks helper js
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Check select all button.
         */
        this.selectall = function () {
            let total = 0;
            let count = 0;
            let matchingTotal = 0;
            let otherSelected = false;
            $(".primarychk").each(function () {
                let primary = $(this);
                let name = primary.attr('name');
                let overrideID = name.replace('mark', 'override');
                let secondary = $('#' + name + '-s');
                let override = $('#' + overrideID);
                let matching = secondary.val() === null || secondary.val() === primary.val();

                total++;
                if (matching) {
                    // We found that the prmary and secondary marker agree.
                    matchingTotal++
                }

                if (primary.is(':checked')) {
                    count++;
                    if (!matching) {
                        // A primary value that does not match the secondary value is selected.
                        otherSelected = true;
                    }
                } else if (secondary.is(':checked')) {
                    // A secondary value is selected.
                    otherSelected = true;
                } else if (override.val() !== 'NULL') {
                    // An override is selected.
                    otherSelected = true;
                }
            });
            if (count === total) {
                $(".selectallprimary").prop("checked", true);
            }
            if (matchingTotal > 0 && !otherSelected && count === matchingTotal) {
                $(".selectallmatching").prop("checked", true);
            } else {
                $(".selectallmatching").prop("checked", false);
            }
        };
    }
});
