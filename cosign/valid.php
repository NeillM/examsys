<?php
// Copyright (C) 2010 FIT Brno University of Technology
// All Rights Reserved. See LICENSE.
// Petr Lampa <lampa@fit.vutbr.cz>
// $Id: valid,v 1.1 2010/03/01 10:32:16 lampa Exp $
// vi:set ts=8 sts=4 sw=4:

// Cosign validation service /cosign/valid
// Setup .htaccess to run php for this URI
// RewriteEngine On
// RewriteRule valid valid.php

// Modified to fit ExamSys authentication by Richard Aspden

require_once __DIR__ . '/../include/load_config.php';
// The cosign class is not available via autoloading.
require_once("cosign.class.php");

// Get the CoSign config array
foreach ($configObject->getbyref('authentication') as $auth_method) {
    if ($auth_method[0] != 'cosign') {
        continue;
    }
    $cosign_auth_config = $auth_method[1];
    break;
}

if (!isset($cosign_auth_config)) {
    // Cosign is unconfigured on the server.
    header("HTTP/1.0 501 Not Implemented");
    echo "Cosign is not enabled";
    exit();
}

$cosign = new cosign($cosign_auth_config, null);

if (!$cosign->cosign_auth()) {
    error_log("cosign valid service failed");
    header("HTTP/1.0 503 Service Temporarily Unavailable");
    echo "Cosign validation service failed";
    exit();
}// if success, request was already redirected
