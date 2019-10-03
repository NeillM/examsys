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
// Initialise review page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jquery', 'start', 'review', 'reference', 'jsxls'], function ($, START, REVIEW, REF, Jsxlx) {
    var review = new REVIEW();
    review.html5init();
    var ref = new REF();
    ref.init();
    var start = new START()
    $(function() {
        start.generatePaperCss();
        start.StartClock();

        var bidirectional = $('#dataset').attr('data-bidirectional');
        var id = $('#dataset').attr('data-id');
        var self = $('#dataset').attr('data-self');

        $('body').on('unload', function () {
            start.KillClock();
        });

        $('#qForm').submit(function (e) {
            review.checkcomments(e);
            if (!bidirectional) {
                var agree = confirm(Jsxlx.lang_string['confirmsubmit']);
                if (agree) {
                    $('body').css('cursor','wait');
                    return true;
                } else {
                    return false;
                }
            }
        });

        $(function() {
            $('.reveal').click(function() {
                $('.var').toggle();
                $('.value').toggle();
            });
        });

        $('#jumpscreen').change(function () {
            $('#button_pressed').val('jump_screen');
            $('#qForm').attr('action',"start.php?id=" + id + "&dont_record=true");
            $('#qForm').submit();
        });

        $('#previous').click(function() {
            $('body').css('cursor','wait');
            $('#qForm').attr('action', self + "?id=" + id);
        });

        $('#next').click(function() {
            $('body').css('cursor','wait');
        });

        $('#finish').click(function() {
            $('body').css('cursor','wait');
        });


        $('.act').click(function() {
            review.dismissItem($(this).attr('id'));
        });

        $('.inact').click(function() {
            review.dismissItem($(this).attr('id'));
        });
    });
});