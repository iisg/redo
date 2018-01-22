<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class ResourceMaxCountConstraintsIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/resources';

    /** @var Metadata */
    private $baseMetadata;
    /** @var Metadata */
    private $baseMetadata2;
    /** @var  Metadata */
    private $parentMetadata;

    private $resourceKindMax1;
    private $resourceKindMax3;

    public function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->parentMetadata = $metadataRepository->findOne(SystemMetadata::PARENT);
        $this->baseMetadata = $this->createMetadata('Base', ['TEST' => 'Base metadata'], [], [], 'textarea');
        $this->baseMetadata2 = $this->createMetadata('Base2', ['TEST' => 'Base metadata2'], [], [], 'textarea');
        $this->resourceKindMax1 = $this->getCountConstrainedResourceKind(1);
        $this->resourceKindMax3 = $this->getCountConstrainedResourceKind(3);
    }

    private function getCountConstrainedResourceKind(int $max): ResourceKind {
        return $this->createResourceKind(['TEST' => 'Resource kind'], [
            $this->resourceKindMetadata($this->parentMetadata, ['TEST' => 'Metadata'], ''),
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata'], 'books', $max),
            $this->resourceKindMetadata($this->baseMetadata2, ['TEST' => 'Metadata2'], 'books', $max),
        ]);
    }

    private function makeRequest(ResourceKind $resourceKind, array $values): Response {
        $client = self::createAdminClient();
        $client->apiRequest('POST', ResourceIntegrationTest::ENDPOINT, [
            'kindId' => $resourceKind->getId(),
            'contents' => json_encode([$this->baseMetadata->getId() => $values]),
            'resourceClass' => 'books',
        ]);
        return $client->getResponse();
    }

    public function testRespectingUpperCountConstraints1() {
        // positive
        $response = $this->makeRequest($this->resourceKindMax1, []);
        $this->assertStatusCode(201, $response);
        $response = $this->makeRequest($this->resourceKindMax1, ['foo']);
        $this->assertStatusCode(201, $response);
        // negative
        $response = $this->makeRequest($this->resourceKindMax1, ['foo', 'bar']);
        $this->assertStatusCode(400, $response);
    }

    public function testRespectingUpperCountConstraintsOver1() {
        // positive
        $response = $this->makeRequest($this->resourceKindMax3, []);
        $this->assertStatusCode(201, $response);
        $response = $this->makeRequest($this->resourceKindMax3, ['foo']);
        $this->assertStatusCode(201, $response);
        $response = $this->makeRequest($this->resourceKindMax3, ['foo', 'bar', 'baz']);
        $this->assertStatusCode(201, $response);
        // negative
        $response = $this->makeRequest($this->resourceKindMax3, ['foo', 'bar', 'baz', 'quux']);
        $this->assertStatusCode(400, $response);
    }

    public function testRespectingNoLowerCountConstraints() {
        $response = $this->makeRequest($this->resourceKindMax1, []);
        $this->assertStatusCode(201, $response);
        $response = $this->makeRequest($this->resourceKindMax3, []);
        $this->assertStatusCode(201, $response);
    }
}
