<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;

interface ResourceDisplayStrategyEvaluator {
    /**
     * @param ResourceEntity|ResourceContents $resourceEntity
     * @param string $template
     * @return string
     */
    public function render($resourceEntity, string $template): string;

    /**
     * @throws InvalidResourceDisplayStrategyException when the template is not valid
     */
    public function validateTemplate(string $template): void;
}
