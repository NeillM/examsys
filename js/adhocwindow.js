function showAdHocWindow(event, questionText){
    $(".adHocWindow").html(questionText);
    $(".adHocWindow").css("display", "block");
    $(".adHocWindow").css("top", event.pageY-50 + "px");
    $(".adHocWindow").css("left", event.pageX+25 + "px");
    $(".adHocWindow").fadeTo("2500", "1");
}

function hideAdHocWindow(event, caller){
    $(".adHocWindow").fadeTo("1", "0.1", function(){
    	$(".adHocWindow").html('');
    	$(".adHocWindow").css("display", "none");
    });
}

$(document).ready(function(){
    $("body").append('<div class="adHocWindow" style="display: none;"></div>');
});


