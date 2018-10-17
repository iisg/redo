<?php
namespace Repeka\Domain\EventListener;

use Repeka\Application\Elasticsearch\Model\ElasticSearch;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Symfony\Component\Workflow\Transition;

class ResourceUpdateFtsIndexListener extends CommandEventsListener {

    /** @var ElasticSearch */
    private $elasticSearch;

    public function __construct(ElasticSearch $elasticSearch) {
        $this->elasticSearch = $elasticSearch;
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $command = $event->getCommand();
        if ($this->isProductiveCommand($command)) {
            /** @var  $ResourceEntity */
            $resource = $event->getResult();
            $this->elasticSearch->insertDocument($resource);
        } elseif ($command instanceof ResourceDeleteCommand) {
            $resourceId = $event->getResult();
            $this->elasticSearch->deleteDocument($resourceId);
        }
    }

    private function isProductiveCommand($command): bool {
        return ($command instanceof ResourceTransitionCommand && $command->getTransition()->getLabel() !== [SystemTransition::DELETE])
            || $command instanceof ResourceEvaluateDisplayStrategiesCommand
            || $command instanceof ResourceGodUpdateCommand;
    }

    protected function subscribedFor(): array {
        return [
            ResourceTransitionCommand::class,
            ResourceDeleteCommand::class,
            ResourceEvaluateDisplayStrategiesCommand::class,
            ResourceGodUpdateCommand::class,
        ];
    }
}
