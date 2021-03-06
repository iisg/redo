<?php
namespace Repeka\Plugins\Redo\Tests\Integration;

use Repeka\Plugins\Redo\Authentication\PKSoapService;

class TestPKSoapService extends PKSoapService {
    private const TEST_USERS = [
        'b/012345' => [
            'plainPassword' => 'h4linaRulz',
            'first_name' => 'Halina',
            'last_name' => 'Zięba',
            'email_last_used' => 'halinka@repeka.pl',
            'city' => 'Paczków',
            'street' => 'Taśmowa',
            'institute_desc' => 'Inżynierii Biomedycznej',
        ],
        'b/123456' => ['password' => 'cGlvdHI=', 'first_name' => 'Piotr'], // piotr
        's/123456' => ['plainPassword' => 'pass'],
        '1234567890' => ['plainPassword' => 'pass'],
    ];

    public function __construct() {
    }

    public function getClientDataById(string $userId): array {
        if (isset(self::TEST_USERS[$userId])) {
            return self::TEST_USERS[$userId];
        } else {
            throw new \SoapFault(123, "User $userId does not exist.");
        }
    }

    public function isValidPassword(string $providedByUser, string $cipherText) {
        return base64_encode($providedByUser) == $cipherText;
    }
}
