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
// Frequency discrimination analysis functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define(['jquery'], function ($) {
    return function() {
        /**
         * Toggle question exclusion.
         * @param integer qID question id
         * @param integer parts number of question parts
         * @param integer marks number of question marks
         */
        this.toggle = function(qID, parts, marks) {
            var $statusInput = $('#status_' + qID);
            var $checkbox = $('#checkbox_' + qID);
            var isExcluded = $statusInput.val().charAt(0) === '1';

            // Update the checkbox state
            isExcluded = !isExcluded;
            $checkbox.prop('checked', isExcluded);

            // marks is at least 1 to handle questions with no question options
            marks = Math.max(1, marks);

            // Update the hidden input containing marks
            var new_value = isExcluded ? '1'.repeat(marks) : '0'.repeat(marks);
            $statusInput.val(new_value);

            // Update the excluded class on related elements
            for (var i = 1; i <= parts; i++) {
                var $part = $('#q_' + qID + '_' + i);
                if (isExcluded) {
                    $part.addClass('excluded');
                } else {
                    $part.removeClass('excluded');
                }
            }
        };

        /**
         * Open fill in the blank remark window.
         * @param integer q_id question id
         * @param integer part_no question part id
         * @returns bool
         */
        this.blankCorrect = function(q_id, part_no) {
            var paperid = $('#dataset').attr('data-id');
            var startdate = $('#dataset').attr('data-startdate');
            var enddate = $('#dataset').attr('data-enddate');
            var studentsonly = $('#dataset').attr('data-studentsonly');
            window.open("blank_remark.php?q_id=" + q_id + "&blank=" + part_no + "&paperID=" + paperid + "&startdate=" + startdate + "&enddate=" + enddate + "&studentsonly=" + studentsonly,"remark","width=500,height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            return false;
        };

        /**
         * Open calculation question remark window.
         * @param integer q_id question id
         * @returns bool
         */
        this.calcCorrect = function(q_id) {
            var paperid = $('#dataset').attr('data-id');
            var startdate = $('#dataset').attr('data-startdate');
            var enddate = $('#dataset').attr('data-enddate');
            var studentsonly = $('#dataset').attr('data-studentsonly');
            window.open("enhanced_calc_remark.php?q_id=" + q_id + "&paperID=" + paperid + "&startdate=" + startdate + "&enddate=" + enddate + "&studentsonly=" + studentsonly,"remark","width=850,height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            return false;
        };
    }
});