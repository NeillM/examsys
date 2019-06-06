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
// Review page functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['alert', 'start', 'qarea', 'qhotspot', 'qlabelling', 'jquery'], function(ALERT, start, qarea, qhotspot, qlabelling, $) {
    return function() {
        /**
         * Initial html5 questions for paper review.
         */
        this.html5init = function () {
            var language = $('#dataset').attr('data-language');
            $("canvas[id^=canvas]").each(function() {
                switch ($(this).attr('class')) {
                    case 'labelling':
                        var label = new qlabelling();
                        label.setUpLabelling($(this).attr('data-qno'),
                            "flash" + $(this).attr('data-qno'),
                            language, $(this).attr('data-qmedia'),
                            $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "review");
                        break;
                    case 'hotspot':
                        var hotspot = new qhotspot();
                        hotspot.setUpHotspot($(this).attr('data-qno'),
                            "flash" + $(this).attr('data-qno'),
                            language, $(this).attr('data-qmedia'),
                            $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "review");
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
        };

        /**
         * Verify that a comment has been added if a rating has been supplied.
         * @param ojbect e event
         */
        this.checkcomments = function(e) {
            $('.commentsbox').each(function() {
                if ($(this).val() != '') {
                    var commentID = $(this).attr('id');
                    var commentNo = commentID.substr(11);
                    if ( $('input[name=exttype' + commentNo + ']:checked', '#qForm').val() == undefined) {
                        var alert = new ALERT();
                        alert.notification('selectradio', commentNo);
                        $('body').css('cursor','default');
                        e.preventDefault();
                    }
                }
            });
        };

        /**
         * Strikethrough an option in a question
         * @param string itemID selector
         */
        this.dismissItem = function(itemID) {
            if ($("#" + itemID).hasClass("act")) {
                $("#" + itemID).removeClass("act").addClass("inact");
            } else {
                $("#" + itemID).removeClass("inact").addClass("act");
            }
        }
    }
});