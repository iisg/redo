<?php
namespace Repeka\Application\Authentication;

class PKAuthenticationClient {
    private $soapService;

    public function __construct(PKSoapService $soapService) {
        $this->soapService = $soapService;
    }

    public function authenticate(string $login, string $password): bool {
        $userData = $this->fetchUserData($login);
        if (array_key_exists('plainPassword', $userData)) {
            return $this->validatePlainTextPassword($password, $userData['plainPassword']);
        } elseif (array_key_exists('password', $userData)) {
            return $this->validateCipheredPassword($password, $userData['password']);
        } else {
            throw new PKAuthenticationException('No valid authentication method available', $userData);
        }
    }

    public function fetchUserData(string $login): array {
        $userId = $this->getUserId($login);
        try {
            $userData = $this->soapService->getClientDataById($userId);
        } catch (\SoapFault $e) {
            throw new \Exception('Fetching user data failed', 0, $e);
        }
        if (!is_array($userData)) {
            throw new PKAuthenticationException('Remote service responded with invalid data', $userData);
        }
        return $userData;
    }

    private function getUserId($login): string {
        if (strlen($login) < 6) {
            throw new \InvalidArgumentException('Login too short');
        } elseif (strlen($login) == 6) {
            return "B/$login";
        } else {
            return $login;
        }
    }

    private function validatePlainTextPassword(string $clientPassword, string $storedPassword): bool {
        return hash_equals($storedPassword, $clientPassword);
    }

    private function validateCipheredPassword(string $clientPassword, string $ciphertext): bool {
        return $this->soapService->isValidPassword($clientPassword, $ciphertext);
    }
}
