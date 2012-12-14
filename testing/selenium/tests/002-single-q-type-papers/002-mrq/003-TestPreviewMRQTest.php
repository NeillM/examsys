<?php
require_once 'shared.inc.php';

class TestPreviewMRQTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';
  protected $page_root = 'https://rogo.local';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCompletePaperCorrect()
  {
    // TODO: test order of alphabetic questions

    do_student_login($this, 'teststudent4', 'fiu&52K3');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 1 notes for students');
    $this->assertTextPresent('MRQ 1 scenario');
    $this->assertTextPresent('MRQ 1, display order, mark per option, 1 mark, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 2, alphabetic, mark per option, 1 mark, Option B and Option P correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_3");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 3, random, mark per option, 2 marks, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 4, display order, mark per question, 2 marks, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_4");
    $this->click("id=q2_1");
    $this->click("id=q2_2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 5, alphabetic, mark per question, 1 mark, Option M and Option X correct');
    $this->assertTextPresent('MRQ 6, random, mark per question, 2 marks, Option Two and Option Three correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_3");
    $this->click("id=q2_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 7 notes for students');
    $this->assertTextPresent('MRQ 7 scenario');
    $this->assertTextPresent('MRQ 7, display order, mark per option, 1 mark correct, -1 mark incorrect, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 8, alphabetic, mark per option, 2 marks correct, -1 mark incorrect, Option B and Option X correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_4");
    $this->click("id=q2_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 9, random, mark per option, 1 mark correct, -0.5 marks incorrect, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 10, display order, mark per question, 2 marks correct, -1 mark incorrect, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 11, alphabetic, mark per question, 1 mark correct, -1 mark incorrect, Option M and Option B correct');
    $this->assertTextPresent('MRQ 12, random, mark per question, 3 marks correct, -2 marks incorrect, Option Two and Option Four correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_2");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102');
  }

  public function testCompletePaperIncorrect()
  {
    do_student_login($this, 'teststudent5', 'sjg!12T^');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 1 notes for students');
    $this->assertTextPresent('MRQ 1 scenario');
    $this->assertTextPresent('MRQ 1, display order, mark per option, 1 mark, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 2, alphabetic, mark per option, 1 mark, Option B and Option P correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_4");
    $this->click("id=q2_1");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 3, random, mark per option, 2 marks, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 4, display order, mark per question, 2 marks, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_3");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 5, alphabetic, mark per question, 1 mark, Option M and Option X correct');
    $this->assertTextPresent('MRQ 6, random, mark per question, 2 marks, Option Two and Option Three correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_4");
    $this->click("id=q2_4");
    $this->click("id=q2_1");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 7 notes for students');
    $this->assertTextPresent('MRQ 7 scenario');
    $this->assertTextPresent('MRQ 7, display order, mark per option, 1 mark correct, -1 mark incorrect, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 8, alphabetic, mark per option, 2 marks correct, -1 mark incorrect, Option B and Option X correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 9, random, mark per option, 1 mark correct, -0.5 marks incorrect, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 10, display order, mark per question, 2 marks correct, -1 mark incorrect, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_4");
    $this->click("id=q1_2");
    $this->click("id=q2_3");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 11, alphabetic, mark per question, 1 mark correct, -1 mark incorrect, Option M and Option B correct');
    $this->assertTextPresent('MRQ 12, random, mark per question, 3 marks correct, -2 marks incorrect, Option Two and Option Four correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_4");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_3");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102');
  }

  public function testCompletePaperMixed()
  {
    do_student_login($this, 'teststudent6', 'ara!68X7');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 1 notes for students');
    $this->assertTextPresent('MRQ 1 scenario');
    $this->assertTextPresent('MRQ 1, display order, mark per option, 1 mark, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 2, alphabetic, mark per option, 1 mark, Option B and Option P correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 3, random, mark per option, 2 marks, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 4, display order, mark per question, 2 marks, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 5, alphabetic, mark per question, 1 mark, Option M and Option X correct');
    $this->assertTextPresent('MRQ 6, random, mark per question, 2 marks, Option Two and Option Three correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_2");
    $this->click("id=q1_4");
    $this->click("id=q2_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 7 notes for students');
    $this->assertTextPresent('MRQ 7 scenario');
    $this->assertTextPresent('MRQ 7, display order, mark per option, 1 mark correct, -1 mark incorrect, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 8, alphabetic, mark per option, 2 marks correct, -1 mark incorrect, Option B and Option X correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_4");
    $this->click("id=q2_1");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 9, random, mark per option, 1 mark correct, -0.5 marks incorrect, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 10, display order, mark per question, 2 marks correct, -1 mark incorrect, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_2");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 11, alphabetic, mark per question, 1 mark correct, -1 mark incorrect, Option M and Option B correct');
    $this->assertTextPresent('MRQ 12, random, mark per question, 3 marks correct, -2 marks incorrect, Option Two and Option Four correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);

    $this->click("id=q1_1");
    $this->click("id=q1_2");
    $this->click("id=q2_3");
    $this->click("id=q2_1");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102');
  }
}
?>