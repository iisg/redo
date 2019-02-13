<?php
namespace Repeka\Tests\Integration\FullTextSearch;

use Elastica\Result;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Plugins\Redo\Tests\Integration\TestPhraseTranslator;
use Repeka\Tests\IntegrationTestCase;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class ResourceListFtsQueryListenerTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var ResourceEntity */
    private $phpBookResource;

    /** @var ResourceEntity */
    private $phpAndMySQLBookResource;

    /** @var PhraseTranslator */
    private $translator;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->phpBookResource = $this->findResourceByContents(['Tytul' => 'PHP - to można leczyć!']);
        $this->phpAndMySQLBookResource = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL']);
        $this->translator = new TestPhraseTranslator();
    }

    public function testSearchTranslations() {
        /** @var Result[] $results */
        $translation = $this->translator->translate('PHP', 'pl');
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery(
                $translation->getTranslated(),
                [SystemMetadata::RESOURCE_LABEL]
            )
        );
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpBookResource->getId(), $ids);
        $this->assertContains($this->phpAndMySQLBookResource->getId(), $ids);
    }
}
