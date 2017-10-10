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
// Requirement functions
//
// @author Dr Joseph Bater <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2017 The University of Nottingham
//

// Toggle IMS blurb if checked on load.
$(document).ready(function () {
  if ($('#cfg_ims_enabled').is(':checked')) {
   $('#display_ims').toggle();
  }
});
// Cancel button.
$(function () {
  $('#cancel').click(function() {
    history.back();
  });
});
// Toggle IMS blurb when checkbox activated.
$(function () {
  $('#cfg_ims_enabled').change(function() {
    $('#display_ims').toggle();
  });
});