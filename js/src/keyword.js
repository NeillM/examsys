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
// Keyword form functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['alert', 'form', 'jquery'], function(ALERT, FORM, $) {
    return function () {
        /**
         * Notify user / stop submission if illegal character used in keyword.
         * @param integer codeID keypress id
         */
        this.illegalChar = function(codeID) {
            var alert = new ALERT();
            if (codeID == 35) {
                alert.notification('character', '#');
            } else if (codeID == 38) {
                alert.notification('character', '&');
            } else if (codeID == 59) {
                alert.notification('character', ';');
            } else if (codeID == 63) {
                alert.notification('character', '?');
            } else if (codeID == 64) {
                alert.notification('character', '@');
            } else if (codeID == 94) {
                alert.notification('character', '^');
            } else if (codeID == 126) {
                alert.notification('character', '~');
            } else if (codeID == 13) {
                document.myform.submit();
            }
        };
    }
});


