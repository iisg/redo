<?php
namespace Repeka\Tests\Application\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Repository\ContainerAwareRepositoryProvider;
use Repeka\Domain\Repository\RepositoryProvider;

class ContainerAwareRepositoryProviderTest extends \PHPUnit_Framework_TestCase {
    /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $em;
    /** @var RepositoryProvider */
    private $provider;

    protected function setUp() {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->provider = new ContainerAwareRepositoryProvider($this->em);
    }

    public function testReturnsCorrectRepository() {
        $testRepositoryDummy = new \stdClass();
        $this->em->expects($this->once())->method('getRepository')->withConsecutive(['Some\\Test'])->willReturn($testRepositoryDummy);
        $result = $this->provider->getForEntityType('Some\\Test');
        $this->assertSame($testRepositoryDummy, $result);
    }
}
