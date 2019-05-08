<?php
namespace Repeka\Tests\Application\Security\Voters\FileVoters;

use Repeka\Application\Security\Voters\FileVoters\ResourceFileExistsInMetadataVoter;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileExistsInMetadataVoterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileExistsInMetadataVoter */
    private $voter;
    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $fileMetadata;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $directoryMetadata;
    /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $nullToken;

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
        $this->voter = new ResourceFileExistsInMetadataVoter($this->metadataRepository);
    }

    /** @dataProvider filePathsCases */
    public function testFilePaths(bool $expected, string $path, array $contents, string $testName) {
        $actual = $this->voter->voteOnAccessToFile($this->nullToken, $this->createResourceMock(1, null, $contents), $path);
        $this->assertEquals($expected, $actual, $testName);
    }

    public function filePathsCases() {
        return [
            [VoterInterface::ACCESS_GRANTED, 'dir/path', [1 => 'dir/path'], 'Granted when file metadata contains path'],
            [VoterInterface::ACCESS_GRANTED, 'dir/path', [2 => 'dir/path'], 'Granted when directory metadata contains path'],
            [VoterInterface::ACCESS_GRANTED, 'dir/path/file', [2 => 'dir/path'], 'Granted when directory metadata contains path fragment'],
            [VoterInterface::ACCESS_DENIED, 'dir/path/file', [1 => 'dir/path'], 'Denied when file metadata contains path fragment'],
            [VoterInterface::ACCESS_DENIED, 'dir/path', [30 => 'dir/path'], 'Denied when other metadata contains path'],
            [
                VoterInterface::ACCESS_DENIED,
                'dir/path/otherFile',
                [2 => 'dir/path/requestedFile'],
                'Denied when metadata has common part with path',
            ],
            [
                VoterInterface::ACCESS_GRANTED,
                'dir/path',
                [1 => ['dir/otherPath1', 'dir/otherPath2', 'dir/path']],
                'Granted when any value contains path',
            ],
            [VoterInterface::ACCESS_DENIED, 'dir/path', [1 => 'dir/path/file'], 'Denied when file metadata contains path'],
            [VoterInterface::ACCESS_DENIED, 'dir/path', [], 'Denied when contents are empty'],
            [VoterInterface::ACCESS_DENIED, '', [2 => 'dir/path'], 'Denied when path is empty'],
        ];
    }
}
