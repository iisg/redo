<?php
// @codingStandardsIgnoreFile

error_reporting(E_ALL);

$CONFIG = require('config.php');
define('CONFIG', $CONFIG);

class RequestHandler
{
    public function __construct() {
        $login = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $httpCredentials = CONFIG['serverHttpCredentials'];
        $loginStatus = array_key_exists($login, $httpCredentials);
        $passwordStatus = $loginStatus && $httpCredentials[$login] == $password;

        if (!$loginStatus) {
            $this->soapError(0, "Invalid login '$login'");
        } elseif (!$passwordStatus) {
            $this->soapError(0, "Invalid password '$password' for login '$login'");
        } else {
            $this->log("Successful auth as $login/$password");
        }
    }

    private function log(string $message) {
        error_log($message, 4);
    }

    private function soapError(int $code, string $message) {
        $this->log($message);
        throw new SoapFault($code, $message);
    }

    private function getResponseFromConfig($userId) {
        $userSpecific = CONFIG['userSpecificResponses'];
        if (array_key_exists($userId, $userSpecific)) {
            $response = $userSpecific[$userId];
            $response['__fallback'] = false;
        } else {
            $response = CONFIG['fallbackResponse'];
            $response['__fallback'] = true;
        }
        $response['__userId'] = $userId;
        return $response;
    }

    public function getClientDataById($userId) {
        $this->log("getClientDataById: $userId");
        $response = $this->getResponseFromConfig($userId);
        return $response;
    }

    public function isValidPassword($plain, $encrypted) {
        $matches = base64_encode($plain) == $encrypted;
        $statusString = $matches ? 'ok' : 'fail';
        $this->log("isValidPassword: $plain vs. $encrypted [$statusString]");
        return $matches;
    }
}

$server = new SoapServer($CONFIG['wsdlUrl'], $CONFIG['serverOptions']);
$server->setClass(RequestHandler::class);
$server->handle();
