<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemLanguagesCommand extends TransactionalCommand {
    use CommandBusAware;

    /** @var LanguageRepository|EntityRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        parent::__construct();
        $this->languageRepository = $languageRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:system-languages')
            ->setDescription('Inserts default languages.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        if ($this->languageRepository->count([]) == 0) {
            $this->handleCommand(new LanguageCreateCommand('PL', 'PL', 'polski'));
            $this->handleCommand(new LanguageCreateCommand('EN', 'GB', 'english'));
            $output->writeln('Automatically added languages: PL and EN.');
        } else {
            $output->writeln('Did not add any system languages as they already exists.');
        }
    }
}
