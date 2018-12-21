<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;

class MarcxmlExtractQuery extends AbstractCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    private $xml;
    private $id;

    public function __construct(string $xml, $id) {
        $this->xml = $xml;
        $this->id = $id;
    }

    public function getXml() {
        return $this->xml;
    }

    public function getId() {
        return $this->id;
    }
}
