<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\User;

class UserChangePasswordCommand extends ResourceClassAwareCommand implements NonValidatedCommand {
    /** @var User */
    private $user;
    /** @var null|string */
    private $plainPassword;

    public function __construct(User $user, string $plainPassword) {
        parent::__construct(SystemResourceClass::USER);
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getPlainPassword(): ?string {
        return $this->plainPassword;
    }
}
