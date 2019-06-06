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
// Reference tab functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        /**
         * Initialise reference panel.
         */
        this.init = function() {
            var scope = this;
            this.changeRef($('#refpane').val());
            $('.refhead').click(function() {
                scope.changeRef($(this).attr('data-id'));
            });
        };

        /**
         * Change reference tab.
         * @param integer refID reference id
         */
        this.changeRef = function (refID) {
            $('#refpane').val(refID);
            var refcount = $('#paper').attr('data-refcount');
            this.resizeReference();
            var flag = 0;
            for (var i = 0; i < refcount; i++) {
                if (i == refID) {
                    $('#framecontent' + i).show();
                    $('#refhead' + i).css('top', (31 * i) + 'px');
                    flag = 1;
                } else {
                    $('#framecontent' + i).hide();
                    if (flag === 0) {
                        $('#refhead' + i).css('top', (31 * i) + 'px');
                    } else {
                        $('#refhead' + i).css('top', '');
                        $('#refhead' + i).css('bottom', ((refcount - (i + 1)) * 31) + 'px');
                    }
                }
            }
        };

        /**
         * Resize the reference panel.
         */
        this.resizeReference = function () {
           var  winH = $(window).height();
            var refcount = $('#paper').attr('data-refcount');
            if (refcount > 0) {
                $subtract = (31 * refcount) + 11;
                for (var i = 0; i < refcount; i++) {
                    $('#framecontent' + i).css('height', (winH - $subtract) + 'px');
                }
                var mainWidth = $('body').outerWidth() - $('#framecontent0').outerWidth(true);
                $('#maincontent').width(mainWidth);
                $('#maincontent').css('position', 'fixed');
                var maxwidth = $('#css').attr('data-max_ref_width');
                $('#maincontent').css('right', maxwidth + 1);
                $('.framecontent').width(maxwidth - 12);
                $('.refhead').width(maxwidth - 12);
            }
        };
    }
});