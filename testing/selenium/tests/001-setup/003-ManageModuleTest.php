<?php
require_once 'shared.inc.php';

class ManageModuleTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://rogo.local/");
  }

  public function testTestCreateModule()
  {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#10 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Modules' . $this->install_type);

    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Create new Module' . $this->install_type);

    $this->type("name=modulecode", "S02SHL");
    $this->type("name=fullname", "Short Lived");
    $this->select("name=schoolid", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('S02SHL');
  }

  // TODO: Edit Module
  // TODO: Delete Module
}
?>