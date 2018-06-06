<?php
namespace Repeka\Application\Authentication;

class PKAuthenticationClient {
    private $soapService;

    public function __construct(PKSoapService $soapService) {
        $this->soapService = $soapService;
    }

    public function authenticate(string $username, string $password): bool {
        if (!$this->isUsernameSupported($username)) {
            return false;
        }
        $userData = $this->fetchUserData($username);
        if (array_key_exists('plainPassword', $userData)) {
            return $this->validatePlainTextPassword($password, $userData['plainPassword']);
        } elseif (array_key_exists('password', $userData)) {
            return $this->validateCipheredPassword($password, $userData['password']);
        } else {
            throw new PKAuthenticationException('No valid authentication method available', $userData);
        }
    }

    private function isUsernameSupported($username): bool {
        return boolval(preg_match('#^([bs]/\d{6}|\d{10})$#i', $username));
    }

    public function fetchUserData(string $username): array {
        try {
            $userData = $this->soapService->getClientDataById($username);
        } catch (\SoapFault $e) {
            throw new \Exception('Fetching user data failed', 0, $e);
        }
        if (!is_array($userData)) {
            throw new PKAuthenticationException('Remote service responded with invalid data', $userData);
        }
        return $userData;
    }

    private function validatePlainTextPassword(string $clientPassword, string $storedPassword): bool {
        return hash_equals($storedPassword, $clientPassword);
    }

    private function validateCipheredPassword(string $clientPassword, string $ciphertext): bool {
        return $this->soapService->isValidPassword($clientPassword, $ciphertext);
    }
}
