<?php
namespace Repeka\Application\Command\Initialization;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
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
            new SystemUserResourceInitializer($this->getContainer()->get('doctrine.orm.entity_manager')),
            new SystemUserRolesInitializer($this->getContainer()->get('doctrine.orm.entity_manager')),
        ];
        foreach ($initializers as $initializer) {
            $initializer->preEntityInitializer();
            $this->getContainer()->get('doctrine')->getManager()->transactional(function () use ($initializer, $output) {
                $initializer->initialize($output, $this->getContainer());
            });
            $initializer->postEntityInitializer();
        }
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('repeka:statsd:deploy'), $output);
    }
}
