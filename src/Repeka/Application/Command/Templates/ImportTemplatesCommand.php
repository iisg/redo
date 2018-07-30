<?php
namespace Repeka\Application\Command\Templates;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTemplatesCommand extends AbstractTemplateCommand {
    protected function configure() {
        $this
            ->setName('repeka:templates:import')
            ->addArgument('namespace', InputArgument::REQUIRED)
            ->setDescription('Import templates from files into the database.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->ensureTemplatesAreConfigured();
        $namespace = $input->getArgument('namespace');
        $templateMetadataKind = $this->loader->getTemplateMetadata();
        $discoverFilesTemplates = $this->discoverFilesTemplates($namespace);
        $progress = new ProgressBar($output, count($discoverFilesTemplates));
        $progress->display();
        foreach ($discoverFilesTemplates as $templateName) {
            $existingResourceKind = $this->loader->getTemplateResourceKind($templateName);
            $templateContents = $this->getTemplateFromFile($templateName);
            $templateMetadata = $templateMetadataKind->withOverrides(['constraints' => ['displayStrategy' => $templateContents]]);
            if ($existingResourceKind) {
                $metadataList = array_map(
                    function (Metadata $metadata) use ($templateMetadata) {
                        if ($metadata->getId() == $templateMetadata->getId()) {
                            return $templateMetadata;
                        } else {
                            return $metadata;
                        }
                    },
                    $existingResourceKind->getMetadataList()
                );
                $command = new ResourceKindUpdateCommand(
                    $existingResourceKind,
                    $existingResourceKind->getLabel(),
                    $metadataList,
                    $existingResourceKind->getWorkflow()
                );
            } else {
                $command = new ResourceKindCreateCommand($this->createLabelInEveryLanguage($templateName), [$templateMetadata]);
            }
            $this->handleCommandBypassingFirewall($command);
            $progress->advance();
        }
        $progress->clear();
        $this->getApplication()->run(new StringInput('repeka:templates:inspect ' . $namespace), $output);
    }
}
