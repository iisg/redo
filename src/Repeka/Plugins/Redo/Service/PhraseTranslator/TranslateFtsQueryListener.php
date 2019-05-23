<?php
namespace Repeka\Plugins\Redo\Service\PhraseTranslator;

use Repeka\Application\Elasticsearch\Model\ElasticSearchTextQueryCreator;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;

class TranslateFtsQueryListener extends CommandEventsListener {
    /** @var array */
    private $ftsConfig;
    /** @var RedoFtsSearchPhraseTranslator */
    private $redoFtsSearchPhraseTranslator;

    public function __construct(array $ftsConfig, RedoFtsSearchPhraseTranslator $redoFtsSearchPhraseTranslator) {
        $this->ftsConfig = $ftsConfig;
        $this->redoFtsSearchPhraseTranslator = $redoFtsSearchPhraseTranslator;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        if (!$this->isTranslationEnabled()) {
            return;
        }
        /** @var ResourceListFtsQuery $command */
        $command = $event->getCommand();
        $originalPhrase = $command->getPhrases()[0] ?? '';
        if ($originalPhrase) {
            $translatedPhrases = $this->redoFtsSearchPhraseTranslator->translatePhrase($originalPhrase);
            $phrases = [$originalPhrase, ElasticSearchTextQueryCreator::RAW_PHRASES => array_values(array_unique($translatedPhrases))];
            $event->replaceCommand(
                new ResourceListFtsQuery(
                    $phrases,
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
    }

    private function isTranslationEnabled(): bool {
        return $this->ftsConfig['phrase_translation'] ?? false;
    }

    protected function subscribedFor(): array {
        return [ResourceListFtsQuery::class];
    }
}
