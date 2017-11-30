<?php
namespace Repeka\Application\Service;

use LightnCandy\LightnCandy;
use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

class LightnCandyHandlebarsResourceDisplayStrategyEvaluator implements ResourceDisplayStrategyEvaluator {
    public function validateTemplate(string $template): void {
        try {
            LightnCandy::compile($template, [
                'flags' => LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_ERROR_EXCEPTION,
            ]);
        } catch (\Exception $e) {
            throw new InvalidResourceDisplayStrategyException($e->getMessage(), $e);
        }
    }
}
