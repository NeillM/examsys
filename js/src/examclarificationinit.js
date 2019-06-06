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
// Init exam clarification screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jquery'], function ($) {
    $(function () {
        var new_height = $(window).height() - 105;
        $('#msg').height(new_height);

        $('#myform').submit(function(e){
            triggerSave();
            if ($('#msg').val() == '') {
                $('.defaultSkin table.mceLayout').css('border-color', '#C00000');
                $('.defaultSkin table.mceLayout').css('box-shadow', '0 0 6px rgba(200, 0, 0, 0.85)');
                $('.defaultSkin table.mceLayout tr.mceFirst td').css('border-top-color', '#C00000');
                $('.defaultSkin table.mceLayout tr.mceLast td').css('border-bottom-color', '#C00000');
                return false;
            }
            e.preventDefault();
            $.ajax({
                url: 'do_exam_clarification.php',
                type: "post",
                data: $('#myform').serialize(),
                dataType: "json",
                success: function (data) {
                    if (data == 'SUCCESS') {
                        opener.location.reload();
                        window.close();
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert(textStatus);
                },
            });
        });
    });
});
