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
// Updater functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2017 The University of Nottingham
//
define(['alert', 'jquery'], function(ALERT, $) {
    return function () {
        /**
         * Resize user answers list to fit window.
         */
        this.resizeList = function () {
            var winW = 630, winH = 460;
            if (document.body && document.body.offsetWidth) {
                winW = document.body.offsetWidth;
                winH = document.body.offsetHeight;
            }
            if (document.compatMode == 'CSS1Compat' && document.documentElement && document.documentElement.offsetWidth) {
                winW = document.documentElement.offsetWidth;
                winH = document.documentElement.offsetHeight;
            }
            if (window.innerWidth && window.innerHeight) {
                winW = window.innerWidth;
                winH = window.innerHeight;
            }
            winH -= 140;
            $('#list').height(winH);
        };

        /**
         * Save all mark changes to the database.
         */
        this.saveAllRows = function() {
            var scope = this;
            var alert = new ALERT();
            var nomarks = true;
            var partialmarks = false;
            var failsave = false;
            var networkfail = false;
            var saves = [];

            var save_mark = function (index, element) {
                var logID = $(element).data('logid');
                var newMark = $('input[name=mark_' + logID + ']:checked').val();
                var reason = $('#reason_' + logID).val();
                var logType = $('#log_type_' + logID).val();
                var userID = $('#user_id_' + logID).val();
                var row = $(element).parents('tr');
                var startValue = row.data('current');
                var startReason = row.data('reason');

                if (typeof newMark == 'undefined') {
                    partialmarks = true;
                } else if (startValue === newMark && startReason === reason) {
                    // The mark is unchanged we do not need to save.
                    nomarks = false;
                } else {
                    // Only save if the value has been changed.
                    nomarks = false;
                    var save = $.post('../ajax/reports/save_enhancedcalc_override.php',
                        {
                            log_id: logID,
                            user_id: userID,
                            q_id: $('#q_id').val(),
                            paper_id: $('#paper_id').val(),
                            marker_id: $('#marker_id').val(),
                            mark_type: newMark,
                            reason: reason,
                            log: logType
                        },
                        function (data) {
                            if (data != 'OK') {
                                failsave = true;
                                return false;
                            }
                            row.addClass('overridden');

                            // Update the saved values.
                            row.data('current', newMark);
                            row.data('reason', reason);
                        }
                    ).fail(function() {
                        networkfail = true;
                    });

                    // Store the promise.
                    saves.push(save);
                }
            };

            $('.save-row').each(save_mark);

            var display_errors = function() {
                // Display messages about connection errors after all of the requests have completed.
                if (failsave) {
                    alert.show('saveerror');
                }

                if (networkfail) {
                    alert.show('connectionerror');
                }
            }

            $.when.apply($, saves).then(display_errors).fail(display_errors);

            if (nomarks) {
                // No mark overrides were saved.
                alert.show('nomarkmsg');
            } else if (partialmarks) {
                // Some marks overrides were saved.
                alert.show('missingmarkmsg');
            }
        };
    }
});
