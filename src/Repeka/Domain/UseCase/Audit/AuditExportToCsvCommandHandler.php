<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\UseCase\PageResult;

class AuditExportToCsvCommandHandler {
    /** @var CommandBus */
    private $commandBus;
    private $resultsPerPage = 200;
    /** @var AuditEntryCustomColumnsEvaluator */
    private $auditEntryCustomColumnsEvaluator;

    public function __construct(CommandBus $commandBus, AuditEntryCustomColumnsEvaluator $auditEntryCustomColumnsEvaluator) {
        $this->commandBus = $commandBus;
        $this->auditEntryCustomColumnsEvaluator = $auditEntryCustomColumnsEvaluator;
    }

    public function handle(AuditExportToCsvCommand $command) {
        $queryBuilder = $command->getAuditEntryListQueryBuilder();
        $queryBuilder->setResultsPerPage($this->resultsPerPage);
        $queryBuilder->setPage(1);
        $query = $queryBuilder->build();
        /** @var PageResult $entries */
        $entries = $this->commandBus->handle($query);
        $file = $this->openFile($query->getResourceId());
        $this->addColumnHeaders($file, $command->getCustomColumns());
        while ($entries->getResults()) {
            $this->saveToFile($file, $entries->getResults(), $command->getCustomColumns());
            $nextPage = $queryBuilder->build()->getPage() + 1;
            $queryBuilder->setPage($nextPage);
            $entries = $this->commandBus->handle($queryBuilder->build());
        }
        $this->closeFile($file);
    }

    private function openFile(int $resourceId) {
        $fileName = $this->getFileName($resourceId);
        $outputFileName = sprintf(
            '%s/audit/%s.csv',
            \AppKernel::VAR_PATH,
            $fileName
        );
        return fopen($outputFileName, 'w');
    }

    private function getFileName(int $resourceId): string {
        $date = date("Y-m-d\TH-i", time());
        return $resourceId
            ? 'audit_resource_' . $resourceId . '_' . $date
            : 'audit_' . $date;
    }

    /**
     * @param $file
     * @param string[] $customColumns
     */
    private function addColumnHeaders($file, array $customColumns) {
        $header = "Data,Operacja,Użytkownik";
        foreach ($customColumns as $columnName) {
            $header = sprintf("%s,\"%s\"", $header, $columnName);
        }
        $header = $header . "\n";
        fwrite($file, $header);
    }

    /**
     * @param $file
     * @param AuditEntry[] $entries
     * @param string[] $customColumns
     */
    private function saveToFile($file, array $entries, array $customColumns) {
        $data = "";
        foreach ($entries as $entry) {
            $data = sprintf(
                "%s%s,%s,%s",
                $data,
                $entry->getCreatedAt()->format(DATE_ATOM),
                $entry->getCommandName(),
                $entry->getUser() ? $entry->getUser()->getUsername() : ''
            );
            $customColumnsValues = $this->auditEntryCustomColumnsEvaluator->evaluateCustomColumns($entry, $customColumns);
            foreach ($customColumns as $columnName) {
                $data = sprintf(
                    "%s,\"%s\"",
                    $data,
                    isset($customColumnsValues[$columnName]) ? str_replace('"', '""', $customColumnsValues[$columnName]) : ""
                );
            }
            $data = $data . "\n";
        }
        fwrite($file, $data);
    }

    private function closeFile($file) {
        fclose($file);
    }
}
