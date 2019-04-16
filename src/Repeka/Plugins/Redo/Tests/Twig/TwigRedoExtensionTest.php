<?php
namespace Repeka\Plugins\Redo\Tests\Twig;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Plugins\Redo\Authentication\UserDataMapping;
use Repeka\Plugins\Redo\Twig\TwigRedoExtension;
use Repeka\Tests\Traits\StubsTrait;

class TwigRedoExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var TwigRedoExtension */
    private $extension;

    /** @before */
    public function init() {
        $userDataMapping = $this->createMock(UserDataMapping::class);
        $this->extension = new TwigRedoExtension($userDataMapping, $this->createMock(SecurityOracle::class));
    }

    public function testInsertLink() {
        $value = new MetadataValue("Test phrase.");
        $links = [new MetadataValue(['value' => 'Test phrase', 'submetadata' => [10 => ['http://testphrase.com']]])];
        $actual = $this->extension->insertLinks($value, $links);
        $this->assertEquals('<a href="http://testphrase.com">Test phrase</a>.', $actual);
    }

    public function testInsertsMultipleLinks() {
        $value = new MetadataValue("Test phrase. Second phrase. some keyword here");
        $links = [
            new MetadataValue(['value' => 'Test phrase', 'submetadata' => [10 => ['http://testphrase.com']]]),
            new MetadataValue(['value' => 'keyword', 'submetadata' => [10 => ['http://firstlinkinsearch.com']]]),
        ];
        $actual = $this->extension->insertLinks($value, $links);
        $expected = '<a href="http://testphrase.com">Test phrase</a>. Second phrase. '
            . 'some <a href="http://firstlinkinsearch.com">keyword</a> here';
        $this->assertEquals($expected, $actual);
    }
}
