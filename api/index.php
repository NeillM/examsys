<?php

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

$app->post('/requesttoken', function() use($app, $oauth) {
    $oauth->request_token();
});

$app->post('/modulemanagement/enrol', function() use($app, $api, $mysqli, $oauth, $render, $langpack) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    $perm['enrol'] = $oauth->check_permissions('modulemanagement/enrol', $client_id);
    $perm['unenrol'] = $oauth->check_permissions('modulemanagement/unenrol', $client_id);
    
    // Check media type - only test/xml supported currently.
    if(!$api->get_mediatype()) {
        $render->render_xml('api/error.xml','rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
        die;
    }
    
    $response = array();
    $course = new \api\modulemanagement($mysqli);
    
    // Process the request.
    $request = $api->process('modulemanagement', 'enrolrequest');
    $fields = array('userid', 'attempt', 'moduleid', 'session');
    
    // XML.
    if ($request[0] == 'OK') {
        $actions = array('enrol', 'unenrol');
        $response = $api->parse($course, $fields, $actions, $request[1], $perm);
        $template = 'api/success.xml';
    } else {
        $response = $request[1];
        $template = 'api/error.xml';
    }
    
    // Render response.
    $render->render_xml($template, 'moduleManagementEnrolResponse', $response);
    
});

$app->post('/modulemanagement', function() use($app, $api, $mysqli, $oauth, $render, $langpack) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    $perm['create'] = $oauth->check_permissions('modulemanagement/create', $client_id);
    $perm['delete'] = $oauth->check_permissions('modulemanagement/delete', $client_id);
    
    // Check media type - only test/xml supported currently.
    if(!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
        die;
    }

    $response = array();
    $course = new \api\modulemanagement($mysqli);

    // Process the request.
    $request = $api->process('modulemanagement', 'managementrequest');
    $fields = array('id', 'modulecode', 'name', 'school', 'faculty', 'sms');
        
    // XML.
    if ($request[0] == 'OK') {
        $actions = array('create', 'delete');
        $response = $api->parse($course, $fields, $actions, $request[1], $perm);
        $template = 'api/success.xml';
    } else {
        $response = $request[1];
        $template = 'api/error.xml';
    }

    // Render response.
    $render->render_xml($template, 'moduleManagementResponse', $response);
    
});

$app->post('/coursemanagement', function() use($app, $api, $mysqli, $oauth, $render, $langpack) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    $perm['create'] = $oauth->check_permissions('coursemanagement/create', $client_id);
    $perm['delete'] = $oauth->check_permissions('coursemanagement/delete', $client_id);
    
    // Check media type - only test/xml supported currently.
    if(!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
        die;
    }
    $response = array();
    $course = new \api\coursemanagement($mysqli);
    // Process the request.
    $request = $api->process('coursemanagement', 'managementrequest');
    $fields = array('id', 'name', 'description', 'school', 'faculty');
        
    // XML.
    if ($request[0] == 'OK') {
        $actions = array('create', 'delete');
        $response = $api->parse($course, $fields, $actions, $request[1], $perm);
        $template = 'api/success.xml';
    } else {
        $response = $request[1];
        $template = 'api/error.xml';
    }

    // Render response.
    $render->render_xml($template, 'courseManagementResponse', $response);
    
});

$app->post('/schoolmanagement', function() use($app, $api, $mysqli, $oauth, $render, $langpack) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    $perm['create'] = $oauth->check_permissions('schoolmanagement/create', $client_id);
    $perm['delete'] = $oauth->check_permissions('schoolmanagement/delete', $client_id);
    
    // Check media type - only test/xml supported currently.
    if(!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
        die;
    }
    $response = array();
    $school = new \api\schoolmanagement($mysqli);
    // Process the request.
    $request = $api->process('schoolmanagement', 'managementrequest');
    $fields = array('id', 'name', 'faculty');
        
    // XML.
    if ($request[0] == 'OK') {
        $actions = array('create', 'delete');
        $response = $api->parse($school, $fields, $actions, $request[1], $perm);
        $template = 'api/success.xml';
    } else {
        $response = $request[1];
        $template = 'api/error.xml';
    }

    // Render response.
    $render->render_xml($template, 'schoolManagementResponse', $response);
    
});


$app->post('/facultymanagement', function() use($app, $api, $mysqli, $oauth, $render, $langpack) {
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    $perm['create'] = $oauth->check_permissions('facultymanagement/create', $client_id);
    $perm['delete'] = $oauth->check_permissions('facultymanagement/delete', $client_id);
    
    // Check media type - only test/xml supported currently.
    if(!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
        die;
    }
    $response = array();
    $faculty = new \api\facultymanagement($mysqli);
    // Process the request.
    $request = $api->process('facultymanagement', 'managementrequest');
    $fields = array('id', 'name');
        
    // XML.
    if ($request[0] == 'OK') {
        $actions = array('create', 'delete');
        $response = $api->parse($faculty, $fields, $actions, $request[1], $perm);
        $template = 'api/success.xml';
    } else {
        $response = $request[1];
        $template = 'api/error.xml';
    }

    // Render response.
    $render->render_xml($template, 'facultyManagementResponse', $response);
    
});

$app->post('/usermanagement', function() use($app, $api, $mysqli, $oauth, $render, $langpack) {  
    // Check for auth tokens
    $client_id = $oauth->check_auth();
    
    //Check Permissions
    $perm['create'] = $oauth->check_permissions('usermanagement/create', $client_id);
    $perm['delete'] = $oauth->check_permissions('usermanagement/delete', $client_id);
    
    // Check media type - only test/xml supported currently.
    if(!$api->get_mediatype()) {
        $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
        die;
    }

    $response = array();
    $person = new \api\usermanagement($mysqli);

    // Process the request.
    $request = $api->process('usermanagement', 'managementrequest');
    $fields = array('id', 'username', 'title', 'forename', 'surname', 'initials', 'email', 'password',
        'course', 'gender', 'year', 'role', 'studentid', 'modules');

    if ($request[0] == 'OK') {
            $actions = array('create', 'delete');
            $response = $api->parse($person, $fields, $actions, $request[1], $perm);
            $template = 'api/success.xml';
    } else {
        $response = $request[1];
        $template = 'api/error.xml';
    }
    
    // Render response.
    $render->render_xml($template, 'userManagementResponse', $response);
    
});

$app->notFound(function () use ($app, $api, $render, $langpack) {
    $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '404')));
});

$app->error(function (\Exception $e) use ($app, $api, $render, $langpack) {
    $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '500')));
});

$app->run();