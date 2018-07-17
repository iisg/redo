<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Repository\MetadataRepository;

class AuditEntryListQueryAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /** @param AuditEntryListQuery $command */
    public function adjustCommand(Command $command): Command {
        return new AuditEntryListQuery(
            $command->getCommandNames(),
            $command->getResourceContentsFilter()->withMetadataNamesMappedToIds($this->metadataRepository),
            $command->getPage(),
            $command->getResultsPerPage(),
            $command->getResourceId()
        );
    }
}
