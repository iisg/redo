<?php
namespace Repeka\Application\Command\Templates;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class DumpTemplatesCommand extends AbstractTemplateCommand {
    protected function configure() {
        $this
            ->setName('repeka:templates:dump')
            ->addArgument('namespace', InputArgument::REQUIRED)
            ->setDescription('Dumps templates from database into the files.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->ensureTemplatesAreConfigured();
        $namespace = $input->getArgument('namespace');
        $discoverFilesTemplates = $this->discoverFilesTemplates($namespace);
        $progress = new ProgressBar($output, count($discoverFilesTemplates));
        $progress->display();
        foreach ($discoverFilesTemplates as $templateName) {
            $existingResourceKind = $this->loader->getTemplateResourceKind($templateName);
            if ($existingResourceKind) {
                $databaseContents = $this->loader->getTemplateContent($existingResourceKind);
                file_put_contents($this->getTemplatePath($templateName), $databaseContents);
            }
            $progress->advance();
        }
        $progress->clear();
        $this->getApplication()->run(new StringInput('repeka:templates:inspect ' . $namespace), $output);
    }
}
