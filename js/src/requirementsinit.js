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
// Initialise requirements page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery'], function ($) {
    $(function () {
        $("#requirementslist").load("../requirements/requirements.php");
    });

    // Load upgrade screen.
    $('body').on('click', '#update', function(){
        window.location='../updates/upgrade.php';
    });

    // Load install screen.
    $('body').on('click', '#install', function(){
        window.location='install.php';
    });
});