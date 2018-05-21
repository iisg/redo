<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Migrate resource display strategies to Twig.
 */
class Version20180503231556 extends RepekaMigration {
    public function migrate() {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $resourceKinds = $connection->fetchAll('SELECT id, display_strategies FROM resource_kind');
        foreach ($resourceKinds as $resourceKind) {
            $resourceKind['display_strategies'] = json_decode($resourceKind['display_strategies'], true);
            $resourceKind['display_strategies'] = array_map(
                function ($strategy) {
                    $strategy = preg_replace('#{{\s*allValues\s+m([-\d]+)\s*}}#', '{{r|m($1)}}', $strategy);
                    $strategy = preg_replace('#{{\s*oneValue\s+m([-\d]+)\s*}}#', '{{r|m($1)|first}}', $strategy);
                    $strategy = preg_replace('#{{\s*id\s*}}#', '{{r.id}}', $strategy);
                    return $strategy;
                },
                $resourceKind['display_strategies']
            );
            $resourceKind['display_strategies'] = json_encode($resourceKind['display_strategies']);
            $this->addSql('UPDATE resource_kind SET display_strategies = :display_strategies WHERE id = :id', $resourceKind);
        }
    }
}
