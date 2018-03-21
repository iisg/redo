<?php
namespace Repeka\Domain\UseCase\User;

class UserAuthenticateCommandHandler {
    public function handle(UserAuthenticateCommand $command) {
        if (!$command->isSuccessful()) {
            throw new \DomainException('Unsuccessful authentication.');
        }
    }
}
