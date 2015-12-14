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

// Get configs.
$configObject->set_db_object($mysqli);
$configObject->load_settings('core');
$settings = (object) $configObject->get_setting('core');
if (property_exists($settings, 'apilogfile')) {
    $logfile = $settings->apilogfile;
} else {
    $logfile = '';
}

// Enrolment request.
$app->post('/modulemanagement/enrol', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {
    $request = 'modulemanagement';
    $response = 'moduleManagementEnrolResponse';
    $operations = array('enrol', 'unenrol');
    $fields = array('userid', 'attempt', 'moduleid', 'session');
    $xsd = 'enrolrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);
});

// Module management request.
$app->post('/modulemanagement', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {
    $request = 'modulemanagement';
    $response = 'moduleManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'modulecode', 'name', 'school', 'faculty', 'sms');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);
});

// Course management request.
$app->post('/coursemanagement', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {
    $request = 'coursemanagement';
    $response = 'courseManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'name', 'description', 'school', 'faculty');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);
});

// School management request.
$app->post('/schoolmanagement', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {
    $request = 'schoolmanagement';
    $response = 'schoolManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'name', 'faculty');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);
});

// Faculty management request.
$app->post('/facultymanagement', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {
    $request = 'facultymanagement';
    $response = 'facultyManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'name');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);
});

// User management request.
$app->post('/usermanagement', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {  
    $request = 'usermanagement';
    $response = 'userManagementResponse';
    $operations = array('create', 'delete');
    $fields = array('id', 'username', 'title', 'forename', 'surname', 'initials', 'email', 'password',
        'course', 'gender', 'year', 'role', 'studentid', 'modules');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);
});
// Assessment management request
$app->post('/assessmentmanagement', function() use($api, $mysqli, $oauth, $render, $langpack, $logfile) {  
    $request = 'assessmentmanagement';
    $response = 'assessmentManagementResponse';
    $operations = array('create', 'schedule', 'delete');
    $fields = array('id', 'owner', 'type', 'title', 'startdatetime', 'enddatetime', 'modules', 'session', 'labs', 'month',
        'cohort_size', 'sittings', 'barriers', 'campus', 'notes', 'timezone', 'duration');
    $xsd = 'managementrequest';
    process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile);    
});
/**
 * Gradebook consumption request
 * 
 * @param mysqli $mysqli - db connection
 * @param object $oauth - oauth object
 * @param object $api - api object
 * @param object $render - render object
 * @param object $langpack - language object
 * @param string $logfile - file to log to
 */
$app->get('/gradebook/:filtername/:filterid', function($filtername, $filterid) use($mysqli, $oauth, $api, $render, $langpack, $logfile) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    // Log request.
    if ($logfile != '') {
        $updatelog = "\n\n" . "--" . date("YmdHis") . "--\n\nUser Agent: " . $api->get_user_agent() .
        "\nAccess Token: " . $api->get_parameter('access_token') .
        "\nResource Path: " . $api->get_path();
        file_put_contents($logfile, $updatelog, FILE_APPEND);
    }
    
    //Check Permission
    if (!$oauth->check_permissions('gradebook', $client_id)) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'nopermission')), $logfile);
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
        $render->render_xml($template, 'gradebookResponse', $response, $logfile);
    }
});
/**
 * 404 error handling.
 *
 * @param object $render - render object
 * @param object $api - api object
 * @param object $langpack - language object
 * @param string $logfile - file to log to
 */
$app->notFound(function () use ($render, $api, $langpack, $logfile) {
    
    // Log request.
    if ($logfile != '') {
        $updatelog = "\n\n" . "--" . date("YmdHis") . "--\n\nUser Agent: " . $api->get_user_agent() .
        "\nResource Path: " . $api->get_path() .
        "\nAccess Token: " . $api->get_parameter('access_token') . "\n\n" . $api->get_body();
        file_put_contents($logfile, $updatelog, FILE_APPEND);
    }
    
    $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '404')), $logfile);
});
/**
 * 500 error handling.
 *
 * @param object $render - render object
 * @param object $api - api object
 * @param object $langpack - language object
 * @param string $logfile - file to log to
 */
$app->error(function (\Exception $e) use ($render, $api, $langpack, $logfile) {
    
    // Log request.
    if ($logfile != '') {
        $updatelog = "\n\n" . "--" . date("YmdHis") . "--\n\nUser Agent: " . $api->get_user_agent() .
        "\nResource Path: " . $api->get_path() .
        "\nAccess Token: " . $api->get_parameter('access_token') . "\n\n" . $api->get_body();
        file_put_contents($logfile, $updatelog, FILE_APPEND);
    }
    
    $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '500')), $logfile);
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
 * @param string $logfile - file to log to
 */
function process ($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli, $logfile) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    $user_id = $oauth->get_client_user($client_id);
    //Check Permissions
    foreach ($operations as $operation) {
        $perm[$operation] = $oauth->check_permissions($request . '/' . $operation, $client_id);
    }

    // Log request.
    if ($logfile != '') {
        $updatelog = "\n\n" . "--" . date("YmdHis") . "--\n\nUser Agent: " . $api->get_user_agent() .
        "\nResource Path: " . $api->get_path() .
        "\nAccess Token: " . $api->get_parameter('access_token') . "\n\n" . $api->get_body();
        file_put_contents($logfile, $updatelog, FILE_APPEND);
    }
    
    // Check media type - only text/xml supported currently.
    if (!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')), $logfile);
    } else {
        $responsedata = array();
        $classname = '\\api\\' . $request;
        $requestobject = new $classname($mysqli);

        // Process the request.
        $data = $api->process($request, $xsd);
        
        // XML.
        if ($data[0] == 'OK') {
            $responsedata = $api->parse($requestobject, $fields, $operations, $data[1], $perm, $user_id);
            $template = 'api/success.xml';
        } else {
            $responsedata = $data[1];
            $template = 'api/error.xml';
        }
        
        // Render response.
        $render->render_xml($template, $response, $responsedata, $logfile);
    }
}

$app->run();