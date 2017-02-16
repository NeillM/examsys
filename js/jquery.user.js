/*
 * Type Course Filter Dropdown
 *
 * @author Richard Whitefoot (UEA)
 * @version 1.0
 */
$(document).ready(function() {

  // TypeCourseFilter namespace
  var TypeCourseFilter = (function() {
    
    /*
    * _disable
    */
    var _disable = function() {
      $("#new_grade").attr("disabled", "disabled");
      $("#typecourse").addClass("grey");
    };

    /*
    * _enable
    */
    var _enable = function() {
      $("#new_grade").removeAttr("disabled");
      $("#typecourse").removeClass("grey");
    };

    /*
    * _setFilter
    */
    var _setFilter = function() {

      $("#new_roles").change(function(){

        var correspondingID = $(this).find(":selected").data("parent");

        if(!correspondingID) {
          correspondingID = "";
        }

        // Reset Type/Course and store previous value
        if($("#new_grade").data("prev-parent") != correspondingID) {
      
          $("#new_grade").val("");

          $("#new_grade optgroup").hide();

          if(!correspondingID) {
            _disable();
          } else {
            $("#new_grade optgroup[data-role='" + correspondingID + "']").show();
            _enable();
          }
        }

        $("#new_grade").data("prev-parent", correspondingID);

      });
    };

    /*
    * init
    */
    var init = function() {
      _disable();
      _setFilter();
    };

    return {
      init: init
    }
        
  })();

  // Call
  TypeCourseFilter.init();

});