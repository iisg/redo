<?php

use Symfony\Component\HttpFoundation\Request;

ini_set('log_errors', true);
ini_set('error_log', __DIR__ . '/../var/logs/php_error.log');
defined('REPEKA_ENV') || define('REPEKA_ENV', (getenv('REPEKA_ENV') ?: 'prod'));
define('TRUSTED_PROXIES_CONFIG', __DIR__ . '/../var/config/proxy/trusted-proxies.php');
$loader = require __DIR__ . '/../app/autoload.php';
if (file_exists(TRUSTED_PROXIES_CONFIG)) {
    $proxyIps = require TRUSTED_PROXIES_CONFIG;
    Request::setTrustedProxies($proxyIps, Request::HEADER_FORWARDED | Request::HEADER_X_FORWARDED_FOR);
}
$kernel = new AppKernel(REPEKA_ENV, REPEKA_ENV === 'dev');
if (REPEKA_ENV === 'dev') {
    Symfony\Component\Debug\Debug::enable();
    umask(0000);
}
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
