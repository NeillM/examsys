<?php
require '../lang/' . $language . '/touchstone/include/paper_options.inc';

$string['start'] = '1Start';
$string['owner'] = '1Owner';
$string['question'] = '1Question';
$string['type'] = '1Type';
$string['marks'] = '1Marks';
$string['modified'] = '1Modified';
$string['passmark'] = '1Pass Mark';
$string['screen'] = '1Screen';
$string['paperlockedwarning'] = '<strong>1Paper Locked</strong>&nbsp;&nbsp;&nbsp;This paper is now locked and cannot be modified.';
$string['earlywarning'] = '<strong>1Time/Date Warning</strong>&nbsp;&nbsp;&nbsp;This paper is scheduled to start before %sam';
$string['farfuturewarning'] = '<strong>1Time/Date Warning</strong>&nbsp;&nbsp;&nbsp;This paper is scheduled for a long way in the future (%s)';
$string['unlock'] = '1Unlock';
$string['nooptionsdefined'] = '1No options defined for question';
$string['noquestionscreen'] = '<strong>1Warning:</strong> there are no questions on this screen.<br />This will produce an error if the paper is tested!';
$string['markswarning'] = '1Screen %d has %d marks which is %d%% of the paper total. Please insert additional screen breaks to minimise data loss in the event of a computer crash.';
$string['nocorrect'] = '1No correct answer specified';
$string['toomanycorrect'] = '1Too many correct options';
$string['answermissing'] = '1Correct answer missing for some options.';
$string['mcqsurvey'] = "MCQ with 'other' should only be used on surveys";
$string['dichotomouswarning'] = '%d out of %d';
$string['warning'] = '1Warning';
$string['variablenomarks'] = '1Warning: Variable number of marks';
$string['export12'] = '1Export 1.2';
$string['import'] = '1Import';
$string['insertscreenbreak'] = '1Insert screen break';
$string['papernotfound'] = '1Paper not Found';
$string['paperdeleted'] = '1Paper Deleted';
$string['furtherassistance'] = '1For further assistance contact: <a href="mailto:%s">%s</a>';
$string['deleted_msg1'] = '1Paper <strong>%s</strong> has been deleted.';
$string['deleted_msg2'] = '1It can still be recovered from the <a href="/touchstone/delete/recycle_list.php" style="color:blue">recycle bin</a>.';
$string['deleted_msg3'] = '1You do not own this paper, you will need to get <a href="mailto:%s" style="color:blue">%s %s</a> to recover it.';
?>