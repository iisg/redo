<?php
namespace Repeka\Application\Command\PkImport;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PkImportDiscoverMetadataCommand extends Command {
    protected function configure() {
        $this
            ->setName('repeka:pk-import:discover')
            ->addArgument('input', InputArgument::REQUIRED)
            ->setDescription('Displays metadata that needed to be defined in the import config to import resources from given file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $metadata = $this->collectMetadata(PkImportFileLoader::load($input->getArgument('input')));
            $this->displayTable($metadata, $output);
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    private function collectMetadata(\SimpleXMLElement $xml) {
        $resources = $xml->xpath('/*/*');
        $metadataData = [];
        foreach ($resources as $resource) {
            $metadataList = $resource->metadata;
            foreach ($metadataList as $metadata) {
                $termId = (string)$metadata['TERM_ID'];
                if (!isset($metadataData[$termId])) {
                    $metadataData[$termId] = [
                        'id' => $termId,
                        'quantity' => 0,
                    ];
                }
                ++$metadataData[$termId]['quantity'];
            }
        }
        return $metadataData;
    }

    private function displayTable(array $metadata, OutputInterface $output) {
        $table = new Table($output);
        $table->setHeaders(['Term ID (import key)', 'Quantity'])->setRows($metadata);
        $table->render();
    }
}
