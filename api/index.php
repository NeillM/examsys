<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* API routing functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

require_once '../include/load_config.php';

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), 
                                 $configObject->get('cfg_db_webservice_user'), 
                                 $configObject->get('cfg_db_webservice_passwd'), 
                                 $configObject->get('cfg_db_database'), 
                                 $configObject->get('cfg_db_charset'), 
                                 UserNotices::get_instance(), 
                                 $configObject->get('dbclass'));
                                     
$app = new \Slim\Slim();
$oauth = new oauth($configObject);
$render = new render($configObject);
$langpack = new \langpack();

// Set up api.
$api = new \api\api($app);
$api->set_header();

// Request oauth token.
$app->post('/requesttoken', function() use($oauth) {
    $oauth->request_token();
});

// Enrolment request.
$app->post('/modulemanagement/enrol', function() use($api, $mysqli, $oauth, $render, $langpack) {
    $request = 'modulemanagement';
    $response = 'moduleManagementEnrolResponse';
    $operations = array('enrol', 'unenrol');
    $fields = array('userid', 'attempt', 'moduleid', 'session');
    $xsd = 'enrolrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
});

// Module management request.
$app->post('/modulemanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
    $request = 'modulemanagement';
    $response = 'moduleManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'modulecode', 'name', 'school', 'faculty', 'sms');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
});

// Course management request.
$app->post('/coursemanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
    $request = 'coursemanagement';
    $response = 'courseManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'name', 'description', 'school', 'faculty');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
});

// School management request.
$app->post('/schoolmanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
    $request = 'schoolmanagement';
    $response = 'schoolManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'name', 'faculty');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
});

// Faculty management request.
$app->post('/facultymanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
    $request = 'facultymanagement';
    $response = 'facultyManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'name');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
});

// User management request.
$app->post('/usermanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {  
    $request = 'usermanagement';
    $response = 'userManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'username', 'title', 'forename', 'surname', 'initials', 'email', 'password',
        'course', 'gender', 'year', 'role', 'studentid', 'modules');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
});
// Assessment management request
$app->post('/assessmentmanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {  
    $request = 'assessmentmanagement';
    $response = 'assessmentManagementResponse';
    $operations = array('create', 'schedule', 'delete');
    $fields = array('id', 'owner', 'type', 'title', 'startdatetime', 'enddatetime', 'modules', 'session', 'labs', 'month',
        'cohort_size', 'sittings', 'barriers', 'campus', 'notes', 'timezone');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);    
});
// Gradebook consumption request
$app->get('/gradebook/:filtername/:filterid', function($filtername, $filterid) use($mysqli, $oauth, $render, $langpack) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permission
    if (!$oauth->check_permissions('gradebook', $client_id)) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'nopermission')));
    } else {
    
        $response = array();
        $gradebook = new \api\gradebook($mysqli);

        // Process the request.
        $request = $gradebook->get($filtername, $filterid);
        $response = $request[1];
        if ($request[0] == 'OK') {
            $template = 'api/' . $filtername . '_gradebook.xml';
        } else {
            $template = 'api/error.xml';
        }
    
        // Render response.
        $render->render_xml($template, 'gradebookResponse', $response);
    }
});
// 404 error handling.
$app->notFound(function () use ($render, $langpack) {
    $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '404')));
});

// 500 error handling.
$app->error(function (\Exception $e) use ($render, $langpack) {
    $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '500')));
});

/**
 * Process the web wervice request.
 * 
 * All request are authenticated, validated and processed.
 * @param string $request - name of request
 * @param array $operations - operations available in request
 * @param array $fields - expected request fields
 * @param string $response - name of response
 * @param object $oauth - oauth object
 * @param object $api - api object
 * @param object $langpack - language object
 * @param object $render - render object
 * @param string $xsd - xsd filename
 * @param mysqli $mysqli - db connection 
 */
function process ($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    foreach ($operations as $operation) {
        $perm[$operation] = $oauth->check_permissions($request . '/' . $operation, $client_id);
    }

    // Check media type - only test/xml supported currently.
    if (!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
    } else {
        $responsedata = array();
        $classname = '\\api\\' . $request;
        $requestobject = new $classname($mysqli);

        // Process the request.
        $data = $api->process($request, $xsd);
        
        // XML.
        if ($data[0] == 'OK') {
            $responsedata = $api->parse($requestobject, $fields, $operations, $data[1], $perm);
            $template = 'api/success.xml';
        } else {
            $responsedata = $data[1];
            $template = 'api/error.xml';
        }
        
        // Render response.
        $render->render_xml($template, $response, $responsedata);
    }
}

$app->run();