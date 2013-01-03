<?php
require_once 'shared.inc.php';

class ClassTotalsExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20130103170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '44');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '-6');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '-14%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '10');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '23%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Fail');

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
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[6]/td[2]', '44');
    // Mean
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[8]/td[2]', '16');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[8]/td[3]', '(36.3%)');
    // Median
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[9]/td[2]', '-6');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[9]/td[3]', '(-14%)');
    // Standard Deviation
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[10]/td[2]', '25.53');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[10]/td[3]', '(58.2%)');
    // Max
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[11]/td[2]', '44');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[11]/td[3]', '(100%)');
    // Min
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[12]/td[2]', '-6');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[12]/td[3]', '(-14%)');
    // Range
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[13]/td[2]', '50');
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[13]/td[3]', '(114%)');
    // Top 10%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[14]/td[2]', '84.6%');
    // Top 15%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[15]/td[2]', '76.9%');
    // Top 20%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[16]/td[2]', '69.2%');
    // Top 25%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[17]/td[2]', '61.5%');
    // Bottom 10%
    $this->assertElementContainsText('//table/tbody/tr[20]/td/table/tbody/tr[18]/td[2]', '-6.6%');
  }
}
?>