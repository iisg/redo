<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Twig\FrontendConfig;
use Repeka\Application\Twig\Paginator;
use Repeka\Application\Twig\TwigFrontendExtension;
use Repeka\Application\Twig\TwigI18nExtension;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\PrintableArray;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TwigI18nExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    private $request;
    /** @var FrontendConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $frontendConfig;
    /** @var TwigI18nExtension */
    private $extension;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $translatableMetadata;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $otherMetadata;

    /** @before */
    public function init() {
        $this->request = $this->createMock(Request::class);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($this->request);
        $metadataRepository = $this->createMock(MetadataRepository::class);
        $systemLanguageSubmetadata = $this->createMetadataMock(7, null, MetadataControl::SYSTEM_LANGUAGE());
        $systemLanguageSubmetadata2 = $this->createMetadataMock(77, null, MetadataControl::SYSTEM_LANGUAGE());
        $metadataRepository->method('findByQuery')->willReturnCallback(
            function (MetadataListQuery $query) use ($systemLanguageSubmetadata2, $systemLanguageSubmetadata) {
                if ($query->getParent() === $this->translatableMetadata) {
                    return [$systemLanguageSubmetadata, $systemLanguageSubmetadata2];
                } else {
                    return [];
                }
            }
        );
        $this->translatableMetadata = $this->createMetadataMock();
        $this->otherMetadata = $this->createMetadataMock();
        $this->frontendConfig = $this->createMock(FrontendConfig::class);
        $this->extension = new TwigI18nExtension(
            $requestStack,
            $metadataRepository,
            $this->frontendConfig
        );
        $this->request->method('getLocale')->willReturn('pl');
        $this->frontendConfig->method('getConfig')->willReturn(['fallback_ui_languages' => ['en']]);
    }

    public function testInCurrentLanguageArray() {
        $this->assertEquals('ok', $this->extension->inCurrentLanguage(['EN' => 'incorrect', 'PL' => 'ok']));
    }

    public function testInCurrentLanguageArrayFallbackReturned() {
        $this->assertEquals('correct', $this->extension->inCurrentLanguage(['EN' => 'correct', 'FR' => 'incorrecteu']));
    }

    public function testInCurrentLanguageAllReturnedForArrayWithoutLocale() {
        $array = ['FR' => 'valueu', 'ES' => 'el valio'];
        $this->assertEquals($array, $this->extension->inCurrentLanguage($array));
    }

    public function testInCurrentLanguageSimpleValueIsUnchanged() {
        $this->assertSame('PL', $this->extension->inCurrentLanguage('PL'));
        $this->assertSame(123, $this->extension->inCurrentLanguage(123));
        $this->assertSame(null, $this->extension->inCurrentLanguage(null));
        $this->assertSame("", $this->extension->inCurrentLanguage(""));
    }

    public function testInCurrentLanguageMatchesOnlyUppercaseArrayKeys() {
        $this->assertSame('ok', $this->extension->inCurrentLanguage(['PL' => 'ok']));
        $this->assertSame(['pl' => 'not ok'], $this->extension->inCurrentLanguage(['pl' => 'not ok']));
        $this->assertSame(['Pl' => 'not ok'], $this->extension->inCurrentLanguage(['Pl' => 'not ok']));
    }

    public function testInCurrentLanguageReturnsAllValuesWhenNoSystemLanguageSubmetadata() {
        $values = new PrintableArray(
            [
                new MetadataValue('0'),
                new MetadataValue('1'),
            ]
        );
        $this->assertEquals($values, $this->extension->onlyMetadataValuesInCurrentLanguage($values, $this->otherMetadata));
    }

    public function testReturnsAllValuesWithRequestSubmetadata() {
        $value0 = new MetadataValue(['value' => 'correct', 'submetadata' => [7 => [['value' => 'PL']]]]);
        $value1 = new MetadataValue(['value' => 'correct', 'submetadata' => [7 => [['value' => 'PL']]]]);
        $value2 = new MetadataValue(['value' => 'el incorrecto', 'submetadata' => [7 => [['value' => 'ES']]]]);
        $values = new PrintableArray(
            [
                $value0,
                $value1,
                $value2,
            ]
        );
        $expected = new PrintableArray([$value0, $value1]);
        $this->assertEquals($expected, $this->extension->onlyMetadataValuesInCurrentLanguage($values, $this->translatableMetadata));
    }

    public function testReturnsNothingIfNoSubmetadataMatches() {
        $values = new PrintableArray(
            [
                new MetadataValue(['value' => 'incorrect', 'submetadata' => [7 => [['value' => 'EN']]]]),
                new MetadataValue(['value' => 'incorrecteu', 'submetadata' => [7 => [['value' => 'FR']]]]),
            ]
        );
        $this->assertEquals(
            new PrintableArray([]),
            $this->extension->onlyMetadataValuesInCurrentLanguage($values, $this->translatableMetadata)
        );
    }

    public function testIgnoresSubmetadataWithOtherControls() {
        $valueAny1 = new MetadataValue(['value' => 'incorrect', 'submetadata' => [13 => [['value' => 'PL']]]]);
        $valueAny2 = new MetadataValue(['value' => 'incorrect', 'submetadata' => [13 => [['value' => ['array']]]]]);
        $valueSystemLanguage = new MetadataValue(['value' => 'correct', 'submetadata' => [7 => [['value' => 'PL']]]]);
        $values = new PrintableArray(
            [
                $valueAny1,
                $valueAny2,
                $valueSystemLanguage,
            ]
        );
        $this->assertEquals(
            new PrintableArray([$valueSystemLanguage]),
            $this->extension->onlyMetadataValuesInCurrentLanguage($values, $this->translatableMetadata)
        );
    }

    public function testUsesAllSubmetadataValues() {
        $values = new PrintableArray(
            [
                new MetadataValue(['value' => 'correct', 'submetadata' => [7 => [['value' => 'EN'], ['value' => 'PL']]]]),
                new MetadataValue(['value' => 'correct', 'submetadata' => [7 => [['value' => 'EN']], 77 => [['value' => 'PL']]]]),
            ]
        );
        $this->assertEquals($values, $this->extension->onlyMetadataValuesInCurrentLanguage($values, $this->translatableMetadata));
    }

    public function testPrintableArrayCanBeEmpty() {
        $this->assertEquals(
            new PrintableArray([]),
            $this->extension->onlyMetadataValuesInCurrentLanguage(new PrintableArray([]), $this->translatableMetadata)
        );
    }

    public function testDoesReturnsOneValueIfMultipleSubmetadataValues() {
        $values = new PrintableArray(
            [
                new MetadataValue(['value' => 'correct', 'submetadata' => [7 => [['value' => 'PL'], ['value' => 'PL']]]]),
            ]
        );
        $this->assertEquals($values, $this->extension->onlyMetadataValuesInCurrentLanguage($values, $this->translatableMetadata));
    }
}
