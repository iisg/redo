<?php
namespace Repeka\Application\Command\Templates;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class ClearTemplatesCommand extends AbstractTemplateCommand {
    protected function configure() {
        $this
            ->setName('repeka:templates:clear')
            ->addArgument('namespace', InputArgument::REQUIRED)
            ->setDescription('Removes previously imported templates from database.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $namespace = $input->getArgument('namespace');
        $discoverFilesTemplates = $this->discoverFilesTemplates($namespace);
        $progress = new ProgressBar($output, count($discoverFilesTemplates));
        $progress->display();
        foreach ($discoverFilesTemplates as $templateName) {
            $existingResourceKind = $this->loader->getTemplateResourceKind($templateName);
            if ($existingResourceKind) {
                $this->handleCommandBypassingFirewall(new ResourceKindDeleteCommand($existingResourceKind));
            }
            $progress->advance();
        }
        $progress->clear();
        $this->getApplication()->run(new StringInput('repeka:templates:inspect ' . $namespace), $output);
    }
}
