<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20130103170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-03 11:48:37',104,'0','n','n','100',event);hideTimerReset();\"]");
    $this->click("css=#item1a > img");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '6 out of 6');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[2]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '5 out of 5');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '10 out of 10');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p[2]/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '44 out of 44');
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '100%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20130103170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-03 11:54:58',105,'0','n','n','-14',event);hideTimerReset();\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '-1.5 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '-1 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 5');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2.5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 10');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 3');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '-6 out of 44');
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '-14%');
  }

  public function testMixed() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20130103170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-03 12:01:19',106,'0','n','n','23',event);hideTimerReset();\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '-1.5 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '4 out of 6');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '-1 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 5');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0.5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '6 out of 10');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 3');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '10 out of 44');
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '23%');
  }
}
?>