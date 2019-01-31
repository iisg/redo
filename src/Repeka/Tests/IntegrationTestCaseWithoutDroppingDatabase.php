<?php
namespace Repeka\Tests;

abstract class IntegrationTestCaseWithoutDroppingDatabase extends IntegrationTestCase {
    private static $initializedForTestClass = [];

    protected function setUp() {
    }

    abstract protected function initializeDatabaseBeforeTheFirstTest();

    protected function clearDatabase() {
        if (!isset(self::$initializedForTestClass[static::class])) {
            $this->executeCommand('doctrine:schema:drop --force');
            $this->executeCommand('doctrine:migrations:version --delete --all');
            $this->executeCommand('repeka:initialize --skip-backup');
            $this->initializeDatabaseBeforeTheFirstTest();
            self::$initializedForTestClass[static::class] = true;
        }
    }
}
