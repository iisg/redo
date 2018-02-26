<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Respect\Validation\Rules\AbstractRule;

class ResourceDoesNotContainDuplicatedFilenamesRule extends AbstractRule {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function validate($input) {
        try {
            $this->assert($input);
            return true;
        } catch (DomainException $e) {
            return false;
        }
    }

    /** @param ResourceContents $resourceContents */
    public function assert($resourceContents) {
        $fileMetadataIds = $this->getPossibleFileMetadataIds();
        $resourceFilenames = $resourceContents->reduceAllValues(
            function ($value, int $metadataId, array $resourceFilenames) use ($fileMetadataIds) {
                if (in_array($metadataId, $fileMetadataIds)) {
                    $resourceFilenames[] = basename($value);
                }
                return $resourceFilenames;
            },
            []
        );
        $unique = array_unique($resourceFilenames);
        if (count($unique) != count($resourceFilenames)) {
            $duplicatedFilenames = array_unique(array_diff_assoc($resourceFilenames, $unique));
            throw new DomainException('duplicatedFilenames', 400, [
                'filenames' => implode(", ", $duplicatedFilenames),
            ]);
        }
    }

    private function getPossibleFileMetadataIds(): array {
        $query = MetadataListQuery::builder()->filterByControl(MetadataControl::FILE())->build();
        $fileMetadata = $this->metadataRepository->findByQuery($query);
        return EntityUtils::mapToIds($fileMetadata);
    }
}
