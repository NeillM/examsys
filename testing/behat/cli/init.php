<?php
/* 
 * This file is used to install and upgrade behat for Rogo.
 */

require_once __DIR__ . '/../../composer/composer_utils.php';

// Ensure any caches are cleared.
if (function_exists('opcache_reset')) {
    opcache_reset();
}

chdir(__DIR__);

// Ensure composer and it's dependancies are installed.
composer_utils::setup();

exit(0);
