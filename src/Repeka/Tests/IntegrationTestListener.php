<?php
namespace Repeka\Tests;

class IntegrationTestListener extends \PHPUnit_Framework_BaseTestListener {
    public function startTest(\PHPUnit_Framework_Test $test) {
        if ($test instanceof IntegrationTestCase) {
            $test->prepareIntegrationTest();
        }
    }
}
