<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\MetadataConstraints\SystemLanguageExistsConstraint;
use Repeka\Tests\Traits\StubsTrait;

class SystemLanguageExistsConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var SystemLanguageExistsConstraint */
    private $rule;
    /** @var Metadata */
    private $metadata;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    public function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->metadata = $this->createMetadataMock(1, null, MetadataControl::SYSTEM_LANGUAGE(), []);
        $this->languageRepository->method('getAvailableLanguageCodes')->willReturn(['PL', 'EN']);
        $this->rule = new SystemLanguageExistsConstraint(
            $this->languageRepository
        );
        $this->resource = $this->createResourceMock(1);
    }

    public function testAcceptMetadataWithoutLanguages() {
        $this->rule->validateAll($this->metadata, [], $this->resource);
    }

    public function testAcceptMetadataWithAvailableLanguages() {
        $this->rule->validateAll($this->metadata, [1 => "PL", 2 => "EN"], $this->resource);
    }

    public function testRejectMetadataWithUnavailableLanguage() {
        $this->expectException(RespectValidationFailedException::class);
        $this->rule->validateAll($this->metadata, [1 => "EN", 2 => "AA"], $this->resource);
    }

    public function testRejectMetadataWithUnavailableLanguages() {
        $this->expectException(RespectValidationFailedException::class);
        $this->rule->validateAll($this->metadata, [1 => "AA", 2 => "CC"], $this->resource);
    }
}
