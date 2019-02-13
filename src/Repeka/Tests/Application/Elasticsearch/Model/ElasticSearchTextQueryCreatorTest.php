<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Model\ElasticSearchTextQueryCreator;

class ElasticSearchTextQueryCreatorTest extends \PHPUnit_Framework_TestCase {

    /** @dataProvider fromArrayExamples */
    public function testSimpleSearchQueryPhraseAdjuster(string $phrase, $expectedAdjustedPhrase) {
        $elasticSearchTextQueryCreator = new ElasticSearchTextQueryCreator();
        $metadataFilters = $elasticSearchTextQueryCreator->createTextQuery([], [$phrase], ['ma', 'i', 'a', 'nie']);
        $this->assertEquals(
            $expectedAdjustedPhrase,
            $metadataFilters[1]->getParams()['query']
        );
    }

    /** @SuppressWarnings("PHPMD.ExcessiveMethodLength") */
    public function fromArrayExamples(): array {

        return [
            ['', ''],
            ['ala ma kota', 'ala~ kota~'],
            ['"ala" ma kota', '"ala" kota~'],
            ['ala ma -kota', "ala~ -kota~"],
            ['(ala | ma) kota', "(ala~ | ) kota~"],
            ['(ala|ma) kota', "(ala~|) kota~"],
            ['"ala ma kota" i psa', '"ala ma kota" psa~'],
            ['ala~ ma kota~', 'ala~ kota~'],
            ['kochać i jeść', 'kochać~ jeść~'],
            ['równocześnie', 'równocześnie~'],
        ];
    }
}
