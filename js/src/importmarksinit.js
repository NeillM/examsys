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
// Initialise ocse marks, page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['papersidebar', 'jquery'], function (SIDEBAR, $) {
    $(function() {
        var sidebar = new SIDEBAR();
        sidebar.init();

        $('#submit').click(function () {
            window.location='../paper/details.php?paperID=' + $('#dataset').attr('data-paperid') + '&folder=' + $('#dataset').attr('data-folder') + '&module=' + $('#dataset').attr('data-module');
        });
    });
});