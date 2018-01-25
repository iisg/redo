<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Factory\ResourceContentsNormalizer;

class ResourceUpdateContentsCommandAdjuster implements CommandAdjuster {
    /** @var ResourceContentsNormalizer */
    private $resourceContentNormalizer;

    public function __construct(ResourceContentsNormalizer $resourceContentNormalizer) {
        $this->resourceContentNormalizer = $resourceContentNormalizer;
    }

    /**
     * @param ResourceUpdateContentsCommand $command
     * @return ResourceUpdateContentsCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceUpdateContentsCommand(
            $command->getResource(),
            $this->resourceContentNormalizer->normalize($command->getContents())
        );
    }
}
