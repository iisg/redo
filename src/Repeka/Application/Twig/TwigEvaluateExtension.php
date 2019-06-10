<?php
namespace Repeka\Application\Twig;

/**
 * A twig extension that adds an "evaluate" filter for dynamic evaluation.
 * @see https://stackoverflow.com/a/10945264/878514
 */
class TwigEvaluateExtension extends \Twig_Extension {
    public function getFilters() {
        return [
            new \Twig_Filter(
                'evaluate',
                [$this, 'evaluate'],
                [
                    'needs_environment' => true,
                    'needs_context' => true,
                    'is_safe' => [
                        'evaluate' => true,
                    ],
                ]
            ),
        ];
    }

    public function evaluate(\Twig_Environment $environment, $context, $string) {
        try {
            $loader = $environment->getLoader();
            $parsed = $this->parseString($environment, $context, $string);
            $environment->setLoader($loader);
            return $parsed;
        } catch (\Exception $e) {
            return $this->throwableToHumanMessage($e);
        }
    }

    protected function parseString(\Twig_Environment $environment, $context, $string) {
        $template = $environment->createTemplate($string);
        return $template->render($context);
    }

    private function throwableToHumanMessage(\Throwable $e) {
        if ($e instanceof \Twig_Error) {
            return $e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getRawMessage();
        } else {
            return $e->getMessage();
        }
    }
}
