<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;

class LanguageListQuery extends AbstractCommand implements NonValidatedCommand {
    use RequireOperatorRole;
}
