<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;

interface ResourceDisplayStrategyEvaluator {
    /**
     * @throws InvalidResourceDisplayStrategyException when the template is not valid
     */
    public function validateTemplate(string $template): void;
}
