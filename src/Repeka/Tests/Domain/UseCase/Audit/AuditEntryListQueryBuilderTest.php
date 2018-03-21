<?php
namespace Repeka\Tests\Domain\UseCase\Audit;

use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;

class AuditEntryListQueryBuilderTest extends \PHPUnit_Framework_TestCase {
    public function testCommandNamesFilter() {
        $query = AuditEntryListQuery::builder()
            ->filterByCommandNames(['a', 'b'])
            ->build();
        $this->assertEquals(['a', 'b'], $query->getCommandNames());
    }
}
