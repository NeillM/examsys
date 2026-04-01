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
// Initialise paper properties page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['rogoconfig', 'paperproperties', 'datecopy', 'form', 'alert', 'jsxls', 'js_running', 'jquery', 'jqueryui'], function (Config, PROP, DATECOPY, FORM, ALERT, jsxls, JSRUNNING, $) {
    var properties = new PROP();

    properties.paperid = $('#dataset').attr('data-id');
    var type = $('#dataset').attr('data-type');

    var date = new DATECOPY();
    // Use on focus otherwise it can cause time to change when you do not want them to,
    // for example when using change() if the start time is 09:00 and in the end time you type 17:00
    // when you press the 1 key the start time is changed to 1 am.
    $('.datecopy').focusout(function () {
        var datecheck = false;
        if ($('#remote_summative').is(':checked')) {
            if (type == 5) {
                datecheck = true;
            }
        } else {
            if (type == 2 || type == 5) {
                datecheck = true;
            }
        }
        if (datecheck) {
            date.dateCopy(this);
        }
    });

    properties.getMeta();

    var form = new FORM();
    form.init();

    $('.toggle').click(function () {
        form.toggle($(this).attr('data-toggleid'));
    });

    $('#paper_type').click(function () {
        properties.changeType();
    });

    // The year selector.
    $('#session').click(function () {
        properties.getMeta();
    });

    // Module inputs.
    $("input[name='mod[]']").click(function () {
        properties.getMeta();
    });

    $('#theform').submit(function(e) {
        e.preventDefault();
        if (properties.checkForm()) {
            var alert = new ALERT();

            // Flag that we are making an AJAX call.
            const scriptname = 'paperpropertiesinit:form:submit';
            JSRUNNING.start(scriptname);

            $.ajax({
                url: 'update_properties.php',
                type: "post",
                data: $('#theform').serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.error) {
                        // Check if it's a field name and map to appropriate message
                        if (data.message === 'fyear' || data.message === 'fmonth' || data.message === 'fday' || data.message === 'fhour' || data.message === 'fminute') {
                            alert.notification('missingfromtime');
                        } else if (data.message === 'tyear' || data.message === 'tmonth' || data.message === 'tday' || data.message === 'thour' || data.message === 'tminute') {
                            alert.notification('missingtotime');
                        } else {
                            // Handle any other errors that are not covered by the specific cases above
                            var errorText = data.error;
                            if (data.message) {
                                errorText += ': ' + data.message;
                            }
                            alert.plain(errorText || jsxls.lang_string['papererrors']);
                        }
                        JSRUNNING.done(scriptname);
                        return;
                    }
                    if (data == 'SUCCESS') {
                        // We do not want to flag the script as not running before we have changed page.
                        window.location.href = Config.cfgrootpath  + '/paper/details.php?paperID=' + $('#dataset').attr('data-id');
                    } else if (data == 'DUPLICATE_TITLE') {
                        $('#papertitle').addClass('errfield');
                        properties.buttonclick('general','tab1');
                        JSRUNNING.done(scriptname);
                    }
                },
                error: function (xhr, textStatus) {
                    alert.plain(textStatus);
                    JSRUNNING.done(scriptname);
                },
            });
        }
    });

    $('#exam_duration_hours').change(function () {
        if (type == 2 && $('#exam_duration_hours').val() !== '' && $('#exam_duration_mins').val() !== '') {
            properties.updateAvailability();
        }
    });
    $('#exam_duration_mins').change(function () {
        if (type == 2 && $('#exam_duration_mins').val() !== '' && $('#exam_duration_hours').val() !== '') {
            properties.updateAvailability();
        }
    });

    // Disable labs if remote summative.
    if ($('#remote_summative').is(':checked')) {
        $("input[name=lab]", $("#labs_list")).each(function() {
            $(this).prop("disabled", true);
        });
    }
    $('#remote_summative').click(function () {
        if ($(this).is(':checked')) {
            $("input[name=lab]", $("#labs_list")).each(function() {
                $(this).prop("disabled", true);
            });
        } else {
            $("input[name=lab]", $("#labs_list")).each(function() {
                $(this).prop("disabled", false);
            });
        }
    });

    // Disable key input if not enabled.
    if ($('#seb_enabled').is(':checked')) {
        $('#seb_keys_text').prop("disabled", false);
    } else {
        $('#seb_keys_text').prop("disabled", true);
    }
    $('#seb_enabled').click(function () {
        if ($(this).is(':checked')) {
            $('#seb_keys_text').prop("disabled", false);
        } else {
            $('#seb_keys_text').prop("disabled", true);
        }
    });

    // Check if user has permissions.
    if (!$('#dataset').attr('data-summativemanagment')) {
        $('#remote_summative').prop("disabled", true);
    }
});
