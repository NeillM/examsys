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
// User details functions.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jquery'], function($) {
    return function () {
        /**
         * Open a window to edit the user details.
         */
        this.editDetails = function() {
            var editwin = window.open("edit_details.php?userID=" + $('#dataset').attr('data-userid'),"edituser","width=600,height=450,left="+(screen.width/2-260)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Display selected tab and hide the rest.
         * @param string tabID tab identifier
         */
        this.showTab = function(tabID) {
            $('#Log_tab').hide();
            $('#Modules_tab').hide();
            $('#Admin_tab').hide();
            $('#Notes_tab').hide();
            $('#Accessibility_tab').hide();
            $('#Teams_tab').hide();
            $('#Metadata_tab').hide();
            $('#roles_tab').hide();
            $('#' + tabID).show();
        };

        /**
         * Open a window to add a student note.
         */
        this.newStudentNote = function() {
            var note = window.open("new_student_note.php?userID="  + $('#dataset').attr('data-userid'),"note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                note.focus();
            }
        };

        /**
         * Open a window to edit the modules a user is enrol on.
         * @param integer session the academic session
         * @param string grade the users course
         * @param intger searchsid the student identifier
         * @param string searchusername the useranme
         * @param string searchsurname the surname
         */
        this.editModules = function(session, grade, searchsid, searchusername, searchsurname) {
            var student = "&student_id=" + searchsid;
            var username = "&search_username=" + searchusername;
            var surname = "&search_surname=" + searchsurname;
            var editwin = window.open("edit_modules_popup.php?userID="  + $('#dataset').attr('data-userid') + student + username + surname + "&session=" + session + "&grade=" + grade + "","editmodule","width=650,height=750,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Open window to edit staff members teams.
         * @returns bool
         */
        this.editMultiTeams = function() {
            var editwin = window.open("../module/edit_multi_teams_popup.php?userID=" + $('#dataset').attr('data-userid'),"editmodule","width=550,height=750,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
            return false;
        };

        /**
         * Open window to force reset the users password.
         */
        this.forceResetPassword = function() {
            var editwin = window.open("reset_pwd.php?userID=" + $('#dataset').attr('data-userid'),"editmodule","width=600,height=400,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Open window to email user password.
         */
        this.resetPassword = function() {
           var editwin = window.open("forgotten_password.php?email=" + $('#dataset').attr('data-email') + "","editmodule","width=600,height=400,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
            if (window.focus) {
                editwin.focus();
            }
        };

        /**
         * Update accessibility screen based on new choices.
         */
        this.updateAccessDemo = function() {
            var textsize = $('select[name="textsize"] option:selected').text();
            var textsizeval = $('select[name="textsize"] option:selected').val();
            if (textsizeval == 0) {
                textsize = '100%';
            }
            $('#demo_paper_background').css('font-size', textsize);

            var font = $('select[name="font"] option:selected').text();
            var fontval = $('select[name="font"] option:selected').val();
            if (fontval == '') {
                font = 'Arial';
            }
            $('#demo_paper_background').css('font-family', font);

            if ($("#bg_radio_on").is(':checked')) {
                $('#demo_paper_background').css('background-color', $('#span_background').css('background-color'));
            } else {
                $('#demo_paper_background').css('background-color', '#FFFFFF');
            }

            if ($("#fg_radio_on").is(':checked')) {
                $('#demo_paper_background').css('color', $('#span_foreground').css('background-color'));
            } else {
                $('#demo_paper_background').css('color', '#000000');
            }

            if ($("#theme_radio_on").is(':checked')) {
                $('#demo_theme').css('color', $('#span_themecolor').css('background-color'));
            } else {
                $('#demo_theme').css('color', '#316AC5');
            }

            if ($("#labels_radio_on").is(':checked')) {
                $('#demo_true_label').css('color', $('#span_labelcolor').css('background-color'));
                $('#demo_false_label').css('color', $('#span_labelcolor').css('background-color'));
                $('#demo_note').css('color', $('#span_labelcolor').css('background-color'));
            } else {
                $('#demo_true_label').css('color', '#C00000');
                $('#demo_false_label').css('color', '#C00000');
                $('#demo_note').css('color', '#C00000');
            }

            if ($("#unanswered_radio_on").is(':checked')) {
                $('#demo_unanswered').css('background-color', $('#span_unansweredcolor').css('background-color'));
            } else {
                $('#demo_unanswered').css('background-color', '#FFC0C0');
            }

            if ($("#marks_radio_on").is(':checked')) {
                $('#demo_marks').css('color', $('#span_marks_color').css('background-color'));
            } else {
                $('#demo_marks').css('color', '#808080');
            }

            if ($("#globalthemecolour_radio_on").is(':checked')) {
                $('#demo_header').css('background-color', $('#span_globalthemecolour').css('background-color'));
                $('#demo_footer').css('background-color', $('#span_globalthemecolour').css('background-color'));
            } else {
                $('#demo_header').css('background-color', '#5590CF');
                $('#demo_footer').css('background-color', '#5590CF');
            }
            
            if ($("#globalthemefontcolour_radio_on").is(':checked')) {
                $('#demo_header').css('color', $('#span_globalthemefontcolour').css('background-color'));
                $('#demo_footer').css('color', $('#span_globalthemefontcolour').css('background-color'));
            } else {
                $('#demo_header').css('color', '#FFFFFF')
                $('#demo_footer').css('color', '#FFFFFF')
            }
            if ($("#highlightcolour_radio_on").is(':checked')) {
                $('#demo_highlight').css('background-color', $('#span_highlightcolour').css('background-color'));
            } else {
                $('#demo_highlight').css('background-color', '#FCF6CF');
            }
        };
    }
});
