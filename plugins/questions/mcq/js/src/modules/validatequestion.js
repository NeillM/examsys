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
// Paper mcq question validation.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jsxls', 'jquery', 'jqueryvalidate'], function(jsxls, $) {
    return function() {
        /**
         * Add mcq validation methods to jquery-validate.
         */
        this.init = function () {
            $('#edit_form').submit(function () {
                triggerSave();
            });
            $('#edit_form').validate({
                ignore: '',
                rules: {
                    leadin: 'required',
                    option_text1: {
                        required: {
                            depends: function (element) {
                                return ($('#option_media1').val() == '' && ($('#existing_media1').length == 0 || $('#existing_media1').val() == ''));
                            }
                        }
                    },
                    option_text2: {
                        required: {
                            depends: function (element) {
                                return ($('#option_media2').val() == '' && ($('#existing_media2').length == 0 || $('#existing_media2').val() == ''));
                            }
                        }
                    }
                },
                messages: {
                    leadin: jsxls.lang_string['enterleadin'],
                    option_text1: '<br />' + jsxls.lang_string['enteroption'],
                    option_text2: '<br />' + jsxls.lang_string['enteroption']
                },
                errorPlacement: function (error, element) {
                    if (element.attr('name') == 'leadin') {
                        error.insertAfter('#leadin_parent');
                        $('#leadin_tbl').css({'border-color': '#C00000'});
                        $('#leadin_tbl').css({'box-shadow': '0 0 6px rgba(200, 0, 0, 0.85)'});
                    } else if (element.attr('name') == 'option_text1') {
                        error.insertAfter('#option_media1');
                    } else if (element.attr('name') == 'option_text2') {
                        error.insertAfter('#option_media2');
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function () {
                    alert(jsxls.lang_string['validationerror']);
                }
            });
        };
    }
});