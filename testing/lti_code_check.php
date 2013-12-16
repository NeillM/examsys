<?php

require_once '../include/staff_student_auth.inc';
require_once '../config/integration-UoN/lti_integration.class.php';

print $_GET['modcode'] . '<BR>';

var_dump(lti_integration_extended::module_code_translate($_GET['modcode'],'TITLE DFDGDS'));
