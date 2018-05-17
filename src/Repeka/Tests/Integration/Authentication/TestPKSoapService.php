<?php
namespace Repeka\Tests\Integration\Authentication;

use Repeka\Application\Authentication\PKSoapService;

class TestPKSoapService extends PKSoapService {
    private const TEST_USERS = [
        'halinka' => [
            'plainPassword' => 'h4linaRulz',
            'first_name' => 'Halina',
            'last_name' => 'Zięba',
            'email_last_used' => 'halinka@repeka.pl',
            'city' => 'Paczków',
            'street' => 'Taśmowa',
            'institute_desc' => 'Inżynierii Biomedycznej',
        ],
        '1234567' => [
            'plainPassword' => 'h4linaRulz',
            'first_name' => 'Halina',
            'last_name' => 'Zięba',
            'email_last_used' => 'halinka@repeka.pl',
            'city' => 'Paczków',
            'street' => 'Taśmowa',
            'institute_desc' => 'Inżynierii Biomedycznej',
        ],
        'budynek' => ['password' => 'cGlvdHI=', 'first_name' => 'Piotr'], // piotr
        '123456' => ['password' => 'cGlvdHI=', 'first_name' => 'Piotr'], // piotr
        'jeanzulu' => ['password' => 'Z290b3RvZ28='], // gototogo
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
