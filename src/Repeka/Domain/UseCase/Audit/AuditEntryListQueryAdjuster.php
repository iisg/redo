<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Repository\MetadataRepository;
use DateTime;

class AuditEntryListQueryAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;

    private const MOMENT_DATE_FORMAT = 'Y-m-d\TH:i:s';

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function isDate(string $date): bool {
        return (date(self::MOMENT_DATE_FORMAT, strtotime($date)) == $date);
    }

    public function adjustDateFrom(string $dateFrom): string {
        return $this->isDate($dateFrom) ? $dateFrom : "";
    }

    public function adjustDateTo(string $dateTo): string {
        return $this->isDate($dateTo) ? date(self::MOMENT_DATE_FORMAT, strtotime('+1 day', strtotime($dateTo))) : "";
    }

    /** @param AuditEntryListQuery $command */
    public function adjustCommand(Command $command): Command {
        return new AuditEntryListQuery(
            $command->getCommandNames(),
            $this->adjustDateFrom($command->getDateFrom()),
            $this->adjustDateTo($command->getDateTo()),
            $command->getUsers(),
            $command->getResourceContentsFilter()->withMetadataNamesMappedToIds($this->metadataRepository),
            $command->getPage(),
            $command->getResultsPerPage(),
            $command->getResourceId()
        );
    }
}
