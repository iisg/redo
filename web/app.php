<?php
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

ini_set("log_errors", true);
ini_set("error_log", __DIR__ . "/../var/logs/php_error.log");
defined('REPEKA_ENV') || define('REPEKA_ENV', (getenv('REPEKA_ENV') ?: 'prod'));
$loader = require __DIR__ . '/../app/autoload.php';
if (REPEKA_ENV === 'prod') {
    include_once __DIR__ . '/../var/bootstrap.php.cache';
} else if (REPEKA_ENV === 'dev') {
    Debug::enable();
}
$kernel = new AppKernel(REPEKA_ENV, REPEKA_ENV === 'dev');
if (REPEKA_ENV === 'prod') {
    $kernel->loadClassCache();
}
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
