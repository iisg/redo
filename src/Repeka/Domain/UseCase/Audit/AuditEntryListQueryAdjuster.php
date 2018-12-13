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

    public function isDate(string $date): bool {
        return (date('Y-m-d', strtotime($date)) == $date);
    }

    public function adjustDateFrom(string $dateFrom): string {
        return $this->isDate($dateFrom) ? $dateFrom : "";
    }

    public function adjustDateTo(string $dateTo): string {
        return $this->isDate($dateTo) ? date('Y-m-d', strtotime('+1 day', strtotime($dateTo))) : "";
    }

    /** @param AuditEntryListQuery $command */
    public function adjustCommand(Command $command): Command {
        return new AuditEntryListQuery(
            $command->getCommandNames(),
            $this->adjustDateFrom($command->getDateFrom()),
            $this->adjustDateTo($command->getDateTo()),
            $command->getResourceContentsFilter()->withMetadataNamesMappedToIds($this->metadataRepository),
            $command->getPage(),
            $command->getResultsPerPage(),
            $command->getResourceId()
        );
    }
}
