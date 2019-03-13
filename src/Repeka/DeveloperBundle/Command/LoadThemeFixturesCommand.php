<?php
namespace Repeka\DeveloperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class LoadThemeFixturesCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('repeka:dev:load-fixtures')
            ->setDescription('Loads appropriate fixtures.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $theme = $this->getContainer()->getParameter('repeka.theme');
        $this->getApplication()->run(new StringInput("doctrine:fixtures:load --group=$theme -e dev --no-interaction --append"), $output);
    }
}
