<?php
namespace Repeka\DeveloperBundle\Command;

use Repeka\Application\Command\DirectoryContentsLister;
use Repeka\Application\Upload\ResourceFilePathGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearUploadsCommand extends Command {
    /** @var ResourceFilePathGenerator */
    private $resourceFilePathGenerator;
    /** @var DirectoryContentsLister */
    private $directoryContentsLister;

    public function __construct(
        ResourceFilePathGenerator $resourceFilePathGenerator,
        DirectoryContentsLister $directoryContentsLister
    ) {
        parent::__construct();
        $this->resourceFilePathGenerator = $resourceFilePathGenerator;
        $this->directoryContentsLister = $directoryContentsLister;
    }

    protected function configure() {
        $this
            ->setName('repeka:dev:clear-uploads')
            ->setDescription('Removes everything in the uploads path');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $uploadsPath = $this->resourceFilePathGenerator->getUploadsRootPath();
        /** @var \SplFileInfo[] $itemsToDelete */
        $itemsToDelete = $this->directoryContentsLister->listRecursively($uploadsPath);
        $itemsToDelete = array_filter(
            $itemsToDelete,
            function (\SplFileInfo $file) use ($output) {
                return $file->getFilename() != '.gitignore';
            }
        );
        foreach ($itemsToDelete as $item) {
            $itemPath = $item->getPathname();
            if ($item->isDir()) {
                rmdir($itemPath);
            } else {
                unlink($itemPath);
            }
        }
        $output->writeln("<info>Nuked everything in $uploadsPath.</info>");
    }
}
