<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Factory\ResourceContentsNormalizer;

class ResourceCreateCommandAdjuster implements CommandAdjuster {
    /** @var ResourceContentsNormalizer */
    private $resourceContentNormalizer;

    public function __construct(ResourceContentsNormalizer $resourceContentNormalizer) {
        $this->resourceContentNormalizer = $resourceContentNormalizer;
    }

    /**
     * @param ResourceCreateCommand $command
     * @return ResourceCreateCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceCreateCommand(
            $command->getKind(),
            $this->resourceContentNormalizer->normalize($command->getContents())
        );
    }
}
