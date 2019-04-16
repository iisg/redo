<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
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
        ResourceDisplayStrategyUsedMetadataCollector $usedMetadataCollector = null,
        array $additionalContext = []
    ): string {
        try {
            if (!trim($template)) {
                $template = 'ID {{ r.id }}';
            }
            $template = $this->compile($template);
            return $template->render(
                array_merge(
                    [
                        'r' => $resourceEntity,
                        'resource' => $resourceEntity,
                        TwigResourceDisplayStrategyEvaluatorExtension::USED_METADATA_COLLECTOR_KEY => $usedMetadataCollector,
                    ],
                    $additionalContext
                )
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

    /** @return MetadataValue[] */
    public function renderToMetadataValues(
        $resourceEntity,
        string $template,
        ResourceDisplayStrategyUsedMetadataCollector $usedMetadataCollector = null,
        array $additionalContext = []
    ): array {
        $values = trim($this->render($resourceEntity, $template, $usedMetadataCollector, $additionalContext));
        if ($values && ($values{0} == '{' || $values{0} == '[')) {
            $values = htmlspecialchars_decode($values);
            $values = preg_replace('#,\s*([\]}])#', '$1', $values); // allow extra commas in JSON
            $json = json_decode($values, true);
            if (is_array($json)) {
                if ($values{0} == '{') {
                    if (!array_key_exists('value', $json)) {
                        $json = ['value' => $json];
                    }
                    $json = [$json];
                }
                $contents = ResourceContents::fromArray([1 => $json]);
                $values = $contents->getValues(1);
            }
        }
        if (!is_array($values)) {
            $values = [new MetadataValue($values)];
        }
        return ResourceContents::fromArray([1 => $values])->filterOutEmptyMetadata()->getValues(1);
    }
}
