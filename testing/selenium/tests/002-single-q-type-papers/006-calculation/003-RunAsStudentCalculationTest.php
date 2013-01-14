<?php
require_once 'shared.inc.php';

class RunAsStudentCalculationTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testQuestionPresenceAndOrderPlusUnanswered() {
    do_student_login($this, 'teststudent10', 'jgl!34Z^');

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Calculation Questions');
    $this->assertTextPresent('Note: Calculation 1 notes for students');
    $this->assertTextPresent('Calculation 1 scenario');
    $this->assertTextPresent('Calculation 1, no tolerance, no units, 2 decimals, increment A - 0.2, 1 mark');
    $this->assertTextPresent('Calculation 2, tolerance full 1, units = cm, 1 decimal, 2 marks, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Negative marking');
    $this->assertTextPresent('Note: Calculation 3 notes for students');
    $this->assertTextPresent('Calculation 3, no tolerance, no units, 2 decimals, increment A - 0.2, 1 mark, -0.5 marks incorrect');
    $this->assertCssCount('css=input[type="text"]', 4);    // Include timer box
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Calculation 4, tolerance full 1, units = cm, 1 decimal, 2 marks correct, -1 mark incorrect, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Partial Marks');
    $this->assertTextPresent('Note: Calculation 5 notes for students');
    $this->assertTextPresent('Calculation 5 scenario');
    $this->assertTextPresent('Calculation 5, tolerance partial 1, no units, 2 decimals, increment A - 0.2, 1 mark correct, 0.5 marks partial');
    $this->assertTextPresent('Calculation 6, tolerance full 1, tolerance partial 1.5, units = cm, 1 decimal, 2 marks correct, 1 mark partial, increment A - 0.1, increment B - 0.2');
    $this->assertCssCount('css=input[type="text"]', 4);    // Include timer box
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(2 marks)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Negative marking, Partial Marks');
    $this->assertTextPresent('Note: Calculation 7 notes for students');
    $this->assertTextPresent('Calculation 7 scenario');
    $this->assertTextPresent('Calculation 7, tolerance partial 1, no units, 2 decimals, increment A - 0.2, 1 mark, 0.5 marks partial, -0.5 marks incorrect');
    $this->assertTextPresent('Calculation 8, tolerance full 1, tolerance partial 1.5, units = cm, 1 decimal, 2 marks correct, 1 mark partial, -1 mark incorrect, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Partial Marks, % Based Tolerances');
    $this->assertTextPresent('Note: Calculation 9 notes for students');
    $this->assertTextPresent('Calculation 9 scenario');
    $this->assertTextPresent('Calculation 9, tolerance partial 5%, no units, 2 decimals, increment A - 0.2, 1 mark correct, 0.5 marks partial');
    $this->assertCssCount('css=input[type="text"]', 4);    // Include timer box
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(1 mark)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Calculation 10, tolerance full 5%, tolerance partial 8%, units = cm, 1 decimal, 2 marks correct, 1 mark partial, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Negative marking, Partial Marks, % Based Tolerances');
    $this->assertTextPresent('Note: Calculation 11 notes for students');
    $this->assertTextPresent('Calculation 11 scenario');
    $this->assertTextPresent('Calculation 11, tolerance partial 5%, no units, 2 decimals, increment A - 0.2, 1 mark, 0.5 marks partial, -0.5 marks incorrect');
    $this->assertTextPresent('Calculation 12, tolerance full 5%, tolerance partial 8%, units = cm, 1 decimal, 2 marks correct, 1 mark partial, -1 mark incorrect, increment A - 0.1, increment B - 0.2');
    $this->assertCssCount('css=input[type="text"]', 4);    // Include timer box
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(2 marks, negative marking)');

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '0 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '0.0%');
  }

  // public function testCompletePaperCorrect() {
  //   do_student_login($this, 'teststudent11', 'bkt_66Y4');

  //   $this->open("/user_index.php?id=61357920091102");
  //   $this->click("id=start");
  //   $this->waitForPopUp("paper", "30000");
  //   $this->selectWindow("name=paper");
  //   // Get variables
  //   $qn_text = $this->getText('id=calc1_q');
  //   $matches = array();
  //   preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
  //   // Calculate correct answer
  //   $answer = pow($matches[1], 2) * pi();
  //   $answer = round($answer, 2);
  //   $this->type("id=q1", $answer);
  //   $this->click("id=finish");
  //   $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
  //   $this->waitForPageToLoad("30000");
  //   $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102');

  //   // Individual Question Marks
  //   $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');

  //   // Overall Marks
  //   // $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '12 out of 12');
  //   // $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
  //   // $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '100.0%');
  // }

  // public function testCompletePaperIncorrect() {
  //   do_student_login($this, 'teststudent12', 'rmu_74L4');

  //   $this->open("/user_index.php?id=11355244387102");
  //   $this->click("id=start");
  //   $this->waitForPopUp("paper", "30000");
  //   $this->selectWindow("name=paper");

  //   $this->click("xpath=(//input[@name='q1'])[2]");
  //   $this->click("xpath=(//input[@name='q2'])[1]");
  //   $this->click("id=next");
  //   $this->waitForPageToLoad("30000");

  //   $this->select("name=q1", "label=Option Three");
  //   $this->click("xpath=(//input[@name='q2'])[1]");
  //   $this->click("id=next");
  //   $this->waitForPageToLoad("30000");

  //   $this->click("xpath=(//input[@name='q1'])[3]");
  //   $this->select("name=q2", "label=Option M");
  //   $this->click("id=next");
  //   $this->waitForPageToLoad("30000");

  //   $this->click("xpath=(//input[@name='q1' and @value='2'])");
  //   $this->click("xpath=(//input[@name='q2' and @value='3'])");
  //   $this->select("name=q3", "label=Option Two");
  //   $this->click("id=finish");
  //   $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
  //   $this->waitForPageToLoad("30000");
  //   $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102');

  //   // Individual Question Marks
  //   $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '0 out of 1');
  //   $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
  //   $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
  //   $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
  //   $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
  //   $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
  //   $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '-1 out of 1');
  //   $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '-2 out of 1');
  //   $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '-1 out of 2');

  //   // Overall Marks
  //   $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '-4 out of 12');
  //   $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
  //   $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '-33.3%');
  // }

  // public function testCompletePaperMixed() {
  //   do_student_login($this, 'teststudent13', 'hii.420R');

  //   $this->open("/user_index.php?id=11355244387102");
  //   $this->click("id=start");
  //   $this->waitForPopUp("paper", "30000");
  //   $this->selectWindow("name=paper");

  //   $this->click("name=q1");
  //   $this->click("xpath=(//input[@name='q2'])[1]");
  //   $this->click("id=next");
  //   $this->waitForPageToLoad("30000");

  //   $this->select("name=q1", "label=Option Three");
  //   $this->click("xpath=(//input[@name='q2'])[2]");
  //   $this->click("id=next");
  //   $this->waitForPageToLoad("30000");

  //   $this->click("name=q1");
  //   $this->select("name=q2", "label=Option M");
  //   $this->click("id=next");
  //   $this->waitForPageToLoad("30000");

  //   $this->click("xpath=(//input[@name='q1' and @value='2'])");
  //   $this->click("xpath=(//input[@name='q2' and @value='2'])");
  //   $this->select("name=q3", "label=Option Three");
  //   $this->click("id=finish");
  //   $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
  //   $this->waitForPageToLoad("30000");
  //   $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102');

  //   // Individual Question Marks
  //   $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '1 out of 1');
  //   $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
  //   $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
  //   $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
  //   $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
  //   $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
  //   $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '-1 out of 1');
  //   $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
  //   $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '2 out of 2');

  //   // Overall Marks
  //   $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[2]/td[2]', '5 out of 12');
  //   $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[3]/td[2]', '40%');
  //   $this->assertElementContainsText('//div[5]/table/tbody/tr/td/table/tbody/tr[4]/td[2]', '41.7%');
  // }
}
?>