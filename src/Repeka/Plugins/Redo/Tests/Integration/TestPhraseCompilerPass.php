<?php
namespace Repeka\Plugins\Redo\Tests\Integration;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Repeka\Plugins\Redo\Tests\Integration\TestPhraseTranslator;
use Repeka\Plugins\Redo\EventListener\PhraseTranslator;

class TestPhraseCompilerPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        $container->register(TestPhraseTranslator::class, TestPhraseTranslator::class);
        $container->setAlias(PhraseTranslator::class, TestPhraseTranslator::class);
    }
}
