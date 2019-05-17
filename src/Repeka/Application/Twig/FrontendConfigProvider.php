<?php
namespace Repeka\Application\Twig;

interface FrontendConfigProvider {
    public function getConfig(): array;
}
