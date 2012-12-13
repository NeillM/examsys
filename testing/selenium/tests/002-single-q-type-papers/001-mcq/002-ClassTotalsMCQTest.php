<?php
require_once 'shared.inc.php';

class ClassTotalsMCQTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';
  protected $page_root = 'https://rogo.local';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testResults()
  {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20121213100000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '12');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '-4');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '-33%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '5');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '42%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Pass');
  }
}
?>