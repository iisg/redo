<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class UserQuery extends AbstractCommand implements NonValidatedCommand {
    private $id;

    public function __construct(int $id) {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }
}
