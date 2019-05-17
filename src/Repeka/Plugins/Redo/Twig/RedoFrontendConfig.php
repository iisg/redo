<?php
namespace Repeka\Plugins\Redo\Twig;

use Repeka\Application\Twig\FrontendConfigProvider;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RedoFrontendConfig implements FrontendConfigProvider {
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getConfig(): array {
        return [
            'captcha_key' => $this->container->getParameter('redo.captcha_public_key'),
        ];
    }
}
