<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<title>Log In</title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/login_form.css" />
</head>

<body>
<form method="post">
<div class="mainbox">

  <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px" />
  <div style="color:#1F497D;font-size:28pt; font-weight:bold">Rogo</div>
  <div style="color:#1F497D;font-size:9pt">e-Assessment Management System</div>

  <br />
  <br />
  
  <div class="msg">The page you are trying to access requires authentication. Please sign in using your username and password:</div>
    
  <div style="margin-left:65px">
  <table>
  <tr><td>Username</td><td><input type="text" name="ROGO_USER" /></td></tr>
  <tr><td>Password</td><td><input type="password" name="ROGO_PW" /></td></tr>
  </table>
  <br />
  <input type="submit" name="rogo-login-form-std" value=" Sign In " style="width:160px" />
<?php
    if(isset($displaystdformobj->buttons)) {
      foreach($displaystdformobj->buttons as $object) {
        echo <<<END
$object->pretext
<input type="$object->type" name="$object->name" value="$object->value" style="$object->style" />
$object->posttext
END;
      }
    }
    //<input type="submit" name="cancel" value=" Cancel " />
?>
  </div>

</div>
</form>

</body>
</html>