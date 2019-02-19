<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Removes all duplicated metadata names and replaces them
 * with resourceClassName_metadataName
 */
class Version20190216191737 extends RepekaMigration {

    public function migrate() {
        $duplicatedMetadata = $this->fetchAll(
            'SELECT id, name, resource_class FROM metadata AS m1
             WHERE id > 0 AND EXISTS(SELECT * FROM metadata AS m2 WHERE m1.id <> m2.id AND m1.name = m2.name)'
        );
        if (!empty($duplicatedMetadata)) {
            $this->write('Renamed metadata (old => new):');
        }
        foreach ($duplicatedMetadata as $metadata) {
            $newName = $metadata['resource_class'] . '_' . $metadata['name'];
            $params = ['name' => $newName, 'id' => $metadata['id']];
            $this->addSql('UPDATE metadata SET name = :name WHERE id = :id', $params);
            $this->write($metadata['name'] . ' => ' . $newName);
        }
    }
}
