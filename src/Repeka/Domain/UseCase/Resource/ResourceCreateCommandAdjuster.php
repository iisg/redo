<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;

class ResourceCreateCommandAdjuster implements CommandAdjuster {
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(ResourceContentsAdjuster $resourceContentsAdjuster) {
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    /**
     * @param ResourceCreateCommand $command
     * @return ResourceCreateCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceCreateCommand(
            $command->getKind(),
            $this->resourceContentsAdjuster->adjust($command->getContents()),
            $command->getExecutor()
        );
    }
}
