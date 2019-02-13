<?php
namespace Repeka\Plugins\Redo\EventListener;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Plugins\Redo\Service\RedoFtsSearchPhraseTranslator;

class ResourceListFtsQueryListener extends CommandEventsListener {
    /** @var array */
    private $ftsConfig;
    /** @var RedoFtsSearchPhraseTranslator */
    private $redoFtsSearchPhraseTranslator;

    public function __construct(array $ftsConfig, RedoFtsSearchPhraseTranslator $redoFtsSearchPhraseTranslator) {
        $this->ftsConfig = $ftsConfig;
        $this->redoFtsSearchPhraseTranslator = $redoFtsSearchPhraseTranslator;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        if (!$this->isTranslationActivated()) {
            return;
        }
        /** @var ResourceListFtsQuery $command */
        $command = $event->getCommand();
        $phrase = $command->getPhrases()[0];
        $phrases = $this->redoFtsSearchPhraseTranslator->translatePhrase($phrase);
        $phrases[] = $phrase;
        $event->replaceCommand(
            new ResourceListFtsQuery(
                array_values(array_unique($phrases)),
                $command->getSearchableMetadata(),
                $command->getMetadataFilters(),
                $command->getResourceClasses(),
                $command->hasResourceKindFacet(),
                $command->getFacetedMetadata(),
                $command->getFacetsFilters(),
                $command->isOnlyTopLevel(),
                $command->getPage(),
                $command->getResultsPerPage()
            )
        );
    }

    private function isTranslationActivated(): bool {
        return $this->ftsConfig['phrase_translation'] ?? false;
    }

    protected function subscribedFor(): array {
        return [ResourceListFtsQuery::class];
    }
}
