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
// Initialise map question page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['alert', 'questionmapping', 'jquery'], function (ALERT, MAPPING, $) {
    var mapping = new MAPPING();
    mapping.init();

    $('#theform').submit(function (e) {
        e.preventDefault();
        var alert = new ALERT();
        $.ajax({
            url: "do_map_question.php",
            type: "post",
            data: $('#theform').serialize(),
            dataType: "json",
            success: function (data) {
                if (data == 'SUCCESS') {
                    window.opener.location.reload();
                    window.close();
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                alert.plain(textStatus);
            },
        });
    });
});