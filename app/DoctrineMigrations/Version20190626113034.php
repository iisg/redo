<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;

/** Map integer metadata values from strings to numbers. */
class Version20190626113034 extends RepekaMigration {
    public function migrate() {
        $integerMetadataQuery = MetadataListQuery::builder()->filterByControl(MetadataControl::INTEGER())->build();
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $integerMetadataList = $metadataRepository->findByQuery($integerMetadataQuery);
        foreach ($integerMetadataList as $integerMetadata) {
            $this->write('Migrating values of metadata: ' . $integerMetadata->getName());
            $metadataId = $integerMetadata->getId();
            for ($i = 0; $i < 10; $i++) {
                $query = "UPDATE resource SET "
                    . "contents = jsonb_set(contents, '{ $metadataId, $i, value}', (contents->'$metadataId'->$i->>'value')::JSONB) "
                    . "WHERE contents->'$metadataId'->$i IS NOT NULL;";
                $this->addSql($query);
            }
        }
    }
}
