<?php
namespace Repeka\Application\Command\Elasticsearch;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use Elastica\Search;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * example: bin/console repeka:elasticsearch:search zosia
 * TODO remove this when actual indexing is introduced
 */
class SearchCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:elasticsearch:search')
            ->setDescription('Search for matching documents.')
            ->addOption('language', null, InputOption::VALUE_OPTIONAL, 'Required language.')
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, 'Title to be matched.')
            ->addOption('author', null, InputOption::VALUE_OPTIONAL, 'Required author.')
            ->addArgument('content', InputArgument::OPTIONAL, 'Content to be matched.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $language = trim($input->getOption('language'));
        $title = trim($input->getOption('title'));
        $author = trim($input->getOption('author'));
        $content = trim($input->getArgument('content'));
        $qb = new QueryBuilder(new QueryBuilder\Version\Version240());
        /** @var BoolQuery $metadataQuery */
        $metadataQuery = $this->buildMetadataQuery($qb, $language, $title, $author, $content);
        $query = $qb->query()->nested()->setPath('metadata')->setQuery($metadataQuery);
        $client = $this->getContainer()->get('elasticsearch.client');
        $results = (new Search($client))
            ->addIndex($this->getContainer()->getParameter('elasticsearch.index_name'))
            ->addType(ResourceConstants::ES_DOCUMENT_TYPE)
            ->search($query);
        echo sprintf("%d results\n", $results->count());
        foreach ($results as $result) {
            $this->printResult($result->getData());
        }
    }

    private function buildMetadataQuery(QueryBuilder $qb, $language, $title, $author, $content) {
        $metadataQuery = $qb->query()->bool();
        if ($language) {
            $metadataQuery = $metadataQuery
                ->addMust($qb->query()->term(['metadata.' . ResourceConstants::VALUE_TYPE => 'content']))
                ->addMust($qb->query()->nested()->setPath('metadata.metadata')->setQuery(
                    $qb->query()->bool()
                        ->addMust($qb->query()->term(['metadata.' . ResourceConstants::VALUE_TYPE => 'language']))
                        ->addMust($qb->query()->term(['metadata.' . ResourceConstants::RAW_STRING => $language]))
                ));
        }
        if ($title) {
            $metadataQuery = $metadataQuery
                ->addMust($qb->query()->term(['metadata.' . ResourceConstants::VALUE_TYPE => 'title']))
                ->addMust($qb->query()->match('metadata.' . ResourceConstants::TOKENIZED_STRING, $title));
        }
        if ($author) {
            $metadataQuery = $metadataQuery
                ->addMust($qb->query()->term(['metadata.' . ResourceConstants::VALUE_TYPE => 'author']))
                ->addMust($qb->query()->term(['metadata.' . ResourceConstants::TOKENIZED_STRING => $author]));
        }
        if ($content) {
            $supportedLanguages = $this->getContainer()->get('repository.language')->getAvailableLanguageCodes();
            $multiLanguageContentQuery = $qb->query()->bool();
            foreach ($supportedLanguages as $language) {
                $language = strtolower($language);
                $languageSpecificQuery = $this->getContentMatchQuery($content, $language, $qb);
                $multiLanguageContentQuery = $multiLanguageContentQuery->addShould($languageSpecificQuery);
            }
            $metadataQuery = $metadataQuery->addMust($multiLanguageContentQuery);
            return $metadataQuery;
        }
        return $metadataQuery;
    }

    private function getContentMatchQuery(string $content, string $language, QueryBuilder $qb) {
        $fieldName = 'metadata.' . ResourceConstants::longLanguageString($language);
        return $qb->query()->bool()
            ->addMust($qb->query()->match($fieldName, $content))
            ->addShould($qb->query()->match()
                ->setFieldType($fieldName, 'phrase')
                ->setField($fieldName, $content));
    }

    private function printResult($result) {
        echo "Result:\n";
        foreach ($result['metadata'] as $metadata) {
            $valueFields = [];
            foreach ($metadata as $key => $value) {
                if (substr($key, 0, 6) === 'value_') {
                    $valueFields[] = $key;
                }
            }
            if (count($valueFields) > 1) {
                // it's an analyzed string - it has both a value_long_string_* and value_integer
                $valueFields = array_diff($valueFields, [ResourceConstants::INTEGER]);
            } elseif (count($valueFields) === 0) {
                continue;
            }
            $value = $metadata[$valueFields[0]];
            $value = preg_replace('/\s+/', ' ', $value);
            $value = substr($value, 0, 120);
            $key = str_pad($metadata[ResourceConstants::VALUE_TYPE], 8, ' ');
            echo "  $key  =>  $value\n";
        }
    }
}
