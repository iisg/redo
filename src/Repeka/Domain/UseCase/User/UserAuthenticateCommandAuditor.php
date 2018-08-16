<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class UserAuthenticateCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param UserAuthenticateCommand $command
     * @return array
     */
    public function afterHandling(Command $command, $result, ?array $beforeHandlingResult): array {
        return ['username' => $command->getUsername(), 'address_ip' => $command->getAddressIp()];
    }

    /**
     * @param UserAuthenticateCommand $command
     * @return array
     */
    public function afterError(Command $command, \Exception $exception, ?array $beforeHandlingResult): array {
        return ['username' => $command->getUsername(), 'address_ip' => $command->getAddressIp()];
    }
}
