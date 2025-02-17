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
// Copy paper functionality
//
// @author Iyud Dissanayake
// @copyright Copyright (c) 2025 The University of Nottingham
//
define(['jquery'], function($) {
    return function() {
        var self = this;

       //DOM elements
        this.$form = $('#checkcopy');
        this.$paperType = $('#paper_type');
        this.$newPaper = $('#new_paper');
        this.$nextButton = $('#next_button');
        this.$submitButton = $('#submit_button');
        this.$cancelButton = $('#cancel');
        this.$summativeFields = $('#summative_fields');
        
        // Required fields for summative exams
        this.summativeRequiredFields = [
            'duration_hours',
            'duration_mins',
            'period',
            'cohort_size',
            'sittings'
        ];

        /**
         * Initialize the copy paper functionality
         */
        this.init = function() {
            this.bindEvents();
            this.updateButtons();
        };

        /**
         * Bind all event handlers
         */
        this.bindEvents = function() {
            // Handle paper type changes
            this.$paperType.on('change', function() {
                self.updateButtons();
            });

            // Handle next button click
            this.$nextButton.on('click', function() {
                self.handleNextClick();
            });

            // Handle form submission
            this.$form.on('submit', function() {
                return self.handleSubmit();
            });

            // Handle cancel button
            this.$cancelButton.on('click', function() {
                self.handleCancel();
            });
        };

        /**
         * Update button visibility based on paper type
         */
        this.updateButtons = function() {
            var isSummative = this.$paperType.val() === '2';

            if (isSummative) {
                this.$nextButton.show();
                this.$submitButton.hide();
                // Reset and hide summative fields when switching back to summative
                this.$summativeFields.hide();
            } else {
                this.$nextButton.hide();
                this.$submitButton.show();
                this.$summativeFields.hide();
            }
        };

        /**
         * Handle click on the next button
         */
        this.handleNextClick = function() {
            // Validate paper name
            if (!this.$newPaper.val().trim()) {
                alert('Please enter a paper name');
                this.$newPaper.focus();
                return;
            }

            // Show summative fields
            this.$summativeFields.show();
            this.$nextButton.hide();
            this.$submitButton.show();
        };

        /**
         * Handle form submission
         * @returns {boolean} Whether to allow form submission
         */
        this.handleSubmit = function() {
            var isSummative = this.$paperType.val() === '2';
            
            // For summative exams, validate all required fields
            if (isSummative && this.$summativeFields.is(':visible')) {
                for (var i = 0; i < this.summativeRequiredFields.length; i++) {
                    var fieldId = this.summativeRequiredFields[i];
                    var $field = $('#' + fieldId);
                    var value = $field.val();

                    if (!value || value.trim() === '') {
                        alert('Please complete all required fields');
                        $field.focus();
                        return false;
                    }
                }

                // Validate duration
                var hours = parseInt($('#duration_hours').val()) || 0;
                var mins = parseInt($('#duration_mins').val()) || 0;
                if (hours === 0 && mins === 0) {
                    alert('Please enter a valid duration');
                    $('#duration_hours').focus();
                    return false;
                }
            }

            return true;
        };

        /**
         * Handle cancel button click
         */
        this.handleCancel = function() {
            var paperID = $('#dataset').attr('data-paperid');
            window.location.href = '../paper/details.php?paperID=' + paperID;
        };

    };
});
