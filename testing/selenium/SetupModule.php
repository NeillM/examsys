<?php
class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://rogo.local/");
  }

  public function testCreateFaculty()
  {
    $this->do_login();

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
    $this->type("name=add_faculty", "Faculty of Selenium Testing");
    $this->click("name=ok");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Faculty of Selenium Testing');
  }

  /**
   * @depends testCreateFaculty
   */
  public function testTestCreateSchool()
  {
    $this->do_login();

    $this->open("/admin/index.php");
    $this->click("css=#13 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");

    $this->click("link=Create new School");
    $this->waitForPageToLoad("30000");

    $this->type("id=school", "School of Selenium Testing");
    $this->select("name=facultyID", "label=Faculty of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('School of Selenium Testing');
  }

  /**
   * @depends testTestCreateSchool
   */
  public function testTestCreateModule()
  {
    $this->do_login();

    $this->open("/admin/index.php");
    $this->click("css=#10 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");

    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");

    $this->type("name=modulecode", "S01SET");
    $this->type("name=fullname", "Selenium Testing");
    $this->select("name=schoolid", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('S01SET');
  }


  private function do_login() {
    $this->open("/staff/");
    $this->type("name=PHP_USER", "selenium");
    $this->type("name=PHP_PW", "srh*63Hh");
    $this->click("name=submit-UserPW");
    $this->waitForPageToLoad("30000");
  }
}
?>