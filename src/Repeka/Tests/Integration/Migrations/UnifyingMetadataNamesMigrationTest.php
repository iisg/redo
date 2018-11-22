<?php
namespace Repeka\Tests\Integration\Migrations;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20180618111703
 */
class UnifyingMetadataNamesMigrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @var MetadataRepository|EntityRepository */
    private $metadataRepository;

    /** @before */
    public function prepare() {
        $this->loadDumpV8();
        // added when introduced metadata groups
        $this->getEntityManager()->getConnection()->exec('ALTER TABLE "metadata" ADD "group_id" VARCHAR(64) DEFAULT NULL');
        $this->metadataRepository = $this->container->get(MetadataRepository::class);
        $this->metadataRepository->save(Metadata::create('books', MetadataControl::TEXT(), 'żółw ', ['PL' => 'Test']));
        $this->metadataRepository->save(Metadata::create('books', MetadataControl::TEXT(), '"żóŁw', ['PL' => 'Test']));
        $this->getEntityManager()->flush();
        $this->migrate('20180618111703');
    }

    public function testDescriptionMetadata() {
        $this->assertNotNull($this->metadataRepository->findOneBy(['name' => 'opis']));
        $this->assertNull($this->metadataRepository->findOneBy(['name' => 'Opis']));
    }

    public function testTitleMetadata() {
        $this->assertNotNull($this->metadataRepository->findOneBy(['name' => 'tytul']));
        $this->assertNull($this->metadataRepository->findOneBy(['name' => 'Tytul']));
        $this->assertNull($this->metadataRepository->findOneBy(['name' => 'Tytuł']));
    }

    public function testDeduplicatingSampleMetadata() {
        $this->assertNotNull($this->metadataRepository->findOneBy(['name' => 'zolw']));
        $this->assertNotNull($this->metadataRepository->findOneBy(['name' => 'zolw1']));
        $this->assertNull($this->metadataRepository->findOneBy(['name' => 'zolw2']));
    }
}
