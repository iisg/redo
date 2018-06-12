<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;

class XmlExtractQuery extends AbstractCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    private $xml;
    private $mappings;

    public function __construct(string $xml, array $mappings) {
        $this->xml = $xml;
        $this->mappings = $mappings;
    }

    public function getXml() {
        return $this->xml;
    }

    public function getMappings(): array {
        return $this->mappings;
    }
}
