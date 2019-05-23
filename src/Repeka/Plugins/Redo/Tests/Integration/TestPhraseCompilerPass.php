<?php
namespace Repeka\Plugins\Redo\Tests\Integration;

use Repeka\Plugins\Redo\Service\PhraseTranslator\PhraseTranslator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestPhraseCompilerPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        $container->register(TestPhraseTranslator::class, TestPhraseTranslator::class);
        $container->setAlias(PhraseTranslator::class, TestPhraseTranslator::class);
    }
}
