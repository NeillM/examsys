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
// Initialise paper finish page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['helplauncher', 'qarea', 'qhotspot', 'qlabelling', 'jquery'], function (HELPLAUNCHER, qarea, qhotspot, qlabelling, $) {
    $('#randomlink').click(function () {
        HELPLAUNCHER.launchHelp(43, 'student');
    });

    $('.raw_textarea').each(function() {
        var boxWidth = $(this).width();
        var boxHeight = $(this).height();

        var targetID = 'div_' + $(this).attr('id');

        $('#' + targetID).width(boxWidth);
        $('#' + targetID).height(boxHeight);
    });

    $('#close').click(function() {
        window.close();
    });

    if (window.opener == null) {
        $('#close').css('display','none');
    }

    var language = $('#dataset').attr('data-language');
    $("canvas[id^=canvas]").each(function() {
        switch ($(this).attr('class')) {
            case 'labelling':
                var label = new qlabelling();
                label.setUpLabelling($(this).attr('data-qno'),
                    "flash" + $(this).attr('data-qno'),
                    language, $(this).attr('data-qmedia'),
                    $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "script");
                break;
            case 'hotspot':
                var hotspot = new qhotspot();
                hotspot.setUpHotspot($(this).attr('data-qno'),
                    "flash" + $(this).attr('data-qno'),
                    language, $(this).attr('data-qmedia'),
                    $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "script");
                break;
            case 'area':
                var area = new qarea();
                area.setUpArea($(this).attr('data-qno'),
                    "flash" + $(this).attr('data-qno'),
                    language, $(this).attr('data-qmedia'),
                    $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "script");
                break;
            default:
                break;
        }
    });
});