<?php
require_once 'shared.inc.php';

class ManageFacultyTest extends PHPUnit_Extensions_SeleniumTestCase
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
    do_student_login($this, 'teststudent4', 'fiu&52K3');

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('MCQ 1, vertical, display order, 1 mark, Option One correct');
    $this->assertTextPresent('MCQ 2, horizontal, display order, 1 mark, Option Two correct');
    $this->assertCssCount('css=input[type="radio"]', 6);

    $this->click("name=q1");
    $this->click("xpath=(//input[@name='q2'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 3, DDL, display order, 2 marks, Option Two correct');
    $this->assertTextPresent('MCQ 4, vertical, alphabetic, 1 mark, Option M correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->select("name=q1", "label=Option Two");
    $this->click("xpath=(//input[@name='q2'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 5, horizontal, alphabetic, 1 mark, Option B correct');
    $this->assertTextPresent('MCQ 6, DDL, alphabetic, 2 marks, Option X correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->click("name=q1");
    $this->select("name=q2", "label=Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 7, vertical, random, 1 mark, -1 mark incorrect, Option One correct');
    $this->assertTextPresent('MCQ 8, horizontal, random, 1 mark, -2 marks incorrect, Option Two correct');
    $this->assertTextPresent('MCQ 9, DDL, random, 2 marks, -1 mark incorrect, Option Three correct');
    $this->assertCssCount('css=input[type="radio"]', 6);
    $this->assertCssCount('css=select', 2); // Include page jump DDL


    $this->click("xpath=(//input[@name='q1' and @value='1'])");
    $this->click("xpath=(//input[@name='q2' and @value='2'])");
    $this->select("name=q3", "label=Option Three");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102');
  }

  public function testCompletePaperIncorrect()
  {
    do_student_login($this, 'teststudent5', 'sjg!12T^');

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('MCQ 1, vertical, display order, 1 mark, Option One correct');
    $this->assertTextPresent('MCQ 2, horizontal, display order, 1 mark, Option Two correct');
    $this->assertCssCount('css=input[type="radio"]', 6);

    $this->click("xpath=(//input[@name='q1'])[2]");
    $this->click("xpath=(//input[@name='q2'])[1]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 3, DDL, display order, 2 marks, Option Two correct');
    $this->assertTextPresent('MCQ 4, vertical, alphabetic, 1 mark, Option M correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->select("name=q1", "label=Option Three");
    $this->click("xpath=(//input[@name='q2'])[1]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 5, horizontal, alphabetic, 1 mark, Option B correct');
    $this->assertTextPresent('MCQ 6, DDL, alphabetic, 2 marks, Option X correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->click("xpath=(//input[@name='q1'])[3]");
    $this->select("name=q2", "label=Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 7, vertical, random, 1 mark, -1 mark incorrect, Option One correct');
    $this->assertTextPresent('MCQ 8, horizontal, random, 1 mark, -2 marks incorrect, Option Two correct');
    $this->assertTextPresent('MCQ 9, DDL, random, 2 marks, -1 mark incorrect, Option Three correct');
    $this->assertCssCount('css=input[type="radio"]', 6);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->click("xpath=(//input[@name='q1' and @value='2'])");
    $this->click("xpath=(//input[@name='q2' and @value='3'])");
    $this->select("name=q3", "label=Option Two");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102');
  }

  public function testCompletePaperMixed()
  {
    do_student_login($this, 'teststudent6', 'ara!68X7');

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('MCQ 1, vertical, display order, 1 mark, Option One correct');
    $this->assertTextPresent('MCQ 2, horizontal, display order, 1 mark, Option Two correct');
    $this->assertCssCount('css=input[type="radio"]', 6);

    $this->click("name=q1");
    $this->click("xpath=(//input[@name='q2'])[1]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 3, DDL, display order, 2 marks, Option Two correct');
    $this->assertTextPresent('MCQ 4, vertical, alphabetic, 1 mark, Option M correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->select("name=q1", "label=Option Three");
    $this->click("xpath=(//input[@name='q2'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 5, horizontal, alphabetic, 1 mark, Option B correct');
    $this->assertTextPresent('MCQ 6, DDL, alphabetic, 2 marks, Option X correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->click("name=q1");
    $this->select("name=q2", "label=Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 7, vertical, random, 1 mark, -1 mark incorrect, Option One correct');
    $this->assertTextPresent('MCQ 8, horizontal, random, 1 mark, -2 marks incorrect, Option Two correct');
    $this->assertTextPresent('MCQ 9, DDL, random, 2 marks, -1 mark incorrect, Option Three correct');
    $this->assertCssCount('css=input[type="radio"]', 6);
    $this->assertCssCount('css=select', 2); // Include page jump DDL

    $this->click("xpath=(//input[@name='q1' and @value='2'])");
    $this->click("xpath=(//input[@name='q2' and @value='2'])");
    $this->select("name=q3", "label=Option Three");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102');
  }
}
?>