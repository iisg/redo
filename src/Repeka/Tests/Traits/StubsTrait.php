<?php
namespace Repeka\Tests\Traits;

use Repeka\Domain\Repository\LanguageRepository;

/**
 * @method \PHPUnit_Framework_MockObject_MockObject createMock(string $originalClassName)
 */
trait StubsTrait {
    protected function createLanguageRepositoryMock(array $languages): LanguageRepository {
        $mock = $this->createMock(LanguageRepository::class);
        $mock->method('getAvailableLanguageCodes')->willReturn($languages);
        return $mock;
    }
}
