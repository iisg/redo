<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class ResourceMaxCountConstraintsIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/resources';

    /** @var Metadata */
    private $baseMetadata;

    private $resourceKindMax1;
    private $resourceKindMax3;

    public function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        $this->baseMetadata = $this->createMetadata('Base', ['TEST' => 'Base metadata'], [], [], 'textarea');
        $this->resourceKindMax1 = $this->getCountConstrainedResourceKind(1);
        $this->resourceKindMax3 = $this->getCountConstrainedResourceKind(3);
    }

    private function getCountConstrainedResourceKind(int $max): ResourceKind {
        return $this->createResourceKind(['TEST' => 'Resource kind'], [
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata'], 'books', $max),
        ]);
    }

    private function makeRequest(ResourceKind $resourceKind, array $values): Response {
        $client = self::createAdminClient();
        $client->apiRequest('POST', ResourceIntegrationTest::ENDPOINT, [
            'kind_id' => $resourceKind->getId(),
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
