<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Entity\User;

class AuditEntryTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingEntry() {
        $user = $this->createMock(User::class);
        $entry = new AuditEntry('someCommand', $user);
        $this->assertEquals('someCommand', $entry->getCommandName());
        $this->assertEquals($user, $entry->getUser());
        $this->assertEmpty($entry->getData());
        $this->assertTrue($entry->isSuccessful());
        $this->assertNotNull($entry->getCreatedAt());
    }
}
