<?php
/**
 * PHP Built-in Web Server wrapper for ZF apps
 */
$reqUri = $_SERVER['REQUEST_URI'];
if (strpos($reqUri, '?') !== false) {
    $reqUri = substr($reqUri, 0, strpos($reqUri, '?'));
}
$target = realpath(__DIR__ . $reqUri);
if ($target && is_file($target)) {
    // Security check: make sure the file is under the public dir
    if (strpos($target, __DIR__) === 0) {
        // Tell PHP to directly serve the requested file
        return false;
    }
}

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();