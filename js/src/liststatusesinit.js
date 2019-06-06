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
// Initialise question status page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['questionstatus', 'jquery', 'jqueryui'], function (STATUS, $) {
    var status = new STATUS();

    $.ajaxSetup({timeout: 3000});
    $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
        status.showReorderError();
    });

    $('#statuses').sortable({
        axis: 'y',
        update: function (event, ui) {
            $.post("../ajax/admin/update_status_order.php", {statuses: $('#statuses').sortable('serialize')})
                .done(status.reorderSuccess);
        }
    });

    $('body, #content').click(function() {
        status.deselLine;
        $('#menu1a').show();
        $('#menu1b').hide();
    });
    $('.selectable').click(status.selLine);
    $('.selectable').dblclick(function () {
        $(status).trigger('click');
        $('#menu1b .default a').trigger('click');
    });

    status.deselLine();
});