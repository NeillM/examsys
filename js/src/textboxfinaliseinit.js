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
// Init text box finalise marks screen.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['textboxfinalise', 'jquery'], function (TEXTBOX, $) {
    const textbox = new TEXTBOX();
    // Check select all button if all primary mark radio buttons selected on load.
    textbox.selectall();

    // Highlight which mark is being used.
    $('td>input[type="radio"]:checked').parent().addClass('marked');

    $('td>select').each(function() {
        if ($(this).val() && $(this).val() != 'NULL') {
            $(this).parent().addClass('marked');
        }
    });

    /**
     * Actions to take when the marker selection has changed for a user.
     *
     * @param {jQuery} element
     * @param {String} name
     */
    function changeMarkerSelection(element, name) {
        let dropdownID = name.replace('mark', 'override');
        $("#" + dropdownID).val('NULL').parent().removeClass('marked');

        let primary =  $('#' + name + '-p');
        let secondary = $('#' + name + '-s');

        // Remove the existing marking labels.
        primary.parent().removeClass('marked');
        secondary.parent().removeClass('marked');

        if (element.prop('checked')) {
            element.parent().addClass('marked');
        } else {
            element.parent().removeClass('marked');
        }
    }

    /**
     * Actions to take when the mark override is set.
     *
     * @param {jQuery} element
     * @param {String} name
     */
    function changeMarkOverride(element, name) {
        // First remove the selector for primary and secondary marking.
        let radioID = name.replace('override', 'mark');

        let primary =  $('#' + radioID + '-p');
        let secondary = $('#' + radioID + '-s');
        primary.prop("checked", false).parent().removeClass('marked');
        secondary.prop("checked", false).parent().removeClass('marked');

        if (element.val() && element.val() !== 'NULL') {
            element.parent().addClass('marked');
            uncheckMassOptions();
        } else {
            element.parent().removeClass('marked');
        }
    }

    /**
     * Handles the Select primary marks radio button being changed.
     */
    function selectAllPrimaryMarks() {
        if ($(this).is(':checked')) {
            $(".primarychk").prop("checked", true);
            $(".primary").addClass('marked');
            $(".override").removeClass('marked');
            $(".secondary").removeClass('marked');
            $(".selectallprimary").prop("checked", true);
            $('.override-select').val('NULL');
        } else {
            $(".primarychk").prop("checked", false);
            $(".primary").removeClass('marked');
            $(".selectallprimary").prop("checked", false);
        }
    }

    /**
     * Handles the Select all matching radio button being changed.
     *
     * @param {Event} event
     */
    function selectAllMatchingMarks(event) {
        // Stop the button from submitting the form.
        event.preventDefault();

        $(".primarychk").each(function() {
            let primary = $(this);
            let name = primary.attr('name');
            let secondary = $('#' + name + '-s');
            let override = $('#' + name.replace('mark', 'override'));
            // Check against secondary
            let primaryVal = primary.val();
            let secondaryVal = secondary.val();
            if (secondaryVal == null || primaryVal === secondaryVal) {
                // Clear existing if selected
                secondary.prop("checked", false).parent().removeClass('marked');
                override.val('NULL').parent().removeClass('marked');

                // Mark the primary option as selected.
                primary.prop("checked", true);
                changeMarkerSelection(primary, name);
            }
        });

        textbox.selectall();
    }

    /**
     * Unchecks the select boxes for the options that mass change marking options.
     */
    function uncheckMassOptions() {
        $(".selectallprimary").prop("checked", false);
    }

    /* Add in event handlers. */

    $("input:radio").click(function() {
        changeMarkerSelection($(this), $(this).attr('name'));
    });

    // Override selected.
    $("select").change(function() {
        changeMarkOverride($(this), $(this).attr('name'));
    });

    // Select all primary marks radio buttons.
    $(".selectallprimary").change(selectAllPrimaryMarks);

    // Select primary mark where primary and secondary marks agree
    $(".selectallmatching").click(selectAllMatchingMarks);

    // Check select all button if all primary mark radio buttons selected.
    $(".primarychk").click(textbox.selectall);

    // Uncheck select all/select matching button if a secondary or override mark has been selected.
    $(".secondarychk").click(uncheckMassOptions);
});
