<?php
$config = [
    // ---=== COMMON CONFIG ===---
    // SOAP options - will be passed to SoapClient or SoapServer
    'wsdlUrl' => null,//'http://soap.biblos.pk.edu.pl/soap.php?WSDL',
    'commonOptions' => [
        'uri' => 'test',
    ],
    'clientOptions' => [
        'location' => 'http://localhost/',
        'login' => 'budynek',
        'password' => 'alamakota',
        'cache_wsdl' => WSDL_CACHE_NONE,
    ],
    // ---=== SERVER CONFIG ===---
    // HTTP basic auth credentials. SOAP Client must authenticate with one of these, otherwise it will be rejected.
    'serverHttpCredentials' => [
        'budynek' => 'alamakota',
        'admin' => 'admin',
        'pk' => 'qwerty',
    ],
    // Canned responses. If a key matching username is found, it will be used
    'userSpecificResponses' => [
        'halinka' => ['plainPassword' => 'h4linaRulz', 'first_name' => 'Halina', 'last_name' => 'Zięba', 'email_last_used' => 'halinka@repeka.pl', 'city' => 'Paczków',
            'street' => 'Taśmowa', 'institute_desc' => 'Inżynierii Biomedycznej'],
        'budynek' => ['password' => 'cGlvdHI=', 'first_name' => 'Piotr'], // piotr
        'jeanzulu' => ['password' => 'Z290b3RvZ28='], // gototogo
    ],
    // Canned response used if user-specific response isn't found
    'fallbackResponse' => [],
    // ---=== CLIENT CONFIG ===---
    // Client will pass these to ValidLogin() method (mind special handling for 6-char logins)
    'loginCredentials' => [
        'login' => 'halinka',
        'password' => 'h4linaRulz',
    ],
];
$config['clientOptions'] = array_merge($config['commonOptions'] ?? [], $config['clientOptions'] ?? []);
$config['serverOptions'] = array_merge($config['commonOptions'] ?? [], $config['serverOptions'] ?? []);
return $config;
