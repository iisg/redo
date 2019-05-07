<?php
namespace Repeka\Tests\Application\Security;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/** @small */
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

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->oracle = $this->container->get(SecurityOracle::class);
        $this->executeCommand('repeka:create-user ziom ziom');
        $this->ziomUser = $this->getUserByName('ziom');
        $this->ziomUserToken = $this->simulateAuthentication($this->ziomUser);
        $this->adminUser = $this->getUserByName('admin');
        $this->adminUserToken = $this->simulateAuthentication($this->adminUser);
        $titleMetadata = $this->findMetadataByName('tytuł');
        $query = ResourceListQuery::builder()->filterByContents([$titleMetadata->getId() => 'php i mysql'])->build();
        $this->phpBook = $this->getResourceRepository()->findByQuery($query)[0];
    }

    public function testAdminCanAccessFile() {
        $this->assertTrue(
            $this->oracle->hasPermission(['resource' => $this->phpBook, 'filepath' => 'dir/path'], 'FILE_DOWNLOAD', $this->adminUserToken)
        );
    }

    public function testTesterCannotAccessFileWhenFileIsNotInMetadata() {
        $this->assertFalse(
            $this->oracle->hasPermission(['resource' => $this->phpBook, 'filepath' => 'dir/path'], 'FILE_DOWNLOAD', $this->ziomUserToken)
        );
    }

    public function testTesterCanAccessFileWhenFileIsInMetadata() {
        $fileMetadata = $this->findMetadataByName('plik_txt');
        $newContents = $this->phpBook->getContents()->withReplacedValues($fileMetadata, 'dir/path');
        $this->phpBook->updateContents($newContents);
        $this->getResourceRepository()->save($this->phpBook);
        $this->assertTrue(
            $this->oracle->hasPermission(['resource' => $this->phpBook, 'filepath' => 'dir/path'], 'FILE_DOWNLOAD', $this->ziomUserToken)
        );
    }
}
