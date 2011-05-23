<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">	
	<style>
body {background-color: #C4D9F9; font-family: Arial, Helvetica, sans-serif; font-size: 11pt; margin-left: 0px; margin-right: 0px; margin-top: 0px; margin-bottom: 4px}
		a{color:#cc0000;font-size:xx-small;}
	</style>
	
	<!-- STEP 1: Include the Editor js file -->
	<script language=JavaScript src='./scripts/innovaeditor.js'></script>
	
</head>
<body>

<h4>Using in a HTML Form (PHP example) - <a href="../default.htm">Back</a></h4>

<form method="post" action="test.php" id="Form1">

	<textarea id="txtContent" name="txtContent" rows=2 cols=30>
	<?
	function encodeHTML($sHTML)
		{
		$sHTML=ereg_replace("&","&amp;",$sHTML);
		$sHTML=ereg_replace("<","&lt;",$sHTML);
		$sHTML=ereg_replace(">","&gt;",$sHTML);
		return $sHTML;
		}
	
	if(isset($_POST["txtContent"])) 
		{
		$sContent=stripslashes($_POST['txtContent']); /*** remove (/) slashes ***/	
		echo encodeHTML($sContent);
		}
	?>
	</textarea>

	<script> //STEP 2: Replace the textarea (txtContent)
		var oEdit1 = new InnovaEditor("oEdit1");
                oEdit1.mode="XHTMLBody";
                oEdit1.useBR=true;
                oEdit1.width="480";
                oEdit1.height="150px";
                oEdit1.features=["Cut","Copy","Paste","|","StyleAndFormatting","TextFormatting","ListFormatting","BoxFormatting","ParagraphFormatting","CssText","|","Bold","Italic","Underline","|","JustifyLeft","JustifyCenter","JustifyRight","JustifyFull","|","Numbering","Bullets","|","Table","Characters","Hyperlink"];
                oEdit1.arrStyle = [["BODY",false,"","background:white; color:black; font-family:Arial,Helvetica;"],["a:link",false,"","color:white; font-weight:bold;"],["a:active",false,"","color:white; font-weight:bold;"],["a:visited",false,"","color:whitesmoke;font-weight:bold;"],[".CodeInText",true,"Code In Text","font-family:Courier New;font-weight:bold;"]];
                oEdit1.btnStyles = true;
		oEdit1.REPLACE("txtContent");//Specify the id of the textarea here
	</script>

	<input type="submit" value="Submit">
</form>

<?
if(isset($_POST["txtContent"])) 
	{
	$sContent=stripslashes($_POST['txtContent']); /*** remove (/) slashes ***/		
	echo "<p>Content below</p><br />\n" . $sContent;
	}
?>

</body>
</html>
