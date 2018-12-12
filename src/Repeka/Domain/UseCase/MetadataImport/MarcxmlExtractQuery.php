<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;

class MarcxmlExtractQuery extends AbstractCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    private $xml;

    public function __construct(string $xml) {
        $this->xml = $xml;
    }

    public function getXml() {
        return $this->xml;
    }
}
