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
// Init fill in the blank question remark screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['list', 'form', 'alert', 'jquery'], function (LIST, FORM, ALERT, $) {
    var list = new LIST();
    var form = new FORM();

    var winH = $(window).height() - 160
    list.resizeList(winH);

    $(window).resize(function(){
        list.resizeList(winH);
    });

    var alert = new ALERT();
    $("#remarkform").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "do_blank_remark.php",
            type: "post",
            data: $('#remarkform').serialize(),
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

    $("input[id^=word]").click(function () {
        form.toggle($(this).attr('data-div'));
    });

    $(".cancel").click(function() {
        window.close();
    });
});
