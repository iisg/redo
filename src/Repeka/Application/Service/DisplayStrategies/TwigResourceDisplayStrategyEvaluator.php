<?php
namespace Repeka\Application\Service\DisplayStrategies;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Twig\Loader\ArrayLoader;
use Twig_Template;

class TwigResourceDisplayStrategyEvaluator implements ResourceDisplayStrategyEvaluator {
    /** @var \Twig_Environment */
    private $twig;

    public function __construct(TwigResourceDisplayStrategyEvaluatorExtension $extension) {
        $this->twig = new \Twig_Environment(new ArrayLoader([]));
        $this->twig->addExtension($extension);
    }

    public function validateTemplate(string $template): void {
        try {
            $this->compile($template);
        } catch (\Throwable $e) {
            throw new InvalidResourceDisplayStrategyException($this->throwableToHumanMessage($e), $e);
        }
    }

    private function compile(string $template): Twig_Template {
        return $this->twig->createTemplate($template);
    }

    public function render($resourceEntity, string $template): string {
        try {
            if (!trim($template)) {
                $template = 'ID {{ r.id }}';
            }
            $template = $this->compile($template);
            return $template->render(['r' => $resourceEntity, 'resource' => $resourceEntity]);
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
