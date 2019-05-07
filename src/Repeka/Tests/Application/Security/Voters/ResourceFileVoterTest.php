<?php
namespace Repeka\Tests\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\Voters\ResourceFileVoter;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ResourceFileVoterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileVoter */
    private $voter;
    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $fileMetadata;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $directoryMetadata;
    /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $nullToken;
    /** @var UserEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $user;
    /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $userToken;

    public function setUp() {
        $this->fileMetadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->directoryMetadata = $this->createMetadataMock(2, null, MetadataControl::DIRECTORY());
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->metadataRepository->method('findByQuery')->willReturnCallback(
            function (MetadataListQuery $query) {
                $metadata = [];
                if (in_array(MetadataControl::FILE(), $query->getControls())) {
                    $metadata[] = $this->fileMetadata;
                }
                if (in_array(MetadataControl::DIRECTORY(), $query->getControls())) {
                    $metadata[] = $this->directoryMetadata;
                }
                return $metadata;
            }
        );
        $this->nullToken = $this->createMock(TokenInterface::class);
        $this->voter = new ResourceFileVoter($this->metadataRepository);
        $this->user = new UserEntity();
        $this->userToken = $this->createMock(TokenInterface::class);
        $this->userToken->method('getUser')->willReturn($this->user);
    }

    /** @dataProvider filePathsCases */
    public function testFilePaths(bool $expected, string $path, array $contents, string $testName) {
        $actual = $this->voter->voteOnAttribute('FILE_DOWNLOAD', $this->subject($contents, $path), $this->nullToken);
        $this->assertEquals($expected, $actual, $testName);
    }

    public function filePathsCases() {
        return [
            [true, 'dir/path', [1 => 'dir/path'], 'Granted when file metadata contains path'],
            [true, 'dir/path', [2 => 'dir/path'], 'Granted when directory metadata contains path'],
            [true, 'dir/path/file', [2 => 'dir/path'], 'Granted when directory metadata contains path fragment'],
            [false, 'dir/path/file', [1 => 'dir/path'], 'Denied when file metadata contains path fragment'],
            [false, 'dir/path', [30 => 'dir/path'], 'Denied when other metadata contains path'],
            [false, 'dir/path/otherFile', [2 => 'dir/path/requestedFile'], 'Denied when metadata has common part with path'],
            [true, 'dir/path', [1 => ['dir/otherPath1', 'dir/otherPath2', 'dir/path']], 'Granted when any value contains path'],
            [false, 'dir/path', [1 => 'dir/path/file'], 'Denied when file metadata contains path'],
            [false, 'dir/path', [], 'Denied when contents are empty'],
            [false, '', [2 => 'dir/path'], 'Denied when path is empty'],
        ];
    }

    /** @dataProvider userRolesCases */
    public function testUserRoles(bool $expected, array $roles, string $testName) {
        $this->user->updateRoles($roles);
        $actual = $this->voter->voteOnAttribute('FILE_DOWNLOAD', $this->subject([], 'dir/path'), $this->userToken);
        $this->assertEquals($expected, $actual, $testName);
    }

    public function userRolesCases() {
        return [
            [true, ['ADMIN-books'], 'Granted when user is admin'],
            [true, ['OPERATOR-books'], 'Granted when user is operator'],
            [false, ['any-role'], 'Denied when user has only other roles'],
        ];
    }

    private function subject($contents, $path) {
        $resource = $this->createResourceMock(1, null, $contents);
        return ['resource' => $resource, 'filepath' => $path];
    }
}
