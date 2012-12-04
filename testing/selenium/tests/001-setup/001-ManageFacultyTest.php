<?php
require_once 'shared.inc.php';

class ManageFacultyTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://rogo.local/");
  }

  public function testCreateFaculty()
  {
    do_admin_login($this);

    $this->create_faculty('Faculty of Selenium Testing');
  }

  // TODO: Can't create faculty with same name
  // NOTE: not possible with current implementation

  public function testCreateFacultyForDeletion()
  {
    do_admin_login($this);

    $this->create_faculty('Faculty of Short Lived');
  }

  public function testEditFaculty() {
    do_admin_login($this);

    $this->open("/admin/list_faculties.php");
    $this->click("css=#4 > td > div.col10");
    $this->click("link=Edit Faculty");
    $this->waitForPopUp("faculties", "30000");
    $this->selectWindow("name=faculties");
    $this->type("name=new_faculty", "Faculty of Short Lived2");
    $this->click("name=submit");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Faculty of Short Lived2');
  }

  // TODO: Delete Faculty

  private function create_faculty($name) {
    $this->open("/staff/index.php");
    $this->click("link=Administrative Tools");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Rogō: Admin' . $this->install_type);

    $this->click("css=#8 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Faculties' . $this->install_type);

    $this->click("link=Create new Faculty");
    $this->waitForPopUp("faculties", "30000");
    $this->selectWindow("name=faculties");
    $this->type("name=add_faculty", $name);
    $this->click("name=ok");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent($name);
  }
}
?>