<?php
// @codingStandardsIgnoreFile

include('TinlibUserDataLoader.php');

$CONFIG = require('config.php');
$credentials = $CONFIG['loginCredentials'];

$result = TinlibUserDataLoader::ValidLogin($credentials['login'], $credentials['password']);
echo $result ? "Login successful\n" : "Login failed\n";
