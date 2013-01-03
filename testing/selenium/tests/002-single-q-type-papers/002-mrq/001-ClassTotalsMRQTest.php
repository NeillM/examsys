<?php
require_once 'shared.inc.php';

class ClassTotalsMRQTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testResults() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20121217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//table/tbody/tr[4]/td[5]', '27');
    $this->assertElementContainsText('//table/tbody/tr[4]/td[6]', '100%');
    $this->assertElementContainsText('//table/tbody/tr[4]/td[7]', 'Distinction');

    $this->assertElementContainsText('//table/tbody/tr[5]/td[5]', '-8');
    $this->assertElementContainsText('//table/tbody/tr[5]/td[6]', '-30%');
    $this->assertElementContainsText('//table/tbody/tr[5]/td[7]', 'Fail');

    $this->assertElementContainsText('//table/tbody/tr[6]/td[5]', '3.5');
    $this->assertElementContainsText('//table/tbody/tr[6]/td[6]', '13%');
    $this->assertElementContainsText('//table/tbody/tr[6]/td[7]', 'Fail');

    // Overall

    // Failures
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[3]/td[2]', '2');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[3]/td[3]', '(67% of cohort)');
    // Passes
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[4]/td[2]', '0');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[4]/td[3]', '(0% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[5]/td[2]', '1');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[5]/td[3]', '(33% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[6]/td[2]', '27');
    // Mean
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[8]/td[2]', '7.5');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[8]/td[3]', '(27.7%)');
    // Median
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[9]/td[2]', '-8');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[9]/td[3]', '(-30%)');
    // Standard Deviation
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[10]/td[2]', '17.84');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[10]/td[3]', '(66.2%)');
    // Max
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[11]/td[2]', '27');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[11]/td[3]', '(100%)');
    // Min
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[12]/td[2]', '-8');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[12]/td[3]', '(-30%)');
    // Range
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[13]/td[2]', '35');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[13]/td[3]', '(130%)');
    // Top 10%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[14]/td[2]', '82.6%');
    // Top 15%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[15]/td[2]', '73.9%');
    // Top 20%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[16]/td[2]', '65.2%');
    // Top 25%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[17]/td[2]', '56.5%');
    // Bottom 10%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[18]/td[2]', '-21.4%');
  }
}
?>