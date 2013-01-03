<?php
require_once 'shared.inc.php';

class TestPreviewExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent4', 'fiu&52K3');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Extended Matching Questions');
    $this->assertTextPresent('Note: Extended Matching notes for students');
    $this->assertTextPresent('Ext Match 1, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 2, aplhabetic, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q2_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_1']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_3']/option[6]", 'Option X');

    $this->select("name=q1_1", "label=A. Option One");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=C. Option M");
    $this->select("name=q2_2", "label=B. Option F");
    $this->select("name=q2_3", "label=A. Option B");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 3, random, 2 marks, 3 scenarios');
    $this->assertTextPresent('Ext Match 4, display order, mark per question,1 mark, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL

    $this->select("name=q1_1", "value=1");
    $this->select("name=q1_2", "value=5");
    $this->select("name=q1_3", "value=2");
    $this->select("name=q2_1", "label=A. Option One");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 5, aplhabetic, mark per question, 3 marks correct, -1 marks incorrect, 3 scenarios');
    $this->assertTextPresent('Ext Match 6, random, mark per question, 2 marks, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q1_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_1']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');

    $this->select("name=q1_1", "label=C. Option M");
    $this->select("name=q1_2", "label=B. Option F");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q2_1", "value=1");
    $this->select("name=q2_2", "value=5");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 7, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 8, aplhabetic, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 4); // Multi-select boxes
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q2_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_1']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q2_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_3']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q2_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_3']/option[5]", 'Option X');

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=E. Option Five");
    $this->addSelection("id=q1_2", "label=C. Option Three");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=D. Option P");
    $this->select("name=q2_2", "label=B. Option F");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=C. Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 9, random, 2 marks, 3 scenarios');
    $this->assertTextPresent('Ext Match 10, display order, mark per question,1 mark, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 5); // Multi-select boxes

    $this->select("name=q1_1", "value=1");
    $this->addSelection("id=q1_2", "value=3");
    $this->addSelection("id=q1_2", "value=5");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=4");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=E. Option Five");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 11, aplhabetic, mark per question, 3 marks correct, -1 marks incorrect, 3 scenarios');
    $this->assertTextPresent('Ext Match 12, random, mark per question, 2 marks, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 3); // Multi-select boxes
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->select("name=q1_2", "label=B. Option F");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q2_1", "value=1");
    $this->addSelection("id=q2_2", "value=3");
    $this->addSelection("id=q2_2", "value=5");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=4");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent5', 'sjg!12T^');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Extended Matching Questions');
    $this->assertTextPresent('Note: Extended Matching notes for students');
    $this->assertTextPresent('Ext Match 1, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 2, aplhabetic, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q2_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_1']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_3']/option[6]", 'Option X');

    $this->select("name=q1_1", "label=B. Option Two");
    $this->select("name=q1_2", "label=C. Option Three");
    $this->select("name=q1_3", "label=D. Option Four");
    $this->select("name=q2_1", "label=D. Option P");
    $this->select("name=q2_2", "label=E. Option X");
    $this->select("name=q2_3", "label=D. Option P");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 3, random, 2 marks, 3 scenarios');
    $this->assertTextPresent('Ext Match 4, display order, mark per question,1 mark, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL

    $this->select("name=q1_1", "value=3");
    $this->select("name=q1_2", "value=3");
    $this->select("name=q1_3", "value=5");
    $this->select("name=q2_1", "label=B. Option Two");
    $this->select("name=q2_2", "label=C. Option Three");
    $this->select("name=q2_3", "label=D. Option Four");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 5, aplhabetic, mark per question, 3 marks correct, -1 marks incorrect, 3 scenarios');
    $this->assertTextPresent('Ext Match 6, random, mark per question, 2 marks, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q1_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_1']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');

    $this->select("name=q1_1", "label=A. Option B");
    $this->select("name=q1_2", "label=C. Option M");
    $this->select("name=q1_3", "label=E. Option X");
    $this->select("name=q2_1", "value=5");
    $this->select("name=q2_2", "value=2");
    $this->select("name=q2_3", "value=1");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 7, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 8, aplhabetic, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 4); // Multi-select boxes
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q2_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_1']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q2_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_3']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q2_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_3']/option[5]", 'Option X');

    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_1", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->select("name=q1_3", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=B. Option F");
    $this->addSelection("id=q2_1", "label=E. Option X");
    $this->select("name=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=D. Option P");
    $this->addSelection("id=q2_3", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 9, random, 2 marks, 3 scenarios');
    $this->assertTextPresent('Ext Match 10, display order, mark per question,1 mark, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 5); // Multi-select boxes

    $this->select("name=q1_1", "value=2");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=2");
    $this->addSelection("id=q1_3", "value=3");
    $this->addSelection("id=q1_3", "value=5");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=C. Option Three");
    $this->addSelection("id=q2_3", "label=D. Option Four");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 11, aplhabetic, mark per question, 3 marks correct, -1 marks incorrect, 3 scenarios');
    $this->assertTextPresent('Ext Match 12, random, mark per question, 2 marks, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 3); // Multi-select boxes
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');

    $this->addSelection("id=q1_1", "label=A. Option B");
    $this->addSelection("id=q1_1", "label=B. Option F");
    $this->select("name=q1_2", "label=D. Option P");
    $this->select("name=q1_3", "label=E. Option X");
    $this->select("name=q2_1", "value=3");
    $this->addSelection("id=q2_2", "value=1");
    $this->addSelection("id=q2_2", "value=4");
    $this->addSelection("id=q2_3", "value=1");
    $this->addSelection("id=q2_3", "value=5");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());

    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102');
  }

  public function testCompletePaperMixed() {
    do_student_login($this, 'teststudent6', 'ara!68X7');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Extended Matching Questions');
    $this->assertTextPresent('Note: Extended Matching notes for students');
    $this->assertTextPresent('Ext Match 1, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 2, aplhabetic, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q2_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_1']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_3']/option[6]", 'Option X');

    $this->select("name=q1_1", "label=A. Option One");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=A. Option B");
    $this->select("name=q2_2", "label=C. Option M");
    $this->select("name=q2_3", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 3, random, 2 marks, 3 scenarios');
    $this->assertTextPresent('Ext Match 4, display order, mark per question,1 mark, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL

    $this->select("name=q1_1", "value=1");
    $this->select("name=q1_2", "value=1");
    $this->select("name=q1_3", "value=2");
    $this->select("name=q2_1", "label=B. Option Two");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=D. Option Four");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 5, aplhabetic, mark per question, 3 marks correct, -1 marks incorrect, 3 scenarios');
    $this->assertTextPresent('Ext Match 6, random, mark per question, 2 marks, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q1_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_1']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');

    $this->select("name=q1_1", "label=A. Option B");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q2_1", "value=1");
    $this->select("name=q2_2", "value=5");
    $this->select("name=q2_3", "value=5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 7, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 8, aplhabetic, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 4); // Multi-select boxes
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q2_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_1']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q2_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_3']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q2_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_3']/option[5]", 'Option X');

    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_1", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->select("name=q1_3", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=E. Option X");
    $this->select("name=q2_2", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=B. Option F");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 9, random, 2 marks, 3 scenarios');
    $this->assertTextPresent('Ext Match 10, display order, mark per question,1 mark, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 5); // Multi-select boxes

    $this->select("name=q1_1", "value=1");
    $this->addSelection("id=q1_2", "value=2");
    $this->addSelection("id=q1_2", "value=5");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=5");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=C. Option Three");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 11, aplhabetic, mark per question, 3 marks correct, -1 marks incorrect, 3 scenarios');
    $this->assertTextPresent('Ext Match 12, random, mark per question, 2 marks, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 3); // Multi-select boxes
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option F');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option F');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q2_1", "value=1");
    $this->addSelection("id=q2_2", "value=3");
    $this->addSelection("id=q2_2", "value=5");
    $this->addSelection("id=q2_3", "value=5");
    $this->addSelection("id=q2_3", "value=4");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102');
  }
}
?>