<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Elasticsearch\Search\ESSearch;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FtsSearchCommand extends ContainerAwareCommand {

    /** @var ESSearch */
    private $esSearch;

    public function __construct(ESSearch $esSearch) {
        parent::__construct();
        $this->esSearch = $esSearch;
    }

    protected function configure() {
        $this
            ->setName('repeka:fts:search')
            ->setDescription('Search for matching documents.')
            ->addOption('metadata', 'm', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Searched metadata name')
            ->addOption('value', 'w', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Value to match metadata name.')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'limit the maximum number of return values')
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Set offset from where to retrieve values')
            ->setDescription('Search for resources');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $arguments = $input->getOption('metadata');
        $values = $input->getOption('value');
        $size = $input->getOption('limit');
        $offset = $input->getOption('offset');
        $this->setPairs($arguments, $values);
        $this->setSize($size);
        $this->setOffset($offset);
        $this->esSearch->createQuery();
        $results = $this->esSearch->search();
        $output->writeln($results->count() . " results");
        foreach ($results as $result) {
            $output->writeln("Id: " . $result->getId());
        }
    }

    private function setPairs($arguments, $values) {
        if ($arguments && $values && count($arguments) != count($values)) {
            throw new \InvalidArgumentException('Number of arguments and values must be the same');
        }
        if ($arguments && $values) {
            for ($i = 0; $i < count($arguments); $i++) {
                $this->esSearch->addPair($arguments[$i], $values[$i]);
            }
        }
    }

    private function setSize($size) {
        if ($size) {
            $this->esSearch->setSize($size);
        }
    }

    private function setOffset($offset) {
        if ($offset) {
            $this->esSearch->setOffset($offset);
        }
    }
}
