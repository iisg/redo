<?php
namespace Repeka\Tests\Domain\Validation\Strippers;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Validation\Strippers\UnknownMetadataGroupStripper;
use Repeka\Tests\Traits\StubsTrait;

class UnknownMetadataGroupStripperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    /** @var UnknownMetadataGroupStripper */
    private $unknownMetadataGroupStripper;

    protected function setUp() {
        $this->unknownMetadataGroupStripper = new UnknownMetadataGroupStripper([['id' => 'a'], ['id' => 'b']]);
    }

    /** @dataProvider groupTestCases */
    public function testGettingSupportedGroup($groupId, $expectedGroupId) {
        $this->assertEquals($expectedGroupId, $this->unknownMetadataGroupStripper->getSupportedMetadataGroup($groupId));
    }

    public function groupTestCases() {
        return [
            ['a', 'a'],
            ['b', 'b'],
            ['c', Metadata::DEFAULT_GROUP],
            [Metadata::DEFAULT_GROUP, Metadata::DEFAULT_GROUP],
            ['', Metadata::DEFAULT_GROUP],
            [null, Metadata::DEFAULT_GROUP],
        ];
    }
}
