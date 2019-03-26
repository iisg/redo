<?php
// @codingStandardsIgnoreFile
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;

class ResourceWorkflowsFixture extends RepekaFixture {
    const ORDER = 1;

    const BOOK_WORKFLOW = 'bookWorkflow';
    const USER_WORKFLOW = 'userWorkflow';
    const REMARK_WORKFLOW = 'remarkWorkflow';

    /** @inheritdoc */
    public function load(ObjectManager $manager) {
        $this->addBookWorkflow();
        $this->addUserWorkflow();
        $this->addRemarkWorkflow();
    }

    private function addBookWorkflow() {
        $places = json_decode(
            '[{"id": "y1oosxtgf", "label": {"PL": "Zaimportowana", "EN":"Imported"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "data_utworzenia_rekordu", "metadataValue": "{{ \'now\'|date(\'Y-m-d H:i:s\') }}", "setOnlyWhenEmpty": true}}]}]',
            true
        );
        $name = [
            'PL' => 'Pełny obieg książki',
            'EN' => 'Book workflow',
        ];
        $this->handleCommand(new ResourceWorkflowCreateCommand($name, $places, [], 'books', '', ''), self::BOOK_WORKFLOW);
    }

    private function addUserWorkflow() {
        $places = json_decode(
            <<<JSON
[{"id": "dw5kam1sr", "label": {"EN": "Signed up", "PL": "Zarejestrowany"}, "pluginsConfig": [], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [-2], "autoAssignMetadataIds": []}]
JSON
            ,
            true
        );
        $this->handleCommand(
            new ResourceWorkflowCreateCommand(
                [
                    'PL' => 'Proces użytkownika',
                    'EN' => 'User workflow',
                ],
                $places,
                [],
                'users',
                '{"dw5kam1sr":{"x":244,"y":284}}',
// region base64_thumbnail
                'data:image/png;base64,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'
// endregion
            ),
            self::USER_WORKFLOW
        );
    }

    private function addRemarkWorkflow() {
        $places = json_decode(
            <<<JSON
            [ {"id": "2ln8ff5a6", "label": {"EN": "Reported remark", "PL": "Zgłoszona uwaga"}, "pluginsConfig": []}, {"id": "y0ay2nfa5", "label": {"EN": "Served", "PL": "Obsłużona"}, "pluginsConfig": []}, {"id": "sdskdr3xx", "label": {"EN": "Taken to serivce", "PL": "Przyjęta do obsługi"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "Report manager", "metadataValue": "1", "setOnlyWhenEmpty": true}}]}]
JSON
            ,
            true
        );
        $name = [
            'PL' => 'Zgłaszanie uwag',
            'EN' => 'Report remark',
        ];
        $this->handleCommand(new ResourceWorkflowCreateCommand($name, $places, [], 'cms', '', ''), self::REMARK_WORKFLOW);
    }
}
