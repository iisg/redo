<?php
namespace Repeka\Application\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFlagListCommand extends ContainerAwareCommand {
    const FLAG_PATH = './src/AdminPanel/jspm_packages/github/behdad/region-flags@1.0.1/svg/';
    const FLAG_JSON_PATH = './web/api/flags.json';

    protected function configure() {
        $this
            ->setName('repeka:generate-flag-list')
            ->setDescription('Generate flag list.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $flags = $this->stripSvgExtensions($this->getAllFilesInDirectory(self::FLAG_PATH));
        file_put_contents(self::FLAG_JSON_PATH, json_encode(['available_flags' => array_values($flags)]));
    }

    private function stripSvgExtensions(array $files): array {
        return array_map(
            function ($file) {
                return basename($file, '.svg');
            },
            $files
        );
    }

    /**
     * @see http://stackoverflow.com/a/15774702
     */
    private function getAllFilesInDirectory(string $path): array {
        return array_diff(scandir($path), ['.', '..']);
    }
}
