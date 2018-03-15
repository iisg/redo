<?php
namespace Repeka\Tests\Domain\Entity\Workflow;

use Repeka\Domain\Entity\Workflow\FluentRestrictingMetadataSelector;

class FluentRestrictingMetadataSelectorTest extends \PHPUnit_Framework_TestCase {
    public function testSelectingOne() {
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1], $selector->required()->get());
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([2], $selector->locked()->get());
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([3], $selector->assignees()->get());
    }

    public function testSelectingTwo() {
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1, 2], $selector->required()->locked()->get());
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([2, 3], $selector->locked()->assignees()->get());
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1, 3], $selector->required()->assignees()->get());
    }

    public function testRepeatingDoesNotDuplicateResults() {
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1], $selector->required()->required()->get());
    }

    public function testMergingMultiple() {
        $selector = new FluentRestrictingMetadataSelector([1, 2, 3], [3, 4], [5], [4]);
        $this->assertEquals([1, 2, 3, 5], $selector->required()->assignees()->get());
        $selector = new FluentRestrictingMetadataSelector([1, 2, 3], [3, 4], [5], [4]);
        $this->assertEquals([1, 2, 3, 4], $selector->required()->locked()->get());
    }

    public function testGettingAll() {
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1, 2, 3, 4], $selector->all()->get());
    }

    public function testNoDuplicatesWithAll() {
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1, 2, 3, 4], $selector->required()->all()->get());
        $selector = new FluentRestrictingMetadataSelector([1], [2], [3], [4]);
        $this->assertEquals([1, 2, 3, 4], $selector->all()->required()->get());
    }
}
