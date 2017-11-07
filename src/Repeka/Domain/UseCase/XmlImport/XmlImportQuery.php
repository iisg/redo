<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\Cqrs\Command;

class XmlImportQuery extends Command {
    /** @var string */
    private $id;

    public function __construct(string $id) {
        $this->id = $id;
    }

    public function getId(): string {
        return $this->id;
    }
}
