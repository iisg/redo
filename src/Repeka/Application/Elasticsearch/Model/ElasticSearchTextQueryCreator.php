<?php
namespace Repeka\Application\Elasticsearch\Model;

use Elastica\Query;

class ElasticSearchTextQueryCreator {

    public function createTextQuery(array $fields, string $phrase, array $stopWords): array {
        $metadataFilters = [];
        $metadataFilter = $this->createSimpleQueryString($fields, $phrase);
        $metadataFilter->setParam('boost', 100);
        $metadataFilters[] = $metadataFilter;
        $phrase = $this->simpleSearchQueryPhraseAdjuster(
            $phrase,
            $stopWords
        );
        $metadataFilters[] = $this->createSimpleQueryString($fields, $phrase);
        return $metadataFilters;
    }

    private function createSimpleQueryString(array $fields, string $query): Query\SimpleQueryString {
        $simpleQueryString = new Query\SimpleQueryString($query, $fields);
        $simpleQueryString->setDefaultOperator(Query\SimpleQueryString::OPERATOR_AND);
        return $simpleQueryString;
    }

    private function simpleSearchQueryPhraseAdjuster(string $phrase, array $stopWords = []): string {
        $stopWords = array_map(
            function ($value) {
                return '/\b' . $value . '\b/u';
            },
            $stopWords
        );
        $wordRegex = "/(\p{L}+)/u";
        $splitPhrase = explode('"', $phrase);
        for ($i = 0; $i < count($splitPhrase); $i++) {
            if ($i % 2 == 0) {
                $splitPhrase[$i] = preg_replace($stopWords, '', $splitPhrase[$i]);
                $splitPhrase[$i] = preg_replace($wordRegex, '$1~', $splitPhrase[$i]);
                $splitPhrase[$i] = preg_replace('/~+/', '~', $splitPhrase[$i]);
                $splitPhrase[$i] = preg_replace('/ +/', ' ', $splitPhrase[$i]);
            }
        }
        return join('"', $splitPhrase);
    }
}
