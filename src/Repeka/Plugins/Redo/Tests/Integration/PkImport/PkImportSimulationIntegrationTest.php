<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class PkImportSimulationIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $languageRk;
    /** @var ResourceKind */
    private $bookRk;

    /** @before */
    public function init(): void {
        $nameMetadata = $this->createSimpleMetadata('nazwa', MetadataControl::TEXT(), 'dictionaries');
        $isoMetadata = $this->createSimpleMetadata('iso_code', MetadataControl::TEXT(), 'dictionaries');
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'dictionaries');
        $this->languageRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(['PL' => 'Język', 'EN' => 'Language'], [$nameMetadata, $isoMetadata, $oldIdMetadata])
        );
        $bookTitleMetadata = $this->createSimpleMetadata('tytul', MetadataControl::TEXT(), 'books');
        $this->createMetadata(
            'language',
            ['PL' => 'Jezyk', 'EN' => 'Jezyk'],
            [],
            [],
            MetadataControl::RELATIONSHIP,
            'books',
            ['resourceKind' => [$this->languageRk->getId()]],
            'basic',
            $bookTitleMetadata
        );
        $seeAlsoMetadata = $this->createMetadata(
            'zobacz tez',
            ['PL' => 'Zobacz też', 'EN' => 'See also'],
            [],
            [],
            MetadataControl::RELATIONSHIP,
            'books',
            ['resourceKind' => [$this->languageRk->getId()]],
            'basic',
            $bookTitleMetadata
        );
        $this->bookRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(['PL' => 'Książka', 'EN' => 'Book'], [$bookTitleMetadata, $seeAlsoMetadata])
        );
        $this->clearImportHistory();
        $this->importFile('index-jezyki', $this->languageRk);
        $this->importFile('index-ksiazki', $this->bookRk);
        $this->mapRelations('-i indexItem:language -i resource:zobaczTez');
    }

    public function testImportingBothTitles() {
        $malarstwo = $this->findResourceByContents(['tytul' => 'Malarstwo XIX wieku. Cz. 1']);
        $this->assertEquals(
            ['Malarstwo XIX wieku. Cz. 1', 'Macfall, Haldane History of painting: t. 8'],
            $malarstwo->getContents()->getValuesWithoutSubmetadata($this->findMetadataByName('tytul'))
        );
    }

    public function testMappedRelationsToIndexes() {
        $malarstwo = $this->findResourceByContents(['tytul' => 'Malarstwo XIX wieku. Cz. 1']);
        $polski = $this->findResourceByContents(['nazwa' => 'polski']);
        $values = $malarstwo->getValues($this->findMetadataByName('tytul'));
        $this->assertEquals('Malarstwo XIX wieku. Cz. 1', $values[0]->getValue());
        $language = $values[0]->getSubmetadata($this->findMetadataByName('language'))[0];
        $this->assertEquals($polski->getId(), $language->getValue());
    }

    public function testMappedParent() {
        $malarstwo = $this->findResourceByContents(['tytul' => 'Malarstwo XIX wieku. Cz. 1']);
        $this->assertTrue($malarstwo->hasParent());
        $parent = $this->handleCommandBypassingFirewall(new ResourceQuery($malarstwo->getParentId()));
        $values = $parent->getValues($this->findMetadataByName('tytul'));
        $this->assertEquals('Malarstwo XIX wieku. Cz. 2', $values[0]->getValue());
    }

    public function testMappedResourceRelationship() {
        $malarstwo = $this->findResourceByContents(['tytul' => 'Malarstwo XIX wieku. Cz. 1']);
        $values = $malarstwo->getValues($this->findMetadataByName('zobaczTez'));
        $this->assertEquals($malarstwo->getParentId(), $values[0]->getValue());
    }
}
