<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Validation\Rules\TeaserVisibilityWiderOrEqualToFullVisibilityRule;

class TeaserVisibilityWiderOrEqualToFullVisibilityRuleTest extends \PHPUnit_Framework_TestCase {

    /** @var TeaserVisibilityWiderOrEqualToFullVisibilityRule $rule */
    private $rule;

    public function setUp() {
        $this->rule = new TeaserVisibilityWiderOrEqualToFullVisibilityRule();
    }

    public function testRuleRejectsWiderFullVisibility() {
        $contents = ResourceContents::fromArray(
            [
                SystemMetadata::VISIBILITY => [1, 2, 4],
                SystemMetadata::TEASER_VISIBILITY => [1, 2],
            ]
        );
        $this->assertFalse($this->rule->validate($contents));
    }

    public function testFullVisibilityNotContainingAllUsersWithTeaserVisibilityRejected() {
        $contents = ResourceContents::fromArray(
            [
                SystemMetadata::VISIBILITY => [1, 2, 4],
                SystemMetadata::TEASER_VISIBILITY => [1, 2, 5],
            ]
        );
        $this->assertFalse($this->rule->validate($contents));
    }

    public function testWiderTeaserVisibilityIsValid() {
        $contents = ResourceContents::fromArray(
            [
                SystemMetadata::VISIBILITY => [1, 2, 4],
                SystemMetadata::TEASER_VISIBILITY => [1, 2, 4, 3, 45],
            ]
        );
        $this->assertTrue($this->rule->validate($contents));
    }

    public function testEqualFullAndTeaserVisibilityAreValid() {
        $contents = ResourceContents::fromArray(
            [
                SystemMetadata::VISIBILITY => [1, 2, 4],
                SystemMetadata::TEASER_VISIBILITY => [1, 2, 4],
            ]
        );
        $this->assertTrue($this->rule->validate($contents));
    }
}
