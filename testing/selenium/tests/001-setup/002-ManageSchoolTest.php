<?php
/* DEPENDS ON ManageFacultyTest */

require_once 'shared.inc.php';

class ManageSchoolTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://rogo.local/");
  }

  public function testTestCreateSchool()
  {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#13 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Schools' . $this->install_type);

    $this->click("link=Create new School");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Add Schools' . $this->install_type);

    $this->type('id=school', 'School of Short Lived');
    $this->select('name=facultyID', 'label=Faculty of Selenium Testing');
    $this->click('name=submit');
    $this->waitForPageToLoad('30000');
    $this->assertTextPresent('School of Short Lived');
  }

  // TODO: Edit School
  // TODO: Delete School
}
?>