<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <title>Log In</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/login_form.css"/>
</head>

<body>
<form method="post">
    <div class="mainbox">

        <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0"
             style="float:left; padding-right:8px"/>

        <div style="color:#1F497D;font-size:28pt; font-weight:bold">Rogo</div>
        <div style="color:#1F497D;font-size:9pt">e-Assessment Management System</div>

        <br/>
        <br/>
      <?php
      if (isset($this->displaystdformobj->messages)) {
        foreach ($this->displaystdformobj->messages as $object) {
          echo <<<HTML
$object->pretext
<div class="msg">$object->content</div>
$object->posttext
HTML;
        }
      }

      if (!(isset($this->displaystdformobj->replace) and $this->displaystdformobj->replace === true)) {

        echo <<<HTML
  <div class="msg">The page you are trying to access requires authentication. Please sign in using your username and password:</div>
HTML;

      }
      ?>
        <div style="margin-left:65px">
            <table>
                <tr>
                    <td>Username</td>
                    <td><input type="text" name="ROGO_USER"/></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><input type="password" name="ROGO_PW"/></td>
                </tr>
            </table>
            <br/>
            <input type="submit" name="rogo-login-form-std" value=" Sign In " style="width:160px"/>
          <?php
          if (isset($this->displaystdformobj->buttons)) {
            foreach ($this->displaystdformobj->buttons as $object) {
              echo <<<HTML
$object->pretext
<input type="$object->type" name="$object->name" value="$object->value" style="$object->style" />
$object->posttext
HTML;
            }
          }
          //<input type="submit" name="cancel" value=" Cancel " />
          ?>
        </div>

      <?php
      if (isset($this->displaystdformobj->postbuttonmessages)) {
        foreach ($this->displaystdformobj->postbuttonmessages as $object) {
          $cssclass = 'msg';
          if (isset($object->cssclass)) {
            $cssclass = $object->cssclass;
          }
          echo <<<HTML
$object->pretext
<div class="$cssclass">$object->content</div>
$object->posttext
HTML;
        }
      }
      ?>

    </div>
</form>

<?php
if (isset($this->displaystdformobj->postformmessages)) {

  $cssareaclass = 'mainbox';
  if (isset($this->displaystdformobj->postformmessages[0]->cssareaclass)) {
    $cssclass = $object->cssclass;
  }
  if (!isset($this->displaystdformobj->postformmessages[0]->rawhtml)) {
    echo <<<HTML
<div class="$cssmainclass">
HTML;
    foreach ($this->displaystdformobj->postformmessages as $object) {
      $cssclass = 'msg';
      if (isset($object->cssclass)) {
        $cssclass = $object->cssclass;
      }
      echo <<<HTML
$object->pretext
<div class="$cssclass">$object->content</div>
$object->posttext
HTML;
    }
    echo <<<HTML
</div>
HTML;

  } else {
    echo $this->displaystdformobj->postformmessages[0]->rawhtml;
  }

}
?>


</body>
</html>