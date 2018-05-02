<?php
namespace Repeka\Tests\Domain\Utils;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Utils\EntityUtils;

class EntityUtilsTest extends \PHPUnit_Framework_TestCase {
    /** @var Identifiable[] */
    private $entities;
    private $zero;
    private $one;
    private $zet;

    private function entityMock($id): Identifiable {
        $mock = $this->createMock(Identifiable::class);
        $mock->method('getId')->willReturn($id);
        return $mock;
    }

    protected function setUp() {
        $this->entities = [
            $this->zero = $this->entityMock(0),
            $this->one = $this->entityMock(1),
            $this->entityMock('a'),
            $this->entityMock(2),
            $this->zet = $this->entityMock('z'),
        ];
    }

    public function testGetByIds() {
        $ids = [1, 'z', 0];
        $result = EntityUtils::getByIds($ids, $this->entities);
        $this->assertEquals([$this->one, $this->zet, $this->zero], $result);
    }

    public function testGetByIdsThrowsWhenMoEntityMatchesId() {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessageRegExp("/nope/");
        EntityUtils::getByIds(['nope'], $this->entities);
    }

    public function testGetByIdsPreservesKeys() {
        $ids = [1, 'z', 'key' => 0];
        $result = EntityUtils::getByIds($ids, $this->entities);
        $this->assertEquals([$this->one, $this->zet, 'key' => $this->zero], $result);
    }

    public function testGetLookupMap() {
        $map = EntityUtils::getLookupMap($this->entities);
        $this->assertEquals([0, 1, 'a', 2, 'z'], array_keys($map));
        $this->assertEquals($this->zero, $map[0]);
        $this->assertEquals($this->one, $map[1]);
        $this->assertEquals($this->zet, $map['z']);
    }

    public function testMapToIds() {
        $ids = EntityUtils::mapToIds($this->entities);
        $this->assertEquals([0, 1, 'a', 2, 'z'], $ids);
    }

    public function testFilterByIds() {
        $ids = [1, 'z', 0];
        $result = EntityUtils::filterByIds($ids, $this->entities);
        $this->assertEquals([$this->one, $this->zet, $this->zero], $result);
    }

    public function testFilterByIdsCarriesOnWhenNoEntityMatchesId() {
        $ids = [1, 'z', 0, 'nope'];
        $result = EntityUtils::filterByIds($ids, $this->entities);
        $this->assertEquals([$this->one, $this->zet, $this->zero], $result);
    }

    public function testFilterByIdsPreservesKeys() {
        $ids = ['key' => 1, 'nope'];
        $result = EntityUtils::filterByIds($ids, $this->entities);
        $this->assertEquals(['key' => $this->one], $result);
    }

    public function testForceSetId() {
        $sampleEntity = new UserEntity();
        EntityUtils::forceSetId($sampleEntity, 123);
        $this->assertEquals(123, $sampleEntity->getId());
    }
}
