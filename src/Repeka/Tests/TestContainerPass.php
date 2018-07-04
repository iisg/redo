<?php
namespace Repeka\Tests;

use Assert\Assertion;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Makes enumerated private services that needs to be tested public so they can be fetched from the container without a deprecation warning.
 *
 * @see https://github.com/symfony/symfony-docs/issues/8097
 * @see https://github.com/symfony/symfony/issues/24543
 */
class TestContainerPass implements CompilerPassInterface {
    private static $publicInTests = [
        \Doctrine\ORM\EntityManagerInterface::class,
        \Repeka\Domain\Cqrs\CommandBus::class,
        \Repeka\Domain\MetadataImport\Config\ImportConfigFactory::class,
        \Repeka\Domain\Repository\AuditEntryRepository::class,
        \Repeka\Domain\Repository\LanguageRepository::class,
        \Repeka\Domain\Repository\MetadataRepository::class,
        \Repeka\Domain\Repository\ResourceKindRepository::class,
        \Repeka\Domain\Repository\ResourceRepository::class,
        \Repeka\Domain\Repository\ResourceWorkflowRepository::class,
        \Repeka\Domain\Repository\UserRepository::class,
        \Repeka\Domain\Service\ResourceDisplayStrategyEvaluator::class,
        \Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule::class,
        \Repeka\Plugins\MetadataValueSetter\Model\MetadataValueSetterOnResourceTransitionListener::class,
        \Repeka\Plugins\Ocr\Model\OcrOnResourceTransitionListener::class,
        'sensio_framework_extra.view.guesser',
    ];

    public static function addPublicServices(array $services) {
        self::$publicInTests = array_merge(self::$publicInTests, $services);
    }

    public function process(ContainerBuilder $container) {
        $madePublic = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            if (in_array($id, self::$publicInTests, true) || in_array($definition->getClass(), self::$publicInTests, true)) {
                $definition->setPublic(true);
                $madePublic[] = $id;
            }
        }
        foreach ($container->getAliases() as $id => $definition) {
            if (in_array($id, self::$publicInTests, true)) {
                $definition->setPublic(true);
                $madePublic[] = $id;
            }
        }
        Assertion::greaterOrEqualThan(
            count($madePublic),
            count(self::$publicInTests),
            function () use ($madePublic) {
                return 'The following services were not made public although they have been requtested: '
                    . implode(', ', array_diff(self::$publicInTests, $madePublic));
            }
        );
    }
}
