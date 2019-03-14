<?php
namespace Repeka\Plugins\PdfGenerator;

use Knp\Bundle\SnappyBundle\KnpSnappyBundle;
use Repeka\Application\DependencyInjection\WithBundleDependencies;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaPdfGeneratorPluginBundle extends Bundle implements WithBundleDependencies {
    public function getDependentBundles(): array {
        return [new KnpSnappyBundle()];
    }
}
