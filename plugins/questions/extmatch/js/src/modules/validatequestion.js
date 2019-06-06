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
// Paper extact match question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jsxls', 'jquery', 'jqueryvalidate'], function(jsxls, $) {
    return function() {
        /**
         * Add exact match validation methods to jquery-validate.
         */
        this.init = function () {
            $('#edit_form').submit(function () {
                triggerSave();
            });
            $('#edit_form').validate({
                ignore: '',
                rules: {
                    leadin: 'required',
                    option_text1: 'required',
                    option_text2: 'required',
                    option_text3: 'required'
                },
                messages: {
                    leadin: jsxls.lang_string['enterleadin'],
                    option_text1: '<br />' + jsxls.lang_string['enteroptionshort'],
                    option_text2: '<br />' + jsxls.lang_string['enteroptionshort'],
                    option_text3: '<br />' + jsxls.lang_string['enteroptionshort']
                },
                errorPlacement: function (error, element) {
                    if (element.attr('name') == 'leadin') {
                        error.insertAfter('#leadin_parent');
                        $('#leadin_tbl').css({'border-color': '#C00000'});
                        $('#leadin_tbl').css({'box-shadow': '0 0 6px rgba(200, 0, 0, 0.85)'});
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function () {
                    alert(jsxls.lang_string['validationerror']);
                }
            });

            // Bit of a hack to get the options section to fit in.
            var extraWidth = 0;
            var img = $('#media0 img:first');
            if (img.length == 1) {
                if (img.width() > 820) {
                    extraWidth = img.width() - 820;
                }
            }
            var qh = $('#question-holder');
            qh.addClass('wide');
            if (extraWidth > 0) {
                qh.width(qh.width() + extraWidth);
            }
        };
    }
});