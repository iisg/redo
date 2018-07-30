<?php
namespace Repeka\Application\Command\Templates;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InspectTemplatesCommand extends AbstractTemplateCommand {

    protected function configure() {
        $this
            ->setName('repeka:templates:inspect')
            ->addArgument('namespace', InputArgument::REQUIRED)
            ->setDescription('Compares file and database templates and prints a summary.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $this->ensureTemplatesAreConfigured();
            $configured = true;
        } catch (\Exception $e) {
            $configured = false;
            $errorMessage = $e->getMessage();
        }
        $table = new Table($output);
        $table->setHeaders($configured ? ['Template', 'Stored in database?', 'Is the same?'] : ['Template']);
        $templates = $this->discoverFilesTemplates($input->getArgument('namespace'));
        $rows = [];
        foreach ($templates as $templateName) {
            $row = [$templateName];
            if ($configured) {
                $templateRk = $this->loader->getTemplateResourceKind($templateName);
                $equal = '-';
                if ($templateRk) {
                    $storedTemplate = $this->loader->getTemplateContent($templateRk);
                    $fileTemplate = $this->getTemplateFromFile($templateName);
                    $equal = $storedTemplate == $fileTemplate ? '<info>YES</info>' : '<error>NO</error>';
                }
                $row[] = $templateRk ? 'YES' : 'NO';
                $row[] = $equal;
            }
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->render();
        if (!$configured) {
            $output->writeln('<error>Templates importing is not configured.</error>');
            $output->writeln("<error>$errorMessage</error>");
        }
    }
}
