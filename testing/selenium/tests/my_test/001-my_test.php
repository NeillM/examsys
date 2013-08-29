<?php
require_once 'shared.inc.php';

class MyTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl('https://localhost/');
  }

  public function testResults() {
    do_staff_login($this);
		sleep(10);
		$this->open("/staff/index.php");
    //$this->click("name=rogo-login-form-std");
    //$this->waitForPageToLoad("30000");
    $this->click("link=Make a new folder");
    $this->waitForPageToLoad("30000");
    $this->click("name=folder_name");
    $this->type("name=folder_name", "testfolder");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");
  
		$this->assertTextPresent('testfolder');
  }
}
?>