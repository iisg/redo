<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\MetadataConstraints\SystemLanguageExistsConstraint;
use Repeka\Tests\Traits\StubsTrait;

class SystemLanguageExistsConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject * */
    private $languageRepository;
    /** @var SystemLanguageExistsConstraint * */
    private $rule;
    /** @var Metadata * */
    private $metadata;

    public function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->metadata = $this->createMetadataMock(1, null, MetadataControl::SYSTEM_LANGUAGE(), []);
        $this->languageRepository->method('getAvailableLanguageCodes')->willReturn(['PL', 'EN']);
        $this->rule = new SystemLanguageExistsConstraint(
            $this->languageRepository
        );
    }

    public function testAcceptMetadataWithoutLanguages() {
        $this->rule->validateAll($this->metadata, null, []);
    }

    public function testAcceptMetadataWithAvailableLanguages() {
        $this->rule->validateAll($this->metadata, null, [1 => "PL", 2 => "EN"]);
    }

    public function testRejectMetadataWithUnavailableLanguage() {
        $this->expectException(RespectValidationFailedException::class);
        $this->rule->validateAll($this->metadata, null, [1 => "EN", 2 => "AA"]);
    }

    public function testRejectMetadataWithUnavailableLanguages() {
        $this->expectException(RespectValidationFailedException::class);
        $this->rule->validateAll($this->metadata, null, [1 => "AA", 2 => "CC"]);
    }
}
