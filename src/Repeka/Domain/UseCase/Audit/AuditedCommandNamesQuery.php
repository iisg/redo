<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class AuditedCommandNamesQuery extends AbstractCommand implements NonValidatedCommand {
}
