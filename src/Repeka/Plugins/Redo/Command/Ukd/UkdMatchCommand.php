<?php
namespace Repeka\Plugins\Redo\Command\Ukd;

use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Type;
use Exception;
use Generator;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ESMapping;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class UkdMatchCommand extends ContainerAwareCommand {
    use CommandBusAware;
    /** @var \Elastica\Index */
    private $index;
    /** @var ESClient */
    private $client;
    /** @var ESMapping */
    private $esMapping;
    /** @var string */
    private $indexName = 'wikipedia_titles';
    /** @var Type */
    private $type;

    private const ES_DOCUMENT_TYPE = 'title';

    public function __construct(ESClient $client, ESMapping $esMapping) {
        parent::__construct();
        $this->client = $client;
        $this->index = $this->client->getIndex($this->indexName);
        $this->type = $this->index->getType(self::ES_DOCUMENT_TYPE);
        $this->esMapping = $esMapping;
    }

    protected function configure() {
        $this
            ->setName('redo:ukd:match')
            ->addOption(
                'titles',
                't',
                InputOption::VALUE_REQUIRED,
                'File with wikipedia article titles (from eg. http://dumps.wikimedia.org/plwiki/latest/plwiki-latest-all-titles-in-ns0.gz)'
            )
            ->addOption('ukds', 'u', InputOption::VALUE_REQUIRED, 'File with UKD tags (each tag ends with dot')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file for generated JSON')
            ->setDescription('Match UKD tags with Wikipedia article titles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $titlesFilename = $input->getOption('titles');
            $ukdsFilename = $input->getOption('ukds');
            $outputFilename = $input->getOption('output');
            $this->createIndex();
            $this->indexTitles($titlesFilename, $output);
            $file = new SplFileObject($outputFilename, 'w');
            [$titleByUkd, $notFoundUkds] = $this->matchUkdsToWikipedia($this->readUkds($ukdsFilename), $output);
            $file->fwrite(json_encode(['ukd_tags' => $titleByUkd, 'not_found' => $notFoundUkds], JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            $output->writeln('Cleaning up - deleting index');
            $this->deleteIndex();
        }
    }

    private function indexTitles(string $titlesFile, OutputInterface $output) {
        $output->writeln("Indexing titles in ElasticSearch (processed titles)");
        $progressBar = new ProgressBar($output);
        $progressBar->display();
        foreach ($this->readTitlesInBatches($titlesFile, 1000) as $titles) {
            $this->insertTitles($titles);
            $progressBar->advance(1000);
        }
        $progressBar->finish();
    }

    private function readTitlesInBatches(string $filepath, int $batchSize): Generator {
        $file = new SplFileObject($filepath);
        $file->setFlags(SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        $titles = [];
        while (!$file->eof()) {
            $title = $file->fgets();
            $title = str_replace('_', ' ', $title);
            $titles[] = $title;
            if (count($titles) == $batchSize || $file->eof()) {
                yield $titles;
                $titles = [];
            }
        }
    }

    private function matchUkdsToWikipedia(Generator $ukdsGenerator, OutputInterface $output) {
        $found = [];
        $notFound = [];
        $output->writeln("Matching titles to article titles");
        $progressBar = new ProgressBar($output);
        foreach ($ukdsGenerator as $ukd) {
            $results = $this->search($ukd)->getResults();
            if (!empty($results)) {
                $found[$ukd] = $this->getLinkByTitle($results[0]->getSource()['val']);
            } else {
                $notFound[] = $ukd;
            }
            $progressBar->advance();
        }
        return [$found, $notFound];
    }

    private function getLinkByTitle(string $title) {
        return "https://pl.wikipedia.org/wiki/$title";
    }

    private function readUkds(string $filepath): Generator {
        $file = new SplFileObject($filepath);
        $file->setFlags(SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        while (!$file->eof()) {
            $line = $file->fgets();
            $ukds = array_map('trim', explode('.', $line));
            foreach ($ukds as $ukd) {
                if (!empty($ukd)) {
                    yield $ukd;
                }
            }
        }
    }

    public function insertTitles(iterable $titles) {
        $documents = [];
        $index = 0;
        foreach ($titles as $title) {
            $documents[] = $this->createDocument($title);
            $index++;
        }
        $this->type->addDocuments($documents);
        $this->type->getIndex()->refresh();
    }

    private function search($phrase): ResultSet {
        $simpleQuery = new Query(
            [
                'query' => [
                    'match' => ['val' => $phrase],
                ],
                "from" => 0,
                "size" => 1,
            ]
        );
        $search = new Search($this->client);
        $search->addIndex($this->index->getName());
        $search->addType(self::ES_DOCUMENT_TYPE);
        $search->setQuery($simpleQuery);
        return $search->search();
    }

    private function createDocument(string $title): Document {
        return new Document('', ['val' => $title,]);
    }

    private function createIndex() {
        if ($this->index->exists()) {
            throw new Exception("Index '{$this->indexName}' already exists");
        }
        $this->index->create(
            [
                'analysis' => [
                    'analyzer' => [
                        'default' => [
                            'type' => 'custom',
                            "tokenizer" => "standard",
                            "filter" => ["lowercase", "morfologik_stem"],
                        ],
                    ],
                ],
            ]
        );
        $type = $this->index->getType(self::ES_DOCUMENT_TYPE);
        $this->esMapping->getMapping($type)->send();
    }

    private function deleteIndex() {
        if (!$this->index->exists()) {
            throw new Exception("Index '{$this->indexName}' doesn't exist");
        }
        $this->index->delete();
    }
}
