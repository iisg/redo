<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class UniqueInResourceClassConstraintIntegrationTest extends IntegrationTestCase {

    private $repeatedValue = 'testValue';
    /** @var Metadata */
    private $metadata;
    /** @var ResourceEntity */
    private $resource;

    public function setUp() {
        parent::setUp();
        $this->metadata = $this->createMetadata(
            'unique-metadata',
            ['PL' => 'unikatowa', 'EN' => 'unique'],
            [],
            [],
            'text',
            'books',
            ['uniqueInResourceClass' => true]
        );
        $this->resource = $this->createResource(
            $this->createResourceKind(['PL' => 'test', 'EN' => 'test'], [$this->metadata]),
            [$this->metadata->getId() => $this->repeatedValue]
        );
    }

    public function testConstraintRejectsRepeatedValue() {
        $response = $this->attemptToSaveResource($this->repeatedValue);
        $this->assertStatusCode(400, $response);
    }

    public function testConstraintDifferentiatesSimilarValues() {
        $similarValue1 = 'prefix' . $this->repeatedValue;
        $similarValue2 = $this->repeatedValue . ' some additional words';
        $similarValue3 = substr($this->repeatedValue, 3);
        $response = $this->attemptToSaveResource($similarValue1);
        $this->assertStatusCode(201, $response);
        $response = $this->attemptToSaveResource($similarValue2);
        $this->assertStatusCode(201, $response);
        $response = $this->attemptToSaveResource($similarValue3);
        $this->assertStatusCode(201, $response);
    }

    private function attemptToSaveResource($value): Response {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            '/api/resources',
            [
                'kindId' => $this->resource->getKind()->getId(),
                'contents' => [$this->metadata->getId() => $value],
                'resourceClass' => 'books',
            ]
        );
        return $client->getResponse();
    }
}
