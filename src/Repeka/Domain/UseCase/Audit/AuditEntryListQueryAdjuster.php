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
        $adjustedQuery = new AuditEntryListQuery(
            $command->getCommandNames(),
            $command->getDateFrom(),
            $command->getDateTo(),
            $command->getUsers(),
            $command->getResourceKinds(),
            $this->addQuotes($command->getTransitions()),
            $command->getResourceContentsFilter()->withMetadataNamesMappedToIds($this->metadataRepository),
            $command->getPage(),
            $command->getResultsPerPage(),
            $command->getResourceId()
        );
        return $adjustedQuery;
    }

    private function addQuotes(array $transitions): array {
        array_walk(
            $transitions,
            function (&$x) {
                $x = "\"$x\"";
            }
        );
        return $transitions;
    }
}
