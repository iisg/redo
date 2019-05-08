<?php
namespace Repeka\Tests\Application\Security;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Application\Security\Voters\FileDownloadVoter;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * All tests in this class rely heavily on
 * fixtures and current roles configuration
 */
class ResourceFilePermissionIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var SecurityOracle */
    private $oracle;
    /** @var User */
    private $ziomUser;
    /** @var User */
    private $adminUser;
    /** @var TokenInterface */
    private $ziomUserToken;
    /** @var TokenInterface */
    private $adminUserToken;
    /** @var ResourceEntity */
    private $phpBook;
    /** @var ResourceEntity */
    private $phpItCanBeTreated;
    /** @var ResourceEntity */
    private $invisibleResource;
    /** @var ResourceEntity */
    private $kissResource;
    /** @var Metadata */
    private $fileMetadata;
    /** @var FileSystemDriver */
    private $fileSystemDriver;
    /** @var ResourceFileStorage */
    private $resourceFileStorage;
    private const FILE_PATH = 'resourceFiles/file';
    private $resourcesPaths = [];

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->fileSystemDriver = $this->container->get(FileSystemDriver::class);
        $this->resourceFileStorage = $this->container->get(ResourceFileStorage::class);
        $this->oracle = $this->container->get(SecurityOracle::class);
        $this->executeCommand('repeka:create-user ziom ziom');
        $this->ziomUser = $this->getUserByName('ziom');
        $this->ziomUserToken = $this->simulateAuthentication($this->ziomUser);
        $this->adminUser = $this->getUserByName('admin');
        $this->adminUserToken = $this->simulateAuthentication($this->adminUser);
        $titleMetadata = $this->findMetadataByName('tytuł');
        $this->phpBook = $this->findResourceByContents([$titleMetadata->getId() => 'php i mysql']);
        $this->phpItCanBeTreated = $this->getPhpBookResource();
        $this->kissResource = $this->findResourceByContents([$titleMetadata->getId() => 'KISS']);
        $this->fileMetadata = $this->findMetadataByName('plik_txt');
        $resourceKind = $this->createResourceKind('rk', ['PL' => 'rk', 'EN' => 'rk'], [$titleMetadata]);
        $this->invisibleResource = $this->createResource($resourceKind, []);
        $this->addFilePathToMetadata($this->phpItCanBeTreated);
        $this->addFilePathToMetadata($this->invisibleResource);
        $this->addFilePathToMetadata($this->kissResource);
        $this->prepareFilesForResources();
    }

    private function prepareFilesForResources() {
        $phpBookPath = $this->resourceFileStorage->getFileSystemPath($this->phpBook, self::FILE_PATH);
        $this->fileSystemDriver->putContents($phpBookPath, 'nene');
        $phpItCanBeTreatedPath = $this->resourceFileStorage->getFileSystemPath($this->phpItCanBeTreated, self::FILE_PATH);
        $this->fileSystemDriver->putContents($phpItCanBeTreatedPath, 'nene');
        $invisibleResourcePath = $this->resourceFileStorage->getFileSystemPath($this->invisibleResource, self::FILE_PATH);
        $this->fileSystemDriver->putContents($invisibleResourcePath, 'nene');
        $kissResourcePath = $this->resourceFileStorage->getFileSystemPath($this->kissResource, self::FILE_PATH);
        $this->fileSystemDriver->putContents($kissResourcePath, 'nene');
        $this->resourcesPaths = [$phpBookPath, $phpItCanBeTreatedPath, $invisibleResourcePath, $kissResourcePath];
    }

    private function addFilePathToMetadata(ResourceEntity $resource) {
        $newContents = $resource->getContents()->withReplacedValues($this->fileMetadata, self::FILE_PATH);
        $resource->updateContents($newContents);
        $this->persistAndFlush($resource);
    }

    /** @small */
    public function testAdminCanAccessFileWhenFileIsNotInMetadata() {
        $client = self::createAdminClient();
        $endpoint = 'api/resources/' . $this->phpBook->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testBudynekOperatorCanAccessFileWhenFileIsNotInMetadata() {
        $client = self::createAuthenticatedClient('budynek', 'budynek');
        $endpoint = 'api/resources/' . $this->phpBook->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    /** @small */
    public function testZiomCannotAccessFileWhenFileIsNotInMetadata() {
        $client = self::createAuthenticatedClient('ziom', 'ziom');
        $endpoint = 'api/resources/' . $this->phpBook->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testAdminCanAccessFileWhenResourceIsNotVisible() {
        $client = self::createAdminClient();
        $endpoint = 'api/resources/' . $this->invisibleResource->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    /** @small */
    public function testOperatorCannotAccessFileOfResourceHeCannotView() {
        $client = self::createAuthenticatedClient('budynek', 'budynek');
        $endpoint = 'api/resources/' . $this->invisibleResource->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(403, $client->getResponse());
    }

    /** @small */
    public function testUnauthenticatedUserCannotAccessFileWhenResourceIsNotVisible() {
        $client = self::createClient();
        $endpoint = 'api/resources/' . $this->phpBook->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testAdminCanAccessFileWhenIncorrectIp() {
        $client = self::createAdminClient();
        $endpoint = 'api/resources/' . $this->phpItCanBeTreated->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    /** @small */
    public function testZiomCannotAccessFileWhenIncorrectIp() {
        $client = self::createAuthenticatedClient('ziom', 'ziom');
        $endpoint = 'api/resources/' . $this->phpItCanBeTreated->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testZiomCanAccessFileWhenCorrectIp() {
        $client = self::createAuthenticatedClient('ziom', 'ziom', [], ['REMOTE_ADDR' => '0.0.0.0']);
        $endpoint = 'api/resources/' . $this->phpItCanBeTreated->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    /** @small */
    public function testZiomCanAccessFilesWithoutIp() {
        $this->simulateAuthentication($this->ziomUser);
        $can = $this->oracle->hasPermission(['resource' => $this->phpItCanBeTreated], FileDownloadVoter::FILE_DOWNLOAD_ATTRIBUTE);
        $this->assertTrue($can);
    }

    public function testPreventsAccessIfInvalidResourceDownloadQuery() {
        $this->simulateAuthentication($this->ziomUser);
        // query only on resource - invalid!
        $can = $this->oracle->hasPermission($this->phpItCanBeTreated, FileDownloadVoter::FILE_DOWNLOAD_ATTRIBUTE);
        $this->assertFalse($can);
    }

    /** @small */
    public function testUnauthenticatedUserCannotAccessFileWhenCorrectIpAndOnlyForAuthenticated() {
        $client = self::createClient();
        $endpoint = 'api/resources/' . $this->kissResource->getId() . '/file/' . self::FILE_PATH;
        $client->apiRequest('GET', $endpoint, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(403, $client->getResponse());
    }

    protected function tearDown() {
        parent::tearDown();
        foreach ($this->resourcesPaths as $path) {
            $this->fileSystemDriver->delete($path);
        }
    }
}
