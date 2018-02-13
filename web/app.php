<?php

use Symfony\Component\HttpFoundation\Request;

ini_set("log_errors", true);
ini_set("error_log", __DIR__ . "/../var/logs/php_error.log");
defined('REPEKA_ENV') || define('REPEKA_ENV', (getenv('REPEKA_ENV') ?: 'prod'));
$loader = require __DIR__ . '/../app/autoload.php';

$kernel = new AppKernel(REPEKA_ENV, REPEKA_ENV === 'dev');
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
