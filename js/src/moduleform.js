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
// Module form functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery', 'jqueryvalidate'], function($) {
    return function () {
        /**
         * Initialise the module add/edit form.
         */
        this.init = function () {

            $('#theform').validate({
                rules: {
                    fullname: {
                        required: true,
                        maxlength: 80
                    }
                }
            });

            $('form').removeAttr('novalidate');

            $('#stdset').click(function() {
                if ($('#stdset').prop('checked')) {
                    $('#ebelgrid').show();
                } else {
                    $('#ebelgrid').hide();
                }
            });

            $('#theform').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: $('#dataset').attr('data-posturl'),
                    type: "post",
                    data: $('#theform').serialize(),
                    dataType: "json",
                    success: function (data) {
                        if (data == 'SUCCESS') {
                            window.location = 'list_modules.php';
                        } else if (data != 'ERROR') {
                            $('#modulecode').addClass('errfield');
                            $('.form-error').show();
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        alert(textStatus);
                    },
                });
            });
        };

        /**
         * Enable sidebar options for edit screen.
         */
        this.setSidebarMenu = function() {
            $('#menu1a').hide();
            $('#menu1b').show();
            $('#lineID').val($('#dataset').attr('data-moduleid'));
        };
    }
});


