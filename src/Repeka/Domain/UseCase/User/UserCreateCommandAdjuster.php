<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;

class UserCreateCommandAdjuster implements CommandAdjuster {
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(ResourceContentsAdjuster $resourceContentsAdjuster) {
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    /** @param UserCreateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new UserCreateCommand(
            self::normalizeUsername($command->getUsername()),
            $command->getPlainPassword(),
            $command->getUserData() ? $this->resourceContentsAdjuster->adjust($command->getUserData()) : null
        );
    }

    public static function normalizeUsername(string $username) {
        $username = preg_replace('#[^a-z0-9_\./-@\+]#i', '', $username);
        return strtolower($username);
    }
}
