<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 11/12/12
 * Time: 11:34
 * To change this template use File | Settings | File Templates.
 */
  /**
   *
   * Form to display login form
   *
   * @author Simon Atack
   * @version 1.0
   * @copyright Copyright (c) 2012 The University of Nottingham
   * @package
   */

echo <<<END
<div>
<form method="POST">
<p>Username:<input type="text" size="20" name="ROGO_USER"><br /></p>
<p>Password:<input type="password" size="20" name="ROGO_PW"><br /></p>
<p><input type="submit" name="rogo-login-form-std" value="Login"><br /></p>
</form>
</div>
END;
