/*
 * Display a non-modal popup, called on hover events in question search results
 * @param Event event - javascript event object
 * @param string questionText - the text to be displayed
 */
function showAdHocWindow(event, questionText){
    $(".adHocWindow").html(questionText);
    $(".adHocWindow").css("display", "block");
    $(".adHocWindow").css("top", event.pageY-50 + "px");
    $(".adHocWindow").css("left", event.pageX+25 + "px");
    $(".adHocWindow").fadeTo("2500", "1");
}

/*
 * Hide the popup, called when the hover event finishes
 */
function hideAdHocWindow(){
    $(".adHocWindow").fadeTo("1", "0.1", function(){
    	$(".adHocWindow").html('');
    	$(".adHocWindow").css("display", "none");
    });
}

// add an empty div for use by the above functions:
$(document).ready(function(){
    $("body").append('<div class="adHocWindow" style="display: none;"></div>');
});


