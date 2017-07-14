<?php

namespace Repeka\Application\Command\Elasticsearch;

use Repeka\Application\Elasticsearch\Model\DateTimeIndexedMetadata;
use Repeka\Application\Elasticsearch\Model\ESResource;
use Repeka\Application\Elasticsearch\Model\IntIndexedMetadata;
use Repeka\Application\Elasticsearch\Model\LongAnalyzedStringIndexedMetadata;
use Repeka\Application\Elasticsearch\Model\RawStringIndexedMetadata;
use Repeka\Application\Elasticsearch\Model\TokenizedStringIndexedMetadata;
use Repeka\Domain\Repository\LanguageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * example: bin/console repeka:elasticsearch:index-file pan-tadeusz.txt pl
 * files: https://confluence.fslab.agh.edu.pl/download/attachments/48891017/txt_it_pl.7z?api=v2
 * TODO remove this when actual indexing is introduced
 */
class IndexFileCommand extends Command {
    /** @var LanguageRepository */
    private $languageRepository;
    /** @var ESResource */
    private $esResource;

    public function __construct(LanguageRepository $languageRepository, ESResource $esResource) {
        parent::__construct();
        $this->languageRepository = $languageRepository;
        $this->esResource = $esResource;
    }

    protected function configure() {
        $this
            ->setName('repeka:elasticsearch:index-file')
            ->setDescription('Adds file contents to Elasticsearch index.')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the file to be indexed.')
            ->addArgument('language', InputArgument::REQUIRED, 'Language in which file contents are written.')
            ->addArgument('title', InputArgument::OPTIONAL, 'Title of the file')
            ->addArgument('author', InputArgument::OPTIONAL, 'Author of the file');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $supportedLanguages = array_map('strtolower', $this->languageRepository->getAvailableLanguageCodes());
        $language = trim($input->getArgument('language'));
        if (!in_array($language, $supportedLanguages)) {
            throw new \Exception(sprintf("Unsupported language '%s'", $language));
        }
        $fileContents = file_get_contents($input->getArgument('filePath'));
        $title = trim($input->getArgument('title'));
        $author = trim($input->getArgument('author'));
        if ($title) {
            $this->esResource->addMetadata(new TokenizedStringIndexedMetadata('title', $title));
        }
        if ($author) {
            $this->esResource->addMetadata(new TokenizedStringIndexedMetadata('author', $author));
        }
        $this->esResource->addMetadata($contentMetadata = new LongAnalyzedStringIndexedMetadata('content', $language, $fileContents, 1));
        $contentMetadata->addMetadata(new RawStringIndexedMetadata('language', $language));
        $contentMetadata->addMetadata(new IntIndexedMetadata('page_count', 1));
        $contentMetadata->addMetadata(new DateTimeIndexedMetadata('created', new \DateTime()));
        $this->esResource->insert();
    }
}
