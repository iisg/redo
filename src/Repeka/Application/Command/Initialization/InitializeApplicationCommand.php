<?php
namespace Repeka\Application\Command\Initialization;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeApplicationCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:initialize')
            ->setDescription('Initializes data in database required for the app to work properly.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var ApplicationInitializer[] $initializers */
        $initializers = [
            new SystemLanguagesInitializer(),
            new SystemUserRolesInitializer(),
        ];
        foreach ($initializers as $initializer) {
            $this->getContainer()->get('doctrine')->getManager()->transactional(function () use ($initializer, $output) {
                $initializer->initialize($output, $this->getContainer());
            });
        }
    }
}
