<?php
namespace Repeka\Application\Command\Resource;

use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ResourceDeleteRecursivelyCommand extends Command {
    use Transactional;

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:resources:delete')
            ->setDescription('Deletes the specified resource by ID and all of its children.')
            ->addArgument('id', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $resource = $this->resourceRepository->findOne($input->getArgument('id'));
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $label = $resource->getValues(SystemMetadata::RESOURCE_LABEL)[0]->getValue();
        $question = "Are you sure you want to remove resource $label (#{$resource->getId()})? [y/N] ";
        $confirmed = $helper->ask($input, $output, new ConfirmationQuestion($question, !$input->isInteractive()));
        if ($confirmed) {
            $this->transactional(
                function () use ($output, $resource) {
                    $this->deleteResourceRecursively($resource, $output);
                }
            );
            $output->writeln(PHP_EOL . 'Ok.');
        } else {
            $output->writeln('Operation cancelled.');
        }
    }

    private function deleteResourceRecursively(ResourceEntity $resource, OutputInterface $output, int &$deleteCount = 0) {
        $childrenQuery = ResourceListQuery::builder()->filterByParentId($resource->getId())->build();
        $children = $this->resourceRepository->findByQuery($childrenQuery);
        foreach ($children as $child) {
            $this->deleteResourceRecursively($child, $output, $deleteCount);
        }
        $this->resourceRepository->delete($resource);
        $output->write("\rDeleted " . (++$deleteCount));
    }
}
