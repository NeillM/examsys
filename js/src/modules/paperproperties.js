// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.
//
// Paper properties
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'alert', 'helplauncher', 'jquery', 'jqueryui'], function(JsXls, ALERT, HELPLAUNCHER, $) {
    return function () {
        /**
         * Get the paper metadata.
         */
        this.getMeta = function() {
            var scope = this;
            var mod_codes = '';

            // Generate a comma separated list of modules that are associated with the paper.
            $("input[name='mod[]']:checked").each(function(index, checkbox) {
                if (mod_codes == '') {
                    mod_codes = $(checkbox).val();
                } else {
                    mod_codes += ',' + $(checkbox).val();
                }
            });

            // Load metadata.
            $('#metadata_security>.fieldset-elements').load('getMetdataSecurity.php', 'modules=' + mod_codes + '&paperID=' + this.paperid + '&session=' + $('#session').val(), function() {
                    $('#meta_dropdown_no').attr('data-loaded', 1);
                    scope.enablesubmit();
                }
            );
            // Load reference list.
            $('#reference_list>.fieldset-elements').load('getAvailableRefMaterial.php', 'modules=' + mod_codes + '&paperID=' + this.paperid, function() {
                    $('#reference_no').attr('data-loaded', 1);
                    scope.enablesubmit();

                    // Ensure that any help links are made active.
                    $('#reference_list>.fieldset-elements .refmaterials').click(function (e) {
                        e.preventDefault();
                        HELPLAUNCHER.launchHelp(296, 'staff');
                    });
                }
            );
        };

        /**
         * Enable submit button only if metadata and reference list loaded.
         */
        this.enablesubmit = function() {
            if ($('#meta_dropdown_no').attr('data-loaded') == 1 && $('#reference_no').attr('data-loaded') == 1) {
                $("input[name='submit']").prop('disabled', false);
            }
        };

        this.checkForm = function() {
            var alert = new ALERT();

            // Get the availability dates.
            const fdatetime = Date.parse($('#fdate').val() + ' ' + $('#ftime').val());
            const tdatetime = Date.parse($('#tdate').val() + ' ' + $('#ttime').val());

            if (fdatetime > tdatetime) {
                alert.notification('availableerror');
                return false;
            }

            // Require at least one module the user can access is associated with the paper.
            if ($("input[name='mod[]']:checked").length === 0) {
                if ($('#paper_type').val() == '4') {
                    // OSCE stations have a different message when no modules are selected.
                    alert.notification('msg5');
                } else {
                    alert.notification('msg1');
                }
                return false;
            }

            if ($('#paper_type').val() == '2' && $('#remote_summative').is(':checked') == 0) {
                if ($('#fdate').val() != $('#tdate').val()) {
                    alert.notification('msg2');
                    return false;
                }

                if ($('#exam_duration_hours').val() === '' || $('#exam_duration_mins').val() === '') {
                    // Part of the duration is not set.
                    alert.notification('msg3');
                    return false;
                }

                // Check from time has been set.
                if (!$('#ftime').is(':disabled') && $('#ftime').val() === '') {
                    alert.notification('missingfromtime');
                    return false;
                }

                // Check to time has been set.
                if (!$('#ttime').is(':disabled') && $('#ttime').val() === '') {
                    alert.notification('missingtotime');
                    return false;
                }

                if (!$('#session').is(':disabled') && $('#session').val() == '') {
                    alert.notification('msg4');
                    return false;
                }
            }

            if ($('#paper_type').val() == '2') {
                // Calculate the minimum hour and minutes based on student accommodations.

                // The minimum availability is stored in minutes.
                let minavilability = $('#dataset').attr('data-minavail');

                // Difference in the availability dates in seconds.
                const availability = (tdatetime - fdatetime) / 1000;

                // Only do check if exam is within a day.
                if (availability <= 86400) {
                    if ((availability / 60) < minavilability) {
                        alert.notification('durationnotmet');
                        return false;
                    }
                }
            }

            if ($('#paper_type').val() == '4') {
                // OSCE Stations require a session is set.
                if ($('#session').val() == '') {
                    alert.notification('msg4');
                    return false;
                }
            }

            // There must be a deadline when an external examiner is set.
            if ($("input[name='examiner[]']:checked").length > 0) {
                if ($('#externaldeadline').val() == '') {
                    alert.notification('msg6');
                    return false;
                }
            }

            // The must be a deadline when internal reviewers are set.
            if ($("input[name='internal[]']:checked").length > 0) {
                if ($('#internaldeadline').val() == '') {
                    alert.notification('msg6a');
                    return false;
                }
            }
            return true;
        };

        /**
         * Change feedback options given paper type.
         */
        this.changeType = function() {
            if ($('#paper_type').val() == '0') {
                $('#answer-screen').show();
            } else {
                $('#answer-screen').hide();
            }
        };

        /**
         * Update the availability warning message.
         */
        this.updateAvailability = function () {
            var alert = new ALERT();
            $.ajax({
                url: $('#dataset').attr('data-rootpath') + '/paper/get_min_availability.php',
                type: "post",
                data: {paperid: this.paperid, exam_duration_hours: $('#exam_duration_hours').val(), exam_duration_mins: $('#exam_duration_mins').val()},
                dataType: "json",
                success: function (data) {
                    if (data[0] == 'SUCCESS') {
                        $('#paper-end>.form-help').html(JsXls.lang_string['minavailability'].replace('%s', data[1]));
                    }
                },
                error: function (xhr, textStatus) {
                    alert.plain(textStatus);
                },
            });
        };
    }
});
