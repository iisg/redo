<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Replace PL transition labels to all other languages.
 * Add languages to label if not set.
 */
class Version20180422131227 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;
    private $replacingLanguageCode = 'PL';

    public function up(Schema $schema) {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $languages = $this->getLanguages();
        $workflows = $this->getWorkflows();
        foreach ($workflows as $workflow) {
            $workflow->replaceLabels($this->replacingLanguageCode, $languages);
            $this->queueWorkflowUpdate($workflow);
        }
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }

    /** @return Version20180422131227WorkflowEntity[] */
    private function getWorkflows(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id, transitions FROM workflow');
        return array_map(
            function ($row) {
                return new Version20180422131227WorkflowEntity($row['id'], $row['transitions']);
            },
            $results
        );
    }

    /** @return Version20180422131227LanguageEntity[] */
    private function getLanguages(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT code FROM language');
        return array_map(
            function ($row) {
                return new Version20180422131227LanguageEntity($row['code']);
            },
            $results
        );
    }

    private function queueWorkflowUpdate(Version20180422131227WorkflowEntity $workflow): void {
        $this->addSql('UPDATE workflow SET transitions = :transitions WHERE id = :id', $workflow->getAsParams());
    }
}

class Version20180422131227WorkflowEntity {
    private $id;
    private $transitions;

    public function __construct(int $id, string $transitionsJson) {
        $this->id = $id;
        $this->transitions = (array)json_decode($transitionsJson, true);
    }

    public function getAsParams(): array {
        return [
            'id' => $this->id,
            'transitions' => json_encode($this->transitions),
        ];
    }

    /**
     * @param $replacingLanguage string
     * @param $languages Version20180422131227LanguageEntity[]
     */
    public function replaceLabels($replacingLanguage, $languages) {
        foreach ($this->transitions as &$transition) {
            if (!isset($transition['label'])) {
                $transition['label'] = [];
            }
            if (!isset($transition['label'][$replacingLanguage])) {
                $transition['label'][$replacingLanguage] = '';
            }
            $replacingLabel = $transition['label'][$replacingLanguage];
            foreach ($languages as $language) {
                $transition['label'][$language->getCode()] = $replacingLabel;
            }
        }
    }
}

class Version20180422131227LanguageEntity {
    private $code;

    public function __construct(string $code) {
        $this->code = $code;
    }

    public function getCode(): string {
        return $this->code;
    }
}