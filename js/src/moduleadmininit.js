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
// Initialise module page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['list', 'modulessidebar', 'jquery', 'jquerytablesorter'], function (LIST, MODULE, $) {
    var module = new MODULE();
    module.init();
    if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({
            sortList: [[0,0]]
        });
    }
    var list = new LIST();
    list.init();

    // Display sms sync options if available to module.
    $(".l").click(function(event) {
        var externalid = $(this).attr('data-externalid');
        var syncprevious = $(this).attr('data-syncprevious');

        if (externalid == '') {
            $('#syncoptions').hide();
        } else {
            $('#syncoptions').show();
        }

        $('#sms').attr('data-id', externalid);
        $('#sms2').attr('data-id', externalid);
        $('#sms3').attr('data-id', externalid);

        // Display previous year syn if enabled for the module.
        if (syncprevious == '1') {
            $('#sms3').show();
        } else {
            $('#sms3').hide();
        }
    });

    $(".l").dblclick(function() {
        list.edit('./edit_module.php?moduleid=', $(this).attr('id'));
    });
});