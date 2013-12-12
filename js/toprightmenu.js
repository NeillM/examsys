$(document).ready(function(event) {
	$('#toprightmenu_icon').click(function() {
		if ($('#toprightmenu').is(':visible')) {
			$('#toprightmenu').fadeOut();
		} else {
			$('#toprightmenu').fadeIn();
		}
	});
	
	$('#signout').click(function() {
	  location.href = cfgRootPath + '/logout.php';
	});

	$('#displaycredits').click(function() {
		opencredits();
	});
	
	$('#aboutrogo').click(function() {
		opencredits();
	});
	
	function opencredits() {
		notice=window.open(cfgRootPath + "/credits/index.php","credits","width=696,innerwidth=708,height=510,innerheight=560,scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=0,menubar=0");
		notice.moveTo(screen.width/2-350,screen.height/2-255)
		if (window.focus) {
			notice.focus();
		}
	}

});