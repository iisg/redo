<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class AuditedCommandNamesQuery extends AbstractCommand implements NonValidatedCommand {

    private $onlyResource = true;

    public function __construct(bool $onlyResource) {
        $this->onlyResource = $onlyResource;
    }

    public function getOnlyResource(): bool {
        return $this->onlyResource;
    }
}
