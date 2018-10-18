<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceDisplayStrategyUsedMetadataCollector;
use Twig\Environment;
use Twig\Template;

class TwigResourceDisplayStrategyEvaluator implements ResourceDisplayStrategyEvaluator {
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig) {
        $this->twig = $twig;
    }

    public function validateTemplate(string $template): void {
        try {
            $this->compile($template);
        } catch (\Throwable $e) {
            throw new InvalidResourceDisplayStrategyException($this->throwableToHumanMessage($e), $e);
        }
    }

    private function compile(string $template): Template {
        return $this->twig->createTemplate($template);
    }

    public function render(
        $resourceEntity,
        string $template,
        ResourceDisplayStrategyUsedMetadataCollector $usedMetadataCollector = null
    ): string {
        try {
            if (!trim($template)) {
                $template = 'ID {{ r.id }}';
            }
            $template = $this->compile($template);
            return $template->render(
                [
                    'r' => $resourceEntity,
                    'resource' => $resourceEntity,
                    TwigResourceDisplayStrategyEvaluatorExtension::USED_METADATA_COLLECTOR_KEY => $usedMetadataCollector,
                ]
            );
        } catch (\Throwable $e) {
            return $this->throwableToHumanMessage($e);
        }
    }

    private function throwableToHumanMessage(\Throwable $e) {
        if ($e instanceof \Twig_Error) {
            return $e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getRawMessage();
        } else {
            // one kitten just died :-(
            return $e->getMessage();
        }
    }
}
