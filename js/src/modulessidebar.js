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
//
// Modules list functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
//
define(['requireconfig.min', 'list', 'jquery'], function(config, LIST, $) {
    return function() {
        /**
         * Initialise module sidebar.
         */
        this.init = function() {
            var scope = this;
            var list = new LIST();
            list.init();

            $('#sms').click(function() {
                $('#sms').append('<img src="../artwork/working.gif" class="busyicon" />');
            });

            $('#sms2').click(function() {
                $('#sms2').append('<img src="../artwork/working.gif" class="busyicon" />');
            });

            $(".editmodule").click(function() {
                list.edit('./edit_module.php?moduleid=', $('#lineID').val());
            });

            $(".deletemodule").click(function() {
                scope.deleteModule();
            });

            $(".modulecohort").click(function() {
                scope.studentCohort();
            });

            $(".jumpmodule").click(function() {
                scope.jumpToModule();
            });
        };

        /**
         * Display module index screen.
         */
        this.jumpToModule = function() {
            window.location = config.cfgrootpath + '/module/index.php?module=' + $('#lineID').val();
        };

        /**
         * Display module student cohort.
         */
        this.studentCohort = function() {
            window.location = config.cfgrootpath + '/users/search.php?search_surname=&search_username=&student_id=&module=' + $('#lineID').val() + '&calendar_year=<?php echo $current_session ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_courseID=&tmp_yearID=';
        };

        /**
         * Open window to delete module.
         */
        this.deleteModule = function() {
            var notice=window.open("../delete/check_delete_module.php?idMod=" + $('#lineID').val() + "","notice","width=450,height=180,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            notice.moveTo(screen.width / 2 - 225, screen.height / 2 - 90);
            if (window.focus) {
                notice.focus();
            }
        };
    }
});