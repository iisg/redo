<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SystemLanguagesInitializer implements ApplicationInitializer {
    public function initialize(OutputInterface $output, ContainerInterface $container) {
        $this->ensureAtLeastOneLanguageExists($output, $container);
    }

    private function ensureAtLeastOneLanguageExists(OutputInterface $output, ContainerInterface $container) {
        /** @var EntityRepository $languageRepository */
        $languageRepository = $container->get('doctrine')->getRepository(Language::class);
        if ($languageRepository->count([]) == 0) {
            $container->get('repeka.command_bus')->handle(new LanguageCreateCommand('PL', 'PL', 'polski'));
            $container->get('repeka.command_bus')->handle(new LanguageCreateCommand('EN', 'GB', 'english'));
            $output->writeln('Automatically added languages: PL and EN.');
        }
    }
}
