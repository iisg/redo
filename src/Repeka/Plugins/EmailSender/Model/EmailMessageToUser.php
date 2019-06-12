<?php
namespace Repeka\Plugins\EmailSender\Model;

use Repeka\Domain\Entity\User;

class EmailMessageToUser extends EmailMessage {
    /** @var User */
    private $user;

    public function setToUser(User $user, string $emailAddress = null): self {
        $this->user = $user;
        parent::setTo($emailAddress ?: $user->getUsername());
        return $this;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function setTo(string $addresses, array $context = []): EmailMessage {
        throw new \BadMethodCallException('This is message to user only. Use setToUser instead.');
    }
}
